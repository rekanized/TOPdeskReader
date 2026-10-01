<?php

declare(strict_types=1);

// Composer cannot load a native extension into its already running PHP process.
// Install it before dependencies are resolved, then check it in a new process.
if (PHP_VERSION_ID < 80500 || PHP_VERSION_ID >= 90000) {
    fwrite(STDERR, "This application requires PHP 8.5. Run Composer with PHP 8.5.\n");
    exit(1);
}

function sqlsrvIsAvailable(): bool
{
    return extension_loaded('pdo_sqlsrv')
        && in_array('sqlsrv', PDO::getAvailableDrivers(), true);
}

function sqlsrvIsAvailableInFreshProcess(array $descriptors): bool
{
    $check = proc_open(
        [PHP_BINARY, '-r', 'exit(extension_loaded("pdo_sqlsrv") && in_array("sqlsrv", PDO::getAvailableDrivers(), true) ? 0 : 1);'],
        $descriptors,
        $pipes,
    );

    return is_resource($check) && proc_close($check) === 0;
}

function writeLinuxIni(string $path, string $contents): bool
{
    if ((is_file($path) && is_writable($path)) || (! file_exists($path) && is_writable(dirname($path)))) {
        return file_put_contents($path, $contents, LOCK_EX) === strlen($contents);
    }

    $temporaryFile = tempnam(sys_get_temp_dir(), 'topdesk-sqlsrv-');

    if ($temporaryFile === false || file_put_contents($temporaryFile, $contents) !== strlen($contents)) {
        return false;
    }

    $command = is_file($path)
        ? ['sudo', 'cp', '--', $temporaryFile, $path]
        : ['sudo', 'install', '-m', '0644', $temporaryFile, $path];
    $process = proc_open($command, [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes);
    $success = is_resource($process) && proc_close($process) === 0;
    unlink($temporaryFile);

    return $success;
}

function enableLinuxSqlsrv(): bool
{
    $module = rtrim((string) ini_get('extension_dir'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'pdo_sqlsrv.so';

    if (! is_file($module)) {
        return false;
    }

    $mainIni = php_ini_loaded_file();
    $scanned = php_ini_scanned_files();
    $firstScannedIni = is_string($scanned) ? trim(explode(',', $scanned)[0]) : '';

    if ($firstScannedIni === '') {
        return false;
    }

    $driverIni = dirname($firstScannedIni).DIRECTORY_SEPARATOR.'zz-pdo_sqlsrv.ini';
    $extensionLine = '/^[ \t]*extension[ \t]*=[ \t]*["\']?pdo_sqlsrv(?:\.so)?["\']?[ \t]*(?:;[^\r\n]*)?(?:\r?\n|$)/mi';

    if (is_string($mainIni)) {
        $settings = file_get_contents($mainIni);

        if (is_string($settings)) {
            $cleanSettings = preg_replace($extensionLine, '', $settings);

            if ($cleanSettings === null || ($cleanSettings !== $settings && ! writeLinuxIni($mainIni, $cleanSettings))) {
                return false;
            }
        }
    }

    if (is_file($driverIni)) {
        $settings = file_get_contents($driverIni);

        return is_string($settings) && preg_match($extensionLine, $settings) === 1;
    }

    return writeLinuxIni($driverIni, 'extension=pdo_sqlsrv'.PHP_EOL);
}

$descriptors = [0 => STDIN, 1 => STDOUT, 2 => STDERR];

if (sqlsrvIsAvailable()) {
    fwrite(STDOUT, "PDO SQL Server driver is already available.\n");
    exit(0);
}

if (PHP_OS_FAMILY === 'Linux' && enableLinuxSqlsrv() && sqlsrvIsAvailableInFreshProcess($descriptors)) {
    fwrite(STDOUT, "PDO SQL Server driver is now available.\n");
    exit(0);
}

const PIE_VERSION = '1.5.1';
const PIE_SHA256 = 'f82fb7a81aee71a44b5a0b0b7b35f0bf17502e7d19bff7e890b6f4a1964adb80';

$pie = sys_get_temp_dir().DIRECTORY_SEPARATOR.'topdeskreader-pie-'.PIE_VERSION.'.phar';

if (! is_file($pie) || ! hash_equals(PIE_SHA256, hash_file('sha256', $pie))) {
    $url = 'https://github.com/php/pie/releases/download/'.PIE_VERSION.'/pie.phar';
    fwrite(STDOUT, 'Downloading PHP Installer for Extensions (PIE) '.PIE_VERSION."...\n");

    if (extension_loaded('curl')) {
        $curl = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_TIMEOUT => 120,
        ];

        if (PHP_OS_FAMILY === 'Windows' && defined('CURLSSLOPT_NATIVE_CA')) {
            $options[CURLOPT_SSL_OPTIONS] = CURLSSLOPT_NATIVE_CA;
        }

        curl_setopt_array($curl, $options);
        $contents = curl_exec($curl);
        $downloadError = curl_error($curl);
    } else {
        $contents = @file_get_contents($url);
        $downloadError = 'Configure PHP OpenSSL certificate trust or enable curl.';
    }

    if (! is_string($contents) || ! hash_equals(PIE_SHA256, hash('sha256', $contents))) {
        fwrite(STDERR, "Could not download or verify PIE from {$url}. {$downloadError}\n");
        exit(1);
    }

    if (file_put_contents($pie, $contents) !== strlen($contents)) {
        fwrite(STDERR, "Could not save PIE to {$pie}. Check write permissions.\n");
        exit(1);
    }
}

fwrite(STDOUT, 'Installing Microsoft PDO SQL Server driver for '.PHP_BINARY."...\n");

$pieCommand = [PHP_BINARY, $pie, 'install', 'microsoft/pdo_sqlsrv:5.13.3', '--no-interaction'];

if (PHP_OS_FAMILY === 'Linux') {
    $pieCommand[] = '--skip-enable-extension';
}

$process = proc_open(
    $pieCommand,
    $descriptors,
    $pipes,
    dirname(__DIR__),
);

if (! is_resource($process) || proc_close($process) !== 0) {
    fwrite(STDERR, "PIE could not install pdo_sqlsrv.\n");

    if (PHP_OS_FAMILY === 'Linux') {
        fwrite(STDERR, "If the compiler reports missing sql.h, install unixODBC development headers, then run composer install again:\n");
        fwrite(STDERR, "  Debian/Ubuntu: sudo apt-get install unixodbc-dev\n");
        fwrite(STDERR, "  RHEL/Fedora:   sudo dnf install unixODBC-devel\n");
        fwrite(STDERR, "  Alpine:        sudo apk add unixodbc-dev\n");
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        fwrite(STDERR, "If the compiler reports missing sql.h, install unixODBC with brew install unixodbc.\n");
    }

    fwrite(STDERR, "Also ensure Microsoft ODBC Driver for SQL Server 17 or 18 is installed.\n");
    exit(1);
}

if (! sqlsrvIsAvailableInFreshProcess($descriptors)) {
    // PIE may consider the package installed even when this PHP ini was later changed.
    $ini = php_ini_loaded_file();

    if (PHP_OS_FAMILY === 'Linux') {
        enableLinuxSqlsrv();
    } elseif (is_string($ini) && is_writable($ini)) {
        $settings = file_get_contents($ini);

        if (is_string($settings) && ! preg_match('/^\s*extension\s*=\s*[^\r\n]*pdo_sqlsrv/im', $settings)) {
            file_put_contents($ini, PHP_EOL.'extension=pdo_sqlsrv'.PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}

if (! sqlsrvIsAvailableInFreshProcess($descriptors)) {
    fwrite(STDERR, "PIE finished, but a new PHP process cannot load pdo_sqlsrv.\n");

    if (PHP_OS_FAMILY === 'Linux') {
        fwrite(STDERR, "Run php --ini. Remove any extension=pdo_sqlsrv line from the main php.ini and enable it in a scanned ini file named zz-pdo_sqlsrv.ini, after the ini file that loads PDO.\n");
    } else {
        fwrite(STDERR, "Check the CLI php.ini and extension dependencies.\n");
    }

    exit(1);
}

fwrite(STDOUT, "PDO SQL Server driver is available.\n");
