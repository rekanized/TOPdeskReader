<?php

namespace Tests\Feature;

use App\Http\Controllers\DatabaseController;
use Mockery;
use PDO;
use PDOStatement;
use Tests\TestCase;

class TopdeskConnectionTest extends TestCase
{
    public function test_connection_uses_cached_topdesk_configuration(): void
    {
        config()->set('topdesk', [
            'server' => 'sql.example,1433',
            'database' => 'topdesk',
            'username' => 'reader',
            'password' => 'example-password',
        ]);

        $pdo = new class extends PDO
        {
            public array $attributes = [];

            public function __construct() {}

            public function setAttribute(int $attribute, mixed $value): bool
            {
                $this->attributes[$attribute] = $value;

                return true;
            }
        };

        $database = new class($pdo) extends DatabaseController
        {
            public array $arguments = [];

            public function __construct(private PDO $pdo) {}

            protected function createPdo(string $dsn, string $username, string $password): PDO
            {
                $this->arguments = [$dsn, $username, $password];

                return $this->pdo;
            }
        };

        $this->assertSame($pdo, $database->connect());
        $this->assertSame(
            ['sqlsrv:server=sql.example,1433;Database=topdesk', 'reader', 'example-password'],
            $database->arguments
        );
        $this->assertSame(PDO::ERRMODE_EXCEPTION, $pdo->attributes[PDO::ATTR_ERRMODE]);
    }

    public function test_search_passes_named_parameters_to_the_topdesk_connection(): void
    {
        $row = [
            'topdesk_id' => '123',
            'id' => 'INC-123',
            'description' => 'Example ticket',
            'type' => 'ticket',
        ];

        $statement = Mockery::mock(PDOStatement::class);
        $statement->shouldReceive('execute')->once()->with([
            ':searchid' => '%INC%',
            ':searchdescription' => '%INC%',
            ':searchperson' => '%INC%',
        ])->andReturn(true);
        $statement->shouldReceive('fetchAll')->once()->with(PDO::FETCH_ASSOC)->andReturn([$row]);

        $pdo = Mockery::mock(PDO::class);
        $pdo->shouldReceive('prepare')->once()->with(Mockery::on(function (string $sql): bool {
            return str_contains($sql, 'SELECT TOP 100')
                && str_contains($sql, 'FROM incident')
                && str_contains($sql, 'FROM [change]')
                && str_contains($sql, 'FROM changeactivity');
        }))->andReturn($statement);

        $database = Mockery::mock(DatabaseController::class);
        $database->shouldReceive('connect')->once()->andReturn($pdo);
        $this->app->instance(DatabaseController::class, $database);

        $this->get('/tickets?searchvalue=INC&customerfilter=all&tickettypefilter=all&typefilter=all')
            ->assertOk()
            ->assertExactJson([$row]);
    }

    public function test_connection_check_reads_the_topdesk_tables(): void
    {
        config()->set('topdesk.database', 'topdesk');

        $statement = Mockery::mock(PDOStatement::class);
        $statement->shouldReceive('fetchColumn')->once()->andReturn('topdesk');

        $pdo = Mockery::mock(PDO::class);
        $pdo->shouldReceive('query')->once()->with('SELECT DB_NAME()')->andReturn($statement);

        foreach ([
            'incident', '[change]', 'changeactivity', 'vestiging', 'priority',
            'wijzigingstatus', 'actiedoor', 'change_priority', 'impact', 'urgency',
            'wijziging_impact', 'changebenefit', 'gebruiker',
            '[incident__memogeschiedenis]', '[change__memo_history]',
            '[changeactivity__memo_history]', 'changeactivity_status',
        ] as $table) {
            $pdo->shouldReceive('query')->once()->with("SELECT TOP 0 * FROM {$table}")->andReturn($statement);
        }

        $database = Mockery::mock(DatabaseController::class);
        $database->shouldReceive('connect')->once()->andReturn($pdo);
        $this->app->instance(DatabaseController::class, $database);

        $this->artisan('topdesk:check')
            ->expectsOutput('Connected to topdesk; the TOPdesk tables are readable.')
            ->assertExitCode(0);
    }
}
