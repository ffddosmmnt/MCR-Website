param(
    [Parameter(Mandatory)][int]$HttpPort,
    [Parameter(Mandatory)][int]$FailurePort,
    [Parameter(Mandatory)][string]$Password,
    [Parameter(Mandatory)][scriptblock]$RunSql,
    [Parameter(Mandatory)][string]$EvidenceDirectory
)
$ErrorActionPreference = 'Stop'
$client = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
function Check([bool]$Condition, [string]$Message) {
    if (-not $Condition) { throw "Dashboard test failed: $Message" }
}
function Request([string]$Path, [string]$Method = 'GET', [hashtable]$Body = @{}, [object]$Session = $client, [int]$Port = $HttpPort) {
    $args = @{ Uri="http://127.0.0.1:$Port$Path"; Method=$Method; WebSession=$Session; SkipHttpErrorCheck=$true; MaximumRedirection=0; TimeoutSec=8 }
    if ($Method -eq 'POST') { $args.Body = $Body }
    return Invoke-WebRequest @args -ErrorAction SilentlyContinue
}
function Token([object]$Response, [string]$Name = 'csrf') {
    return [regex]::Match($Response.Content, 'name="' + $Name + '" value="([^"]*)"').Groups[1].Value
}
function Sql([string]$Query) { return & $RunSql "USE mcr_foundation_test; SET time_zone = '+00:00'; $Query" }
function Form([string]$Module, [string]$Action, [int]$Id = 0) {
    $path = "/dashboard-auth/index.php?module=$Module&action=$Action"
    if ($Id) { $path += "&id=$Id" }
    return Request $path
}
function Write-Record([string]$Module, [string]$Action, [hashtable]$Values, [int]$Id = 0, [object]$Snapshot = $null) {
    $form = if ($null -ne $Snapshot) { $Snapshot } else { Form $Module $Action $Id }
    Check ($form.StatusCode -eq 200) "Load $Module $Action form"
    $body = @{ csrf=(Token $form); version=(Token $form 'version') }
    foreach ($key in $Values.Keys) { $body[$key] = $Values[$key] }
    return Request "/dashboard-auth/index.php?module=$Module&action=$Action&id=$Id" 'POST' $body
}

$anonymous = Request '/dashboard-auth/'
Check ($anonymous.StatusCode -eq 303 -and $anonymous.Headers.Location -eq 'login.php') 'Anonymous dashboard access'
Check ((Request '/dashboard-auth/index.php?module=albums&action=create' 'POST' @{title='Unauthorized'}).StatusCode -eq 303) 'Anonymous write'
foreach ($path in @('/dashboard-auth/internal/admin.php','/dashboard-auth/internal/create-admin.php','/dashboard-auth/internal/view.php','/dashboard-auth/internal/test.ps1','/dashboard-auth/.htaccess','/dashboard-auth/selectdb/connection.php')) {
    Check ((Request $path).StatusCode -eq 404) "Private/removed path: $path"
}
foreach ($path in @('/dashboard-auth/adminlte.min.css','/dashboard-auth/template.css','/dashboard-auth/dashboard.js')) {
    Check ((Request $path).StatusCode -eq 200) "Dashboard asset $path"
}
$login = Request '/dashboard-auth/login.php'
Check ($login.StatusCode -eq 200 -and $login.Headers['Cache-Control'] -match 'no-store' -and $anonymous.Headers['Set-Cookie'] -match 'HttpOnly' -and $anonymous.Headers['Set-Cookie'] -match 'SameSite=Lax') 'Login and cookie headers'
$oldCookie = $client.Cookies.GetCookies("http://127.0.0.1:$HttpPort/")['mcr_admin'].Value
Check ((Request '/dashboard-auth/login.php' 'POST' @{csrf=(Token $login); email='admin@example.test'; password='wrong'}).StatusCode -eq 401) 'Bad password rejected'
$signIn = Request '/dashboard-auth/login.php' 'POST' @{csrf=(Token $login); email='admin@example.test'; password=$Password}
Check ($signIn.StatusCode -eq 303) 'Admin sign-in'
$newCookie = $client.Cookies.GetCookies("http://127.0.0.1:$HttpPort/")['mcr_admin'].Value
Check ($oldCookie -ne $newCookie) 'Session ID rotation'
Check ((Request '/dashboard-auth/index.php?module=albums&action=create' 'POST' @{title='Missing CSRF'}).StatusCode -eq 403) 'CSRF rejected'

$fixtures = [ordered]@{
    band_members = @{name='CRUD Member <script>alert(1)</script>'; instrument='Guitar'; cover_img=''}
    albums = @{title='CRUD Album'; release_date='2026-11-12'; cover_image='assets/Blackparade.png'}
    songs = @{album_id=''; title='CRUD Song'; lyrics="Line one`n<script>alert(1)</script>"}
    news_articles = @{title='CRUD News'; slug='crud-test-news'; content='Body <img src=x onerror=alert(1)>'; news_date='2026-11-12T12:00'}
    venues = @{name='CRUD Venue'; city='Jakarta'; country='Indonesia'}
    concerts = @{venue_id=''; concert_date='2026-11-17T20:00'; status='scheduled'}
    ticket_types = @{concert_id=''; name='CRUD Ticket'; price='125.50'; quota='20'}
    categories = @{name='CRUD Category'}
    products = @{category_id=''; name='CRUD Product'; price='19.99'; details='Dummy details'}
    product_variants = @{product_id=''; size='M'; color='Black'; stock='5'}
}
$ids = @{}
$parentMap = @{songs=@('album_id','albums'); concerts=@('venue_id','venues'); ticket_types=@('concert_id','concerts'); products=@('category_id','categories'); product_variants=@('product_id','products')}
foreach ($module in $fixtures.Keys) {
    if ($parentMap.ContainsKey($module)) { $ref = $parentMap[$module]; $fixtures[$module][$ref[0]] = [string]$ids[$ref[1]] }
    $created = Write-Record $module 'create' $fixtures[$module]
    Check ($created.StatusCode -eq 303) "Create $module (got $($created.StatusCode))"
    $ids[$module] = [int](Sql "SELECT MAX(id) FROM $module;")
    $listed = Request "/dashboard-auth/index.php?module=$module"
    Check ($listed.StatusCode -eq 200 -and $listed.Content -match "action=edit(?:&amp;|&)id=$($ids[$module])") "Read $module"
    $changedKey = if ($fixtures[$module].ContainsKey('title')) { 'title' } elseif ($fixtures[$module].ContainsKey('name')) { 'name' } elseif ($module -eq 'concerts') { 'status' } else { 'stock' }
    $fixtures[$module][$changedKey] = if ($changedKey -eq 'stock') { '6' } else { $fixtures[$module][$changedKey] + ' updated' }
    $edited = Write-Record $module 'edit' $fixtures[$module] $ids[$module]
    Check ($edited.StatusCode -eq 303) "Update $module"
    Check ((Sql "SELECT $changedKey FROM $module WHERE id=$($ids[$module]);").Trim() -eq $fixtures[$module][$changedKey]) "Persist updated $module"
}
Check ((Sql "SELECT concert_date FROM concerts WHERE id=$($ids.concerts);").Trim() -eq '2026-11-17 13:00:00') 'WIB stored in UTC'
Check ((Request '/api/tour.php').Content -match '17 Nov 2026, 20:00 WIB') 'Concert frontend integration'
Check ((Request '/api/music.php').Content -match 'CRUD Album' -and (Request '/api/music.php').Content -match 'CRUD Song') 'Music frontend integration'
Check ((Request '/api/article.php?slug=crud-test-news').Content -match 'CRUD News') 'News frontend integration'
Check ((Request '/dashboard-auth/index.php?module=band_members').Content -match '&lt;script&gt;' -and (Request '/dashboard-auth/index.php?module=band_members').Content -notmatch '<script>alert') 'Escaped output'
Check ((Write-Record 'news_articles' 'create' $fixtures.news_articles).StatusCode -eq 409) 'Duplicate slug'
Check ((Write-Record 'albums' 'delete' @{} $ids.albums).StatusCode -eq 409) 'RESTRICT parent deletion'
Check ((Write-Record 'songs' 'create' @{album_id='4294967295'; title='Invalid'; lyrics=''}).StatusCode -eq 404) 'Unknown parent'
Check ((Write-Record 'products' 'create' @{category_id=[string]$ids.categories; name='Bad price'; price='-1'; details=''}).StatusCode -eq 422) 'Negative price'
Check ((Write-Record 'product_variants' 'create' @{product_id=[string]$ids.products; size=''; color=''; stock='2147483648'}).StatusCode -eq 422) 'Stock overflow'
Check ((Write-Record 'albums' 'create' @{title='Bad date'; release_date='2026-02-30'; cover_image=''}).StatusCode -eq 422) 'Invalid calendar date'
Check ((Write-Record 'albums' 'create' @{title='Bad image'; release_date=''; cover_image='javascript:alert(1)'}).StatusCode -eq 422) 'Unsafe image'
Check ((Write-Record 'albums' 'create' @{title=''; release_date=''; cover_image=''}).StatusCode -eq 422) 'Required field'
$stale = Form 'albums' 'edit' $ids.albums
$null = Sql "UPDATE albums SET title='Concurrent change' WHERE id=$($ids.albums);"
Check ((Write-Record 'albums' 'edit' $fixtures.albums $ids.albums $stale).StatusCode -eq 409) 'Stale edit'
$staleDelete = Form 'band_members' 'delete' $ids.band_members
$null = Sql "UPDATE band_members SET instrument='Bass' WHERE id=$($ids.band_members);"
Check ((Write-Record 'band_members' 'delete' @{} $ids.band_members $staleDelete).StatusCode -eq 409) 'Stale delete'
Check ((Request '/dashboard-auth/index.php?module=albums&search=no-such-record').Content -match 'No records found') 'Empty search'
Check ((Request '/dashboard-auth/index.php?module=albums&search=%27%20OR%201%3D1--').Content -match 'No records found') 'SQL-like search remains literal'
foreach ($path in @('/dashboard-auth/index.php?module=users','/dashboard-auth/index.php?module=albums;DROP','/dashboard-auth/index.php?module=albums&sort=unknown','/dashboard-auth/index.php?module=albums&search[]=x','/dashboard-auth/index.php?module=albums&search=%FF','/dashboard-auth/index.php?module=albums&action=edit&id=-1')) {
    Check ((Request $path).StatusCode -in @(400,404)) "Invalid input $path"
}
Check ((Request '/dashboard-auth/index.php?module=songs&filter=1').StatusCode -eq 200) 'Relation filter'
Check ((Request '/dashboard-auth/index.php?module=albums&limit=5&page=9999&sort=title&direction=asc').StatusCode -eq 200) 'Page clamp and sort'
$null = Sql "INSERT INTO categories (name) VALUES ('Page fixture 1'),('Page fixture 2'),('Page fixture 3'),('Page fixture 4'),('Page fixture 5'),('Page fixture 6'),('Page fixture 7');"
$secondPage = Request '/dashboard-auth/index.php?module=categories&limit=5&page=2&sort=name&direction=asc'
Check ($secondPage.StatusCode -eq 200 -and $secondPage.Content -match 'Showing 6–8 of 8 records' -and $secondPage.Content -match 'Page 2 of 2') 'Second page with more than five records'
$null = Sql "DELETE FROM categories WHERE name LIKE 'Page fixture %';"
Check ((Request '/dashboard-auth/index.php' 'PUT').StatusCode -eq 405) 'Write method restriction'
Check ((Request '/dashboard-auth/logout.php').StatusCode -eq 405) 'GET does not log out'
$failureSession = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$failedLogin = Request '/dashboard-auth/login.php' 'GET' @{} $failureSession $FailurePort
$failedPost = Request '/dashboard-auth/login.php' 'POST' @{csrf=(Token $failedLogin); email='admin@example.test'; password=$Password} $failureSession $FailurePort
Check ($failedPost.StatusCode -eq 503 -and $failedPost.Content -notmatch 'SQLSTATE|PDOException|Stack trace|mcr_reader') 'Safe database failure'

# Role changes must invalidate an already authenticated session.
$null = Sql "UPDATE users SET role='user' WHERE email='admin@example.test';"
Check ((Request '/dashboard-auth/').StatusCode -eq 403) 'Role revocation'
$denied = Request '/dashboard-auth/login.php'
Check ((Request '/dashboard-auth/login.php' 'POST' @{csrf=(Token $denied); email='admin@example.test'; password=$Password}).StatusCode -eq 401) 'Ordinary user denied login'
$null = Sql "UPDATE users SET role='admin' WHERE email='admin@example.test';"
$login = Request '/dashboard-auth/login.php'
$null = Request '/dashboard-auth/login.php' 'POST' @{csrf=(Token $login); email='admin@example.test'; password=$Password}
foreach ($module in @('product_variants','products','categories','ticket_types','concerts','venues','news_articles','songs','albums','band_members')) {
    Check ((Write-Record $module 'delete' @{} $ids[$module]).StatusCode -eq 303) "Delete $module"
    Check ((Sql "SELECT COUNT(*) FROM $module WHERE id=$($ids[$module]);").Trim() -eq '0') "Verify deleted $module"
}
$dashboardPage = Request '/dashboard-auth/'
$logout = Request '/dashboard-auth/logout.php' 'POST' @{csrf=(Token $dashboardPage)}
Check ($logout.StatusCode -eq 303 -and (Request '/dashboard-auth/').StatusCode -eq 303) 'Logout invalidates session'
$evidence = @{modules=@($fixtures.Keys); crud='create/read/update/delete passed for all 10 modules'; auth='anonymous, role, CSRF, login/logout, session rotation'; relations='RESTRICT, nonexistent parent'; validation='dates, money, stock, images, required, UTF-8, arrays'; concurrency='stale edit and delete rejected'; frontend='Music, News, Tour share persisted records'; database='isolated mcr_foundation_test only'}
[IO.File]::WriteAllText((Join-Path $EvidenceDirectory 'dashboard-auth-evidence.json'), ($evidence | ConvertTo-Json -Depth 4))
Write-Output 'PASS: dashboard CRUD (10 modules), auth/CSRF, validation, relations, conflicts and frontend integration.'
