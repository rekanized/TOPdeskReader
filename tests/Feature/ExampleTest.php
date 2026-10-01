<?php

namespace Tests\Feature;

use App\Http\Controllers\DatabaseController;
use Mockery;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_api_reports_a_database_connection_failure(): void
    {
        $database = Mockery::mock(DatabaseController::class);
        $database->shouldReceive('connect')->once()->andReturn(null);
        $this->app->instance(DatabaseController::class, $database);

        $this->get('/api/customers')
            ->assertStatus(500)
            ->assertExactJson(['error' => 'Database connection failed']);
    }
}
