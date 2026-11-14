param(
    [Parameter(Mandatory)][int]$HttpPort,
    [Parameter(Mandatory)][int]$FailurePort,
    [Parameter(Mandatory)][scriptblock]$RunSql,
    [Parameter(Mandatory)][string]$EvidenceDirectory
)
$ErrorActionPreference = 'Stop'
function Read-Response([string]$Path,[int]$Port=$HttpPort) {
    Invoke-WebRequest -Uri "http://127.0.0.1:$Port$Path" -SkipHttpErrorCheck -TimeoutSec 8
}
function Verify([bool]$Condition,[string]$Message) {
    if (-not $Condition) { throw "Public content test failed: $Message" }
}
foreach ($path in @('/api/news.php','/api/tour.php')) {
    $empty=Read-Response $path
    Verify ($empty.StatusCode -eq 200) "Empty API $path"
}
& $RunSql @'
USE mcr_foundation_test;
SET time_zone = '+00:00';
UPDATE albums SET cover_image='javascript:alert(1)' WHERE id=2;
INSERT INTO songs (album_id,title,lyrics) VALUES
(1,'Dummy Song One','A needle-in-lyrics phrase.
Second line <script>alert(1)</script>'),
(2,'Dummy Song Two',NULL);
INSERT INTO news_articles (title,slug,content,news_date) VALUES
('Dummy news <img src=x onerror=alert(1)>','dummy-news','First line.
Second line <script>alert(1)</script>','2026-11-01 10:00:00'),
('Newer dummy news','newer-news','Another article.','2026-11-02 10:00:00'),
('Undated dummy news','undated-news',NULL,NULL);
INSERT INTO venues (name,city,country) VALUES ('Dummy venue <script>test</script>','Jakarta','Indonesia');
INSERT INTO concerts (venue_id,concert_date,status) VALUES
(1,'2026-11-17 13:00:00','scheduled'), (1,NULL,'cancelled');
'@

$musicResponse=Read-Response '/api/music.php?q=needle-in-lyrics'
$music=($musicResponse.Content | ConvertFrom-Json).data
Verify ($musicResponse.StatusCode -eq 200 -and $music.albums.Count -eq 2 -and $music.songs.Count -eq 2 -and $music.query -eq 'needle-in-lyrics') 'Album/song data and query'
$lyricSong = @($music.songs | Where-Object { $_.title -eq 'Dummy Song One' })[0]
Verify ($lyricSong.lyrics -match '<script>alert\(1\)</script>') 'Lyrics are data, not HTML pre-escaped by the API'
Verify ($music.albums[0].cover_image -eq 'assets/Blackparade.png') 'Unsafe image path uses public fallback'
foreach ($path in @('/api/music.php?q[]=invalid','/api/music.php?q=%FF','/api/article.php?slug[]=invalid','/api/tour.php?concert=-1','/api/tour.php?concert[]=invalid')) {
    Verify ((Read-Response $path).StatusCode -eq 400) "Invalid input $path"
}
$newsResponse=Read-Response '/api/news.php'
$news=($newsResponse.Content | ConvertFrom-Json).data.articles
Verify ($newsResponse.StatusCode -eq 200 -and $news.Count -eq 3 -and $news[0].slug -eq 'newer-news' -and $news[-1].news_date -eq $null) 'News order/null dates'
$detailResponse=Read-Response '/api/article.php?slug=dummy-news'
$article=($detailResponse.Content | ConvertFrom-Json).data.article
Verify ($detailResponse.StatusCode -eq 200 -and $article.title -match '<img src=x' -and $article.content -match '<script>') 'Article JSON preserves literal text for safe DOM rendering'
foreach ($path in @('/api/article.php','/api/article.php?slug=missing','/api/article.php?slug=%27%20OR%201%3D1--','/api/tour.php?concert=9999')) {
    Verify ((Read-Response $path).StatusCode -eq 404) "Missing or injected record $path"
}
$tourResponse=Read-Response '/api/tour.php?concert=1'
$tour=($tourResponse.Content | ConvertFrom-Json).data
Verify ($tourResponse.StatusCode -eq 200 -and $tour.selected.id -eq 1 -and $tour.selected.date_display -eq '17 Nov 2026, 20:00 WIB') 'Concert identity and WIB'
& $RunSql "USE mcr_foundation_test; UPDATE news_articles SET title='Updated dummy news' WHERE slug='newer-news';"
$homeResponse=Read-Response '/api/home.php'
$homeData=($homeResponse.Content | ConvertFrom-Json).data
Verify ($homeResponse.StatusCode -eq 200 -and $homeData.news[0].title -eq 'Updated dummy news' -and $homeData.concerts[0].date_display -eq '17 Nov 2026, 20:00 WIB') 'Updated data is shared by Home API'
foreach ($route in @('home','music','news','article?slug=dummy-news','tour')) {
    $endpoint=if ($route.Contains('?')) { '/api/article.php?slug=dummy-news' } else { "/api/$route.php" }
    $response=Read-Response $endpoint
    Verify ([string]$response.Headers['Access-Control-Allow-Origin'] -eq '*') "Public read CORS $endpoint"
    $failure=Read-Response $endpoint $FailurePort
    Verify ($failure.StatusCode -eq 503 -and $failure.Content -notmatch 'SQLSTATE|PDOException|Stack trace') "Safe unavailable API $endpoint"
    Verify ([string]$failure.Headers['Access-Control-Allow-Origin'] -eq '*') 'CORS also present on errors'
}
$options=Invoke-WebRequest -Uri "http://127.0.0.1:$HttpPort/api/music.php" -Method OPTIONS -SkipHttpErrorCheck
Verify ($options.StatusCode -eq 204) 'Public API OPTIONS'
$post=Invoke-WebRequest -Uri "http://127.0.0.1:$HttpPort/api/music.php" -Method POST -SkipHttpErrorCheck
Verify ($post.StatusCode -eq 405) 'Public API rejects writes'
$targets=[Collections.Generic.HashSet[string]]::new()
foreach ($page in @('index','music','news','news-detail','tour','about','showcase','ticket-transaction')) {
    $html=Read-Response "/$page.html"
    Verify ($html.StatusCode -eq 200 -and $html.Content -notmatch '<\?php') "Static HTML shell $page"
    Verify ((Read-Response "/$page.html" $FailurePort).StatusCode -eq 200) "HTML shell independent of database $page"
    foreach ($match in [regex]::Matches($html.Content,'(?:href|src|action)="([^"]+)"')) {
        $target=[Net.WebUtility]::HtmlDecode($match.Groups[1].Value)
        if ($target -notmatch '^(?:https?:|#|mailto:|tel:)' -and $target -ne '') {
            Verify ($target -notmatch '\.php(?:[?#]|$)') "No PHP navigation/form action in frontend: $target"
            $null=$targets.Add('/'+($target -split '#',2)[0].TrimStart('/'))
        }
    }
}
foreach ($target in $targets) { Verify ((Read-Response $target).StatusCode -eq 200) "Linked frontend resource $target" }
foreach ($page in @('index','music','news','news-detail','tour')) {
    $query=if ($page -eq 'news-detail') {'?slug=dummy-news'} else {''}
    $response=Invoke-WebRequest -Uri "http://127.0.0.1:$HttpPort/$page.php$query" -MaximumRedirection 0 -SkipHttpErrorCheck -ErrorAction SilentlyContinue
    Verify ($response.StatusCode -eq 308 -and [string]$response.Headers.Location -eq "$page.html$query") "PHP compatibility redirect $page"
}
$evidence=[ordered]@{ music_status=[int]$musicResponse.StatusCode; song_count=$music.songs.Count; news_status=[int]$newsResponse.StatusCode; article_status=[int]$detailResponse.StatusCode; concert_time=$tour.selected.date_display; updated_home_title=$homeData.news[0].title; frontend_pages=8; verified_resources=$targets.Count; cors='Public GET/OPTIONS only, without credentials'; result='All API and static-shell assertions passed. Browser rendering is checked separately.' }
$evidencePath=Join-Path $EvidenceDirectory 'content-evidence.json'
[IO.File]::WriteAllText($evidencePath,($evidence|ConvertTo-Json -Depth 5),[Text.UTF8Encoding]::new($false))
Write-Output "CONTENT EVIDENCE: $evidencePath"
Write-Output "PASS: separated HTML/PHP, public JSON APIs, input validation, CORS, WIB, errors, redirects and $($targets.Count) frontend resources."
