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
            $conn = $this->createPdo(
                "sqlsrv:server={$server};Database={$database}",
                (string) config('topdesk.username'),
                (string) config('topdesk.password')
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (PDOException $e) {
            \Log::error('Database connection failed: '.$e->getMessage());

            return null;
        }
    }

    protected function createPdo(string $dsn, string $username, string $password): PDO
    {
        return new PDO($dsn, $username, $password);
    }
}
