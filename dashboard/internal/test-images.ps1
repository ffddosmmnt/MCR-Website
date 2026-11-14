param(
    [Parameter(Mandatory)][string]$Php,
    [Parameter(Mandatory)][int]$HttpPort,
    [Parameter(Mandatory)][scriptblock]$RunSql,
    [Parameter(Mandatory)][string]$EvidenceDirectory
)
$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$uploadDirectory = (Resolve-Path -LiteralPath (Join-Path $workspace 'dashboard/uploads')).Path
$fixtureDirectory = Join-Path $EvidenceDirectory 'image-fixtures'
New-Item -ItemType Directory -Path $fixtureDirectory -Force | Out-Null
$session = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$ownedUploads = [Collections.Generic.List[string]]::new()
$records = [Collections.Generic.List[object]]::new()
function Image-Check([bool]$Condition, [string]$Message) {
    if (-not $Condition) { throw "Image upload test failed: $Message" }
}
function Image-Sql([string]$Query) { return & $RunSql "USE mcr_foundation_test; $Query" }
function Image-Get([string]$Path) {
    return Invoke-WebRequest "http://127.0.0.1:$HttpPort$Path" -WebSession $session -SkipHttpErrorCheck -TimeoutSec 8
}
function Image-Post([string]$Module, [string]$Action, [hashtable]$Values, [string]$File = '', [int]$Id = 0, [object]$Snapshot = $null) {
    $path = "/dashboard/index.php?module=$Module&action=$Action&id=$Id"
    $page = if ($Snapshot) { $Snapshot } else { Image-Get $path }
    $form = @{
        csrf = [regex]::Match($page.Content, 'name="csrf" value="([^"]*)"').Groups[1].Value
        version = [regex]::Match($page.Content, 'name="version" value="([^"]*)"').Groups[1].Value
    }
    foreach ($key in $Values.Keys) { $form[$key] = $Values[$key] }
    if ($File) { $form[$(if ($Module -eq 'albums') { 'cover_image' } else { 'cover_img' })] = Get-Item -LiteralPath $File }
    return Invoke-WebRequest "http://127.0.0.1:$HttpPort$path" -Method Post -Form $form -WebSession $session -SkipHttpErrorCheck -MaximumRedirection 0 -ErrorAction SilentlyContinue -TimeoutSec 8
}
function Image-Remember([string]$Module) {
    $id = [int](Image-Sql "SELECT MAX(id) FROM $Module;")
    $column = if ($Module -eq 'albums') { 'cover_image' } else { 'cover_img' }
    $path = (Image-Sql "SELECT $column FROM $Module WHERE id=$id;").Trim()
    Image-Check ($path -match '^dashboard/uploads/[a-f0-9]{32}\.(png|jpg|webp)$') 'Generated image filename'
    $ownedUploads.Add($path)
    $records.Add(@{ Module=$Module; Id=$id })
    return @{ Id=$id; Path=$path }
}
$fixtureScript = Join-Path $fixtureDirectory 'generate.php'
$fixtureSource = @'
<?php
$directory = $argv[1];
$pixels = imagecreatetruecolor(4, 4);
imagefilledrectangle($pixels, 0, 0, 3, 3, imagecolorallocate($pixels, 90, 40, 160));
imagepng($pixels, $directory . '/valid.png');
imagejpeg($pixels, $directory . '/valid.jpg');
imagewebp($pixels, $directory . '/valid.webp');
$wide = imagecreatetruecolor(6001, 1);
imagepng($wide, $directory . '/wide.png');
file_put_contents($directory . '/fake.png', '<?php echo "upload-canary"; ?>');
file_put_contents($directory . '/polyglot.php', file_get_contents($directory . '/valid.png') . '<?php echo "upload-canary"; ?>');
file_put_contents($directory . '/oversize.png', file_get_contents($directory . '/valid.png') . str_repeat('x', 2 * 1024 * 1024));
'@
try {
    [IO.File]::WriteAllText($fixtureScript, $fixtureSource)
    & $Php $fixtureScript $fixtureDirectory
    Image-Check ($LASTEXITCODE -eq 0) 'Generate valid image fixtures'
    $page = Image-Get '/dashboard/index.php?module=albums&action=create'
    Image-Check ($page.Content -match 'enctype="multipart/form-data"' -and $page.Content -match 'type="file"' -and $page.Content -notmatch 'Existing local image') 'File selector replaces path field'
    foreach ($extension in @('png','jpg','webp')) {
        $response = Image-Post 'albums' 'create' @{title="Image fixture $extension"; release_date=''} (Join-Path $fixtureDirectory "valid.$extension")
        Image-Check ($response.StatusCode -eq 303) "Upload valid $extension"
        $record = Image-Remember 'albums'
        $image = Image-Get ('/' + $record.Path)
        $expectedMime = if ($extension -eq 'jpg') { 'image/jpeg' } else { "image/$extension" }
        Image-Check ($image.StatusCode -eq 200 -and $image.Headers['Content-Type'] -eq $expectedMime) "Serve $extension as an image"
        Image-Check (@(((Image-Get '/api/music.php').Content | ConvertFrom-Json).data.albums | Where-Object { $_.cover_image -eq $record.Path }).Count -eq 1) 'Album cover available to Music frontend API'
        $preserved = Image-Post 'albums' 'edit' @{title="Image fixture $extension edited"; release_date=''} '' $record.Id
        Image-Check ($preserved.StatusCode -eq 303 -and (Image-Sql "SELECT cover_image FROM albums WHERE id=$($record.Id);").Trim() -eq $record.Path) 'Edit without new image preserves existing image'
    }
    $first = $records[0]
    $oldPath = $ownedUploads[0]
    $replaced = Image-Post 'albums' 'edit' @{title='Replaced cover'; release_date=''} (Join-Path $fixtureDirectory 'valid.webp') $first.Id
    Image-Check ($replaced.StatusCode -eq 303) 'Replace cover'
    $newPath = (Image-Sql "SELECT cover_image FROM albums WHERE id=$($first.Id);").Trim()
    $ownedUploads.Add($newPath)
    Image-Check ($newPath -ne $oldPath -and (Test-Path -LiteralPath (Join-Path $workspace $oldPath))) 'Replacement keeps previous file'
    $member = Image-Post 'band_members' 'create' @{name='Image member fixture'; instrument=''} (Join-Path $fixtureDirectory 'polyglot.php')
    Image-Check ($member.StatusCode -eq 303) 'Decode real image regardless of original filename'
    $memberRecord = Image-Remember 'band_members'
    $rewritten = [IO.File]::ReadAllBytes((Join-Path $workspace $memberRecord.Path))
    Image-Check ($memberRecord.Path.EndsWith('.png') -and [Text.Encoding]::Latin1.GetString($rewritten) -notmatch 'upload-canary|<\?php') 'Rewrite removes embedded code and replaces filename'
    $editPage = Image-Get "/dashboard/index.php?module=band_members&action=edit&id=$($memberRecord.Id)"
    Image-Check ($editPage.Content -match 'Current image' -and $editPage.Content -match [regex]::Escape($memberRecord.Path)) 'Existing image preview'
    foreach ($filename in @('fake.png','oversize.png','wide.png')) {
        $rejected = Image-Post 'albums' 'create' @{title='Rejected image'; release_date=''} (Join-Path $fixtureDirectory $filename)
        Image-Check ($rejected.StatusCode -eq 422) "Reject $filename"
    }
    $stalePage = Image-Get "/dashboard/index.php?module=albums&action=edit&id=$($first.Id)"
    $null = Image-Sql "UPDATE albums SET title='Other admin saved' WHERE id=$($first.Id);"
    $before = @(Get-ChildItem -LiteralPath $uploadDirectory -File -Filter '*.png').Count
    $stale = Image-Post 'albums' 'edit' @{title='Old edit'; release_date=''} (Join-Path $fixtureDirectory 'valid.png') $first.Id $stalePage
    Image-Check ($stale.StatusCode -eq 409 -and @(Get-ChildItem -LiteralPath $uploadDirectory -File -Filter '*.png').Count -eq $before) 'Stale save creates no file'
    $null = Image-Sql "CREATE TRIGGER image_upload_failure BEFORE INSERT ON albums FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Isolated upload failure test';"
    try {
        $failed = Image-Post 'albums' 'create' @{title='Failed save'; release_date=''} (Join-Path $fixtureDirectory 'valid.png')
        Image-Check ($failed.StatusCode -eq 503 -and @(Get-ChildItem -LiteralPath $uploadDirectory -File -Filter '*.png').Count -eq $before) 'Failed database save removes new file'
    } finally { $null = Image-Sql 'DROP TRIGGER IF EXISTS image_upload_failure;' }
    foreach ($path in @('/dashboard/uploads/','/dashboard/uploads/.htaccess','/dashboard/uploads/.gitignore','/dashboard/uploads/test.php','/dashboard/uploads/../internal/images.php')) {
        Image-Check ((Image-Get $path).StatusCode -eq 404) "Reject private upload path $path"
    }
    $deleteResult = Image-Post 'band_members' 'delete' @{} '' $memberRecord.Id
    Image-Check ($deleteResult.StatusCode -eq 303 -and (Test-Path -LiteralPath (Join-Path $workspace $memberRecord.Path))) 'Deleting record retains stored image'
    [IO.File]::WriteAllText((Join-Path $EvidenceDirectory 'image-upload-evidence.json'), (@{formats=@('JPG','PNG','WebP'); storage='dashboard/uploads'; passed=@('real multipart uploads','public album covers','previews','keep old image','replace image','pixel rewrite','reject fake/oversize/dimensions','stale snapshot','rollback cleanup','private paths','retain stored images')} | ConvertTo-Json -Depth 4))
    Write-Output 'PASS: image uploads, formats/size/dimensions, pixel rewrite, storage, previews, Music, replacement, stale save and rollback cleanup.'
} finally {
    foreach ($record in $records) { $null = Image-Sql "DELETE FROM $($record.Module) WHERE id=$($record.Id);" }
    foreach ($path in $ownedUploads) {
        Image-Check ($path -match '^dashboard/uploads/[a-f0-9]{32}\.(png|jpg|webp)$') 'Cleanup stays in upload folder'
        $absolutePath = [IO.Path]::GetFullPath((Join-Path $workspace $path))
        Image-Check ($absolutePath.StartsWith($uploadDirectory + [IO.Path]::DirectorySeparatorChar)) 'Cleanup stays in workspace'
        if (Test-Path -LiteralPath $absolutePath) { Remove-Item -LiteralPath $absolutePath }
    }
    if (Test-Path -LiteralPath $fixtureScript) { Remove-Item -LiteralPath $fixtureScript }
}
