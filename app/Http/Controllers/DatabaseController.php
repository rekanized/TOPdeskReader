<?php

namespace App\Http\Controllers;

use PDO;
use PDOException;

class DatabaseController extends Controller
{
    public function connect(): ?PDO
    {
        $server = config('topdesk.server');
        $database = config('topdesk.database');

        if (! $server || ! $database) {
            \Log::error('TOPdesk SQL Server and database must be configured.');

            return null;
        }

        try {
            $trustServerCertificate = config('topdesk.trust_server_certificate', true) ? 'true' : 'false';
            $conn = $this->createPdo(
                "sqlsrv:Server={$server};Database={$database};Encrypt=true;TrustServerCertificate={$trustServerCertificate}",
                (string) config('topdesk.username'),
                (string) config('topdesk.password')
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (PDOException $e) {
            $context = [];

            if (str_contains($e->getMessage(), 'could not find driver')) {
                $context = [
                    'php_sapi' => PHP_SAPI,
                    'php_version' => PHP_VERSION,
                    'loaded_ini' => php_ini_loaded_file() ?: null,
                    'scanned_ini_files' => php_ini_scanned_files() ?: null,
                    'pdo_drivers' => PDO::getAvailableDrivers(),
                ];
            }

            \Log::error('Database connection failed: '.$e->getMessage(), $context);

            return null;
        }
    }

    protected function createPdo(string $dsn, string $username, string $password): PDO
    {
        return new PDO($dsn, $username, $password);
    }
}
