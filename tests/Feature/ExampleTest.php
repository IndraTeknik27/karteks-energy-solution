<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Basic smoke test — verify the API health endpoint responds.
     * Uses SQLite in-memory so no MySQL-specific migrations needed.
     */
    public function test_the_api_health_returns_successful_response(): void
    {
        $response = $this->get('/api/health');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
