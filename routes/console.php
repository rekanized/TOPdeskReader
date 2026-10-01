<?php

use App\Http\Controllers\DatabaseController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('topdesk:check', function () {
    $connection = app(DatabaseController::class)->connect();

    if (! $connection) {
        $this->error('TOPdesk SQL Server connection failed. Check the MSSQL settings and Laravel log.');

        return 1;
    }

    try {
        $database = $connection->query('SELECT DB_NAME()')->fetchColumn();

        if (strcasecmp((string) $database, (string) config('topdesk.database')) !== 0) {
            $this->error('Connected to an unexpected SQL Server database.');

            return 1;
        }

        foreach ([
            'incident',
            '[change]',
            'changeactivity',
            'vestiging',
            'priority',
            'wijzigingstatus',
            'actiedoor',
            'change_priority',
            'impact',
            'urgency',
            'wijziging_impact',
            'changebenefit',
            'gebruiker',
            '[incident__memogeschiedenis]',
            '[change__memo_history]',
            '[changeactivity__memo_history]',
            'changeactivity_status',
        ] as $table) {
            $connection->query("SELECT TOP 0 * FROM {$table}");
        }

        $this->info("Connected to {$database}; the TOPdesk tables are readable.");

        return 0;
    } catch (PDOException $e) {
        $this->error('TOPdesk schema check failed: '.$e->getMessage());

        return 1;
    }
})->purpose('Check the read-only TOPdesk SQL Server connection and tables');
