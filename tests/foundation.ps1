param(
    [Parameter(Mandatory)][string]$Php,
    [Parameter(Mandatory)][string]$MySqlBin
)

$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$runDirectory = Join-Path $workspace ('.runtime/foundation-' + [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $runDirectory -Force | Out-Null
$mysqlServer = $null
$phpServer = $null
$failureServer = $null
$missingConfigServer = $null
$adminServer = $null
$concurrentAdminServer = $null
$processState = @{ Index = 0 }

function Free-Port {
    $listener = [Net.Sockets.TcpListener]::new([Net.IPAddress]::Loopback, 0)
    $listener.Start()
    try { return $listener.LocalEndpoint.Port } finally { $listener.Stop() }
}

function Start-Tool {
    param([string]$Executable, [string[]]$Arguments, [hashtable]$Environment = @{}, [string]$InputFile)
    $processState.Index++
    $prefix = Join-Path $runDirectory "process-$($processState.Index)"
    $options = @{
        FilePath = $Executable
        ArgumentList = @($Arguments | ForEach-Object { '"' + $_.Replace('"', '\"') + '"' })
        WorkingDirectory = $workspace
        PassThru = $true
        WindowStyle = 'Hidden'
        RedirectStandardOutput = "$prefix.out.log"
        RedirectStandardError = "$prefix.err.log"
        Environment = $Environment
    }
    if ($InputFile) { $options.RedirectStandardInput = $InputFile }
    $process = Start-Process @options
    return [pscustomobject]@{ Process = $process; Output = "$prefix.out.log"; Error = "$prefix.err.log" }
}

function Run-Tool {
    param([string]$Executable, [string[]]$Arguments, [hashtable]$Environment = @{}, [string]$InputFile)
    $job = Start-Tool $Executable $Arguments $Environment $InputFile
    if (-not $job.Process.WaitForExit(55000)) {
        $job.Process.Kill($true)
        throw "Tool timed out; see local logs in $runDirectory"
    }
    if ($job.Process.ExitCode -ne 0) { throw "Tool failed; see $($job.Error)" }
    return [IO.File]::ReadAllText($job.Output)
}

function Assert-True([bool]$Condition, [string]$Message) {
    if (-not $Condition) { throw $Message }
}

function Http([int]$Port, [string]$Path, [string]$Method = 'GET') {
    return Invoke-WebRequest -Uri "http://127.0.0.1:$Port$Path" -Method $Method -SkipHttpErrorCheck -TimeoutSec 8
}

try {
    $dbPort = Free-Port
    $httpPort = Free-Port
    $failurePort = Free-Port
    $missingConfigPort = Free-Port
    $rootPassword = [Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(24))
    $readerPassword = [Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(24))
    $writerPassword = [Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(24))
    $adminPassword = [Convert]::ToHexString([Security.Cryptography.RandomNumberGenerator]::GetBytes(24))
    $dataDirectory = Join-Path $runDirectory 'mysql-data'
    $initFile = Join-Path $runDirectory 'initialize.sql'
    $clientFile = Join-Path $runDirectory 'client.ini'
    $sqlFile = Join-Path $runDirectory 'query.sql'
    $serverExe = Join-Path $MySqlBin 'mysqld.exe'
    $clientExe = Join-Path $MySqlBin 'mysql.exe'
    $baseDirectory = Split-Path -Parent $MySqlBin
    [IO.File]::WriteAllText($initFile, "ALTER USER 'root'@'localhost' IDENTIFIED BY '$rootPassword';")
    [IO.File]::WriteAllText($clientFile, "[client]`nuser=root`npassword=$rootPassword`nhost=127.0.0.1`nport=$dbPort`nprotocol=tcp`ndefault-character-set=utf8mb4`n")
    $null = Run-Tool $serverExe @('--no-defaults', '--initialize', "--basedir=$baseDirectory", "--datadir=$dataDirectory")
    $mysqlServer = Start-Tool $serverExe @(
        '--no-defaults', "--basedir=$baseDirectory", "--datadir=$dataDirectory",
        "--port=$dbPort", '--bind-address=127.0.0.1', '--mysqlx=OFF',
        '--skip-log-bin', "--init-file=$initFile", "--log-error=$runDirectory/mysql-server.log",
        "--pid-file=$runDirectory/mysql.pid"
    )

    $ready = $false
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        if ($mysqlServer.Process.HasExited) { throw "Isolated MySQL exited; see $runDirectory/mysql-server.log" }
        $probe = [Net.Sockets.TcpClient]::new()
        try { $probe.Connect('127.0.0.1', $dbPort); $ready = $true; break } catch { Start-Sleep -Milliseconds 250 } finally { $probe.Dispose() }
    }
    Assert-True $ready 'Isolated MySQL did not become ready.'
    $schema = [IO.File]::ReadAllText((Join-Path $workspace 'mcr_database_mysql.db')).Replace('`mcr_website`', '`mcr_foundation_test`')
    $schema += "`nCREATE USER 'mcr_reader'@'127.0.0.1' IDENTIFIED BY '$readerPassword';`nGRANT SELECT ON mcr_foundation_test.* TO 'mcr_reader'@'127.0.0.1';`n"
    $schema += "`nCREATE USER 'mcr_writer'@'127.0.0.1' IDENTIFIED BY '$writerPassword';`nGRANT SELECT,INSERT,UPDATE,DELETE ON mcr_foundation_test.* TO 'mcr_writer'@'127.0.0.1';`n"
    [IO.File]::WriteAllText($sqlFile, $schema, [Text.UTF8Encoding]::new($false))
    $null = Run-Tool $clientExe @("--defaults-extra-file=$clientFile") @{} $sqlFile
    [IO.File]::WriteAllText($sqlFile, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='mcr_foundation_test'; SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema='mcr_foundation_test';")
    $metadata = Run-Tool $clientExe @("--defaults-extra-file=$clientFile", '--batch', '--skip-column-names') @{} $sqlFile
    Assert-True ($metadata.Trim() -match '^15\s+11$') 'Expected 15 tables and 11 foreign keys after import.'
    Write-Output 'PASS: original schema imported into isolated database (15 tables, 11 foreign keys).'
    $tableNames = [regex]::Matches($schema, 'CREATE TABLE IF NOT EXISTS `([^`]+)`') | ForEach-Object { $_.Groups[1].Value }
    $showCreate = 'USE mcr_foundation_test;' + (($tableNames | Sort-Object | ForEach-Object { 'SHOW CREATE TABLE `' + $_ + '`;' }) -join "`n")
    [IO.File]::WriteAllText($sqlFile, $showCreate)
    $referenceSchema = Run-Tool $clientExe @("--defaults-extra-file=$clientFile", '--batch', '--skip-column-names', '--raw') @{} $sqlFile
    [IO.File]::WriteAllText((Join-Path $runDirectory 'schema-reference.txt'), $referenceSchema)

    $dbEnvironment = @{ DB_HOST='127.0.0.1'; DB_PORT="$dbPort"; DB_NAME='mcr_foundation_test'; DB_USER='mcr_reader'; DB_PASSWORD=$readerPassword }
    Write-Output (Run-Tool $Php @('tests/database.php') $dbEnvironment).Trim()
    $phpFiles = @('router.php','index.php','music.php','tour.php','news.php','news-detail.php') + @(Get-ChildItem -LiteralPath (Join-Path $workspace 'app'),(Join-Path $workspace 'api'),(Join-Path $workspace 'tests'),(Join-Path $workspace 'dashboard'),(Join-Path $workspace 'dashboard-auth') -Filter '*.php' -Recurse -File | ForEach-Object { $_.FullName })
    foreach ($phpFile in $phpFiles) {
        $null = Run-Tool $Php @('-l', $phpFile)
    }
    Write-Output "PASS: PHP syntax checks ($($phpFiles.Count) files)."
    $phpServer = Start-Tool $Php @('-d','display_errors=0','-S',"127.0.0.1:$httpPort",'router.php') $dbEnvironment
    Start-Sleep -Milliseconds 500
    $empty = Http $httpPort '/api/albums.php'
    Assert-True ($empty.StatusCode -eq 200 -and @((($empty.Content | ConvertFrom-Json).data)).Count -eq 0) 'Empty album response failed.'
    Write-Output "DEMO BEFORE (empty test database): HTTP $($empty.StatusCode) $($empty.Content)"
    [IO.File]::WriteAllText($sqlFile, "USE mcr_foundation_test; INSERT INTO albums (title,release_date,cover_image) VALUES ('Dummy older album','2001-01-01',NULL), ('Dummy 😀 <script>test</script>','2006-01-01',NULL);", [Text.UTF8Encoding]::new($false))
    $null = Run-Tool $clientExe @("--defaults-extra-file=$clientFile") @{} $sqlFile
    $albums = Http $httpPort '/api/albums.php'
    $payload = $albums.Content | ConvertFrom-Json
    Assert-True ($albums.StatusCode -eq 200 -and $payload.data.Count -eq 2) 'Album read failed.'
    Assert-True ($payload.data[0].title -eq 'Dummy 😀 <script>test</script>' -and $payload.data[0].cover_image -eq $null) 'UTF-8, ordering or null handling failed.'
    Assert-True ($albums.Headers['Content-Type'] -match 'application/json' -and $albums.Headers['X-Content-Type-Options'] -eq 'nosniff') 'JSON security headers missing.'
    Write-Output "DEMO AFTER (2 dummy albums inserted): HTTP $($albums.StatusCode) $($albums.Content)"
    $wrongMethod = Http $httpPort '/api/albums.php' 'POST'
    Assert-True ($wrongMethod.StatusCode -eq 405) 'POST must return 405.'
    Write-Output "DEMO POST (read-only endpoint): HTTP $($wrongMethod.StatusCode) $($wrongMethod.Content)"
    foreach ($path in @('/.env','/.env.example','/.git/config','/app/database.php','/agent/RULES.md','/tests/database.php','/mcr_database_mysql.db','/router.php','/assets/../app/database.php','/assets/%2e%2e/app/database.php','/missing')) {
        Assert-True ((Http $httpPort $path).StatusCode -eq 404) "Private/unknown path was served: $path"
    }
    foreach ($path in @('/','/music.php','/base.css','/script.js','/assets/hero-concert.png')) {
        Assert-True ((Http $httpPort $path).StatusCode -eq 200) "Static page/asset failed: $path"
    }
    Write-Output 'PASS: HTTP empty/populated albums, UTF-8, nulls, ordering, headers, 405, static routes and private/traversal paths.'

    $badEnvironment = $dbEnvironment.Clone()
    $badEnvironment.DB_PASSWORD = 'intentionally-invalid-test-password'
    $failureServer = Start-Tool $Php @('-d','display_errors=0','-S',"127.0.0.1:$failurePort",'router.php') $badEnvironment
    $missingEnvironment = $dbEnvironment.Clone()
    $missingEnvironment.DB_USER = ''
    $missingConfigServer = Start-Tool $Php @('-d','display_errors=0','-S',"127.0.0.1:$missingConfigPort",'router.php') $missingEnvironment
    Start-Sleep -Milliseconds 500
    $staticWithFailedDatabase = Http $failurePort '/about.html'
    Assert-True ($staticWithFailedDatabase.StatusCode -eq 200) 'Static About page must remain independent of database credentials.'
    Write-Output "DEMO STATIC UI (invalid database credentials): HTTP $($staticWithFailedDatabase.StatusCode) /about.html still loads; this page is not database-driven."
    foreach ($port in @($failurePort,$missingConfigPort)) {
        $failure = Http $port '/api/albums.php'
        Assert-True ($failure.StatusCode -eq 503 -and ($failure.Content | ConvertFrom-Json).error.code -eq 'service_unavailable') 'Expected safe 503 response.'
        Assert-True ($failure.Content -notmatch 'SQLSTATE|PDOException|mcr_reader|intentionally-invalid|Stack trace|password') 'Error response disclosed technical/secret details.'
        $scenario = if ($port -eq $failurePort) { 'invalid database credentials' } else { 'missing database user' }
        Write-Output "DEMO FAILURE ($scenario): HTTP $($failure.StatusCode) $($failure.Content)"
    }
    $evidence = [ordered]@{
        database = 'isolated mcr_foundation_test (not the team database)'
        before = @{ status = [int]$empty.StatusCode; body = ($empty.Content | ConvertFrom-Json) }
        after = @{ status = [int]$albums.StatusCode; body = $payload }
        wrong_method = @{ status = [int]$wrongMethod.StatusCode; body = ($wrongMethod.Content | ConvertFrom-Json) }
        static_ui_with_invalid_credentials = @{ path = '/about.html'; status = [int]$staticWithFailedDatabase.StatusCode }
        missing_configuration = @{ status = [int]$failure.StatusCode; body = ($failure.Content | ConvertFrom-Json) }
    }
    $evidencePath = Join-Path $runDirectory 'http-evidence.json'
    [IO.File]::WriteAllText($evidencePath, ($evidence | ConvertTo-Json -Depth 8), [Text.UTF8Encoding]::new($false))
    Write-Output "DEMO EVIDENCE: $evidencePath"
    Write-Output 'PASS: authentication failure and missing configuration return safe 503 JSON.'
    & (Join-Path $PSScriptRoot 'content.ps1') -HttpPort $httpPort -FailurePort $failurePort -EvidenceDirectory $runDirectory -RunSql {
        param([string]$Sql)
        [IO.File]::WriteAllText($sqlFile, $Sql, [Text.UTF8Encoding]::new($false))
        $null = Run-Tool $clientExe @("--defaults-extra-file=$clientFile") @{} $sqlFile
    }
    $writerEnvironment = $dbEnvironment.Clone()
    $writerEnvironment.DB_USER = 'mcr_writer'
    $writerEnvironment.DB_PASSWORD = $writerPassword
    $adminPort = Free-Port
    $adminServer = Start-Tool $Php @('-d','display_errors=0','-S',"127.0.0.1:$adminPort",'router.php') $writerEnvironment
    $concurrentPort = Free-Port
    $concurrentAdminServer = Start-Tool $Php @('-d','display_errors=0','-S',"127.0.0.1:$concurrentPort",'router.php') $writerEnvironment
    Start-Sleep -Milliseconds 500
    # Direct dashboard is tested before provisioning any users.
    & (Join-Path $workspace 'dashboard/internal/test.ps1') -HttpPort $adminPort -FailurePort $failurePort -ConcurrentPort $concurrentPort -EvidenceDirectory $runDirectory -RunSql {
        param([string]$Sql)
        [IO.File]::WriteAllText($sqlFile, $Sql, [Text.UTF8Encoding]::new($false))
        return Run-Tool $clientExe @("--defaults-extra-file=$clientFile", '--batch', '--skip-column-names') @{} $sqlFile
    }
    & (Join-Path $workspace 'dashboard/internal/test-images.ps1') -Php $Php -HttpPort $adminPort -EvidenceDirectory $runDirectory -RunSql {
        param([string]$Sql)
        [IO.File]::WriteAllText($sqlFile, $Sql, [Text.UTF8Encoding]::new($false))
        return Run-Tool $clientExe @("--defaults-extra-file=$clientFile", '--batch', '--skip-column-names') @{} $sqlFile
    }
    $passwordFile = Join-Path $runDirectory 'admin-password.tmp'
    [IO.File]::WriteAllText($passwordFile, $adminPassword)
    $null = Run-Tool $Php @('dashboard-auth/internal/create-admin.php', 'Test Admin', 'admin@example.test') $writerEnvironment $passwordFile
    Remove-Item -LiteralPath $passwordFile
    & (Join-Path $workspace 'dashboard-auth/internal/test.ps1') -HttpPort $adminPort -FailurePort $failurePort -Password $adminPassword -EvidenceDirectory $runDirectory -RunSql {
        param([string]$Sql)
        [IO.File]::WriteAllText($sqlFile, $Sql, [Text.UTF8Encoding]::new($false))
        return Run-Tool $clientExe @("--defaults-extra-file=$clientFile", '--batch', '--skip-column-names') @{} $sqlFile
    }
    Write-Output "PASS: foundation checks complete. Isolated run artifacts: $runDirectory"
} finally {
    if ($null -ne $mysqlServer -and (Test-Path -LiteralPath (Join-Path $runDirectory 'client.ini'))) {
        try {
            $null = Run-Tool (Join-Path $MySqlBin 'mysqladmin.exe') @("--defaults-extra-file=$clientFile", '--connect-timeout=3', 'shutdown')
            # mysqladmin can return before the Windows server/monitor fully exits.
            $null = $mysqlServer.Process.WaitForExit(15000)
        } catch {
            Write-Warning 'Graceful shutdown failed; stopping only the isolated test process tree.'
        }
    }
    foreach ($job in @($concurrentAdminServer,$adminServer,$missingConfigServer,$failureServer,$phpServer,$mysqlServer)) {
        if ($null -ne $job -and -not $job.Process.HasExited) {
            $job.Process.Kill($true)
            $job.Process.WaitForExit()
        }
    }
    # Retain test data/logs for inspection, but remove generated credential files.
    foreach ($secretFile in @('initialize.sql','client.ini','query.sql','admin-password.tmp')) {
        $secretPath = Join-Path $runDirectory $secretFile
        if (Test-Path -LiteralPath $secretPath) { Remove-Item -LiteralPath $secretPath }
    }
}
