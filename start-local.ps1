#Requires -Version 5.1
param(
    [string]$Php,
    [ValidateRange(1024,65535)][int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
$projectDirectory = $PSScriptRoot
$dbUser = [Environment]::GetEnvironmentVariable('DB_USER', 'Process')
$dbPassword = [Environment]::GetEnvironmentVariable('DB_PASSWORD', 'Process')

function Invoke-ProjectPhp([string]$Executable, [string[]]$PhpArguments) {
    $startInfo = New-Object Diagnostics.ProcessStartInfo
    $startInfo.FileName = $Executable
    # These arguments are fixed PHP flags/code and resolved Windows file paths.
    $startInfo.Arguments = ($PhpArguments | ForEach-Object { '"' + $_.Replace('"', '\"') + '"' }) -join ' '
    $startInfo.WorkingDirectory = $projectDirectory
    $startInfo.UseShellExecute = $false
    $startInfo.CreateNoWindow = $true
    # An explicit empty value survives in the child's environment block on 5.1.
    # Setting $env:DB_PASSWORD = '' in Windows PowerShell would remove it instead.
    if ($null -ne $dbUser) { $startInfo.EnvironmentVariables['DB_USER'] = $dbUser }
    if ($null -ne $dbPassword) { $startInfo.EnvironmentVariables['DB_PASSWORD'] = $dbPassword }
    $phpProcess = New-Object Diagnostics.Process
    $phpProcess.StartInfo = $startInfo
    $started = $false
    try {
        $null = $phpProcess.Start()
        $started = $true
        # Yield to PowerShell so Ctrl+C can reach the finally block on 5.1.
        while (-not $phpProcess.WaitForExit(200)) { Start-Sleep -Milliseconds 50 }
        return $phpProcess.ExitCode
    } finally {
        if ($started -and -not $phpProcess.HasExited) {
            $phpProcess.Kill()
            $phpProcess.WaitForExit()
        }
        $phpProcess.Dispose()
    }
}

try {
    if (-not $Php) {
        $phpCommand = Get-Command php -CommandType Application -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($phpCommand) { $Php = $phpCommand.Source }
    }
    if (-not $Php) {
        # Use Laragon only to locate the PHP runtime, never as the document root.
        $laragonProcess = Get-Process -Name laragon -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($laragonProcess -and $laragonProcess.Path) {
            $phpDirectory = Join-Path (Split-Path -Parent $laragonProcess.Path) 'bin/php'
            $Php = Get-ChildItem -LiteralPath $phpDirectory -Directory |
                Sort-Object Name -Descending |
                ForEach-Object { Join-Path $_.FullName 'php.exe' } |
                Where-Object { Test-Path -LiteralPath $_ -PathType Leaf } |
                Where-Object {
                    (Invoke-ProjectPhp $_ @('-d', 'display_errors=0', '-r', 'exit(extension_loaded("pdo_mysql") && extension_loaded("mbstring") ? 0 : 1);')) -eq 0
                } |
                Select-Object -First 1
        }
    }
    if (-not $Php -or -not (Test-Path -LiteralPath $Php -PathType Leaf)) {
        throw 'PHP tidak ditemukan. Jalankan dengan -Php "path/ke/php.exe" atau gunakan terminal Laragon.'
    }
    $Php = (Resolve-Path -LiteralPath $Php).Path
    if ([string]::IsNullOrEmpty($dbUser)) {
        $dbUser = Read-Host 'Username MySQL lokal'
    }
    if ($null -eq $dbPassword) {
        $securePassword = Read-Host 'Password MySQL (Enter jika kosong)' -AsSecureString
        $passwordPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
        try {
            $dbPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($passwordPointer)
        } finally {
            [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
            $securePassword.Dispose()
        }
    }
    $check = 'try { require $argv[1]; database(); } catch (Throwable $e) { fwrite(STDERR, "Koneksi database gagal. Nyalakan MySQL dan periksa konfigurasi DB_*.\n"); exit(1); }'
    $checkExit = Invoke-ProjectPhp $Php @('-d', 'display_errors=0', '-r', $check, (Join-Path $projectDirectory 'app/database.php'))
    if ($checkExit -ne 0) { throw 'Server belum dijalankan karena koneksi database gagal.' }

    $portProbe = [Net.Sockets.TcpListener]::new([Net.IPAddress]::Loopback, $Port)
    try { $portProbe.Start() } catch { throw "Port $Port sedang dipakai. Gunakan -Port dengan nomor lain." } finally { $portProbe.Stop() }
    Write-Output "Folder website: $projectDirectory"
    Write-Output "Buka http://127.0.0.1:$Port/ (Ctrl+C untuk berhenti)."
    $serverExit = Invoke-ProjectPhp $Php @('-d', 'display_errors=0', '-S', "127.0.0.1:$Port", '-t', $projectDirectory, (Join-Path $projectDirectory 'router.php'))
    if ($serverExit -ne 0) { throw 'Server PHP berhenti dengan kesalahan.' }
} finally {
    $dbPassword = $null
}
