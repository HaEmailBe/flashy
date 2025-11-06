<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * Check database connection to flashy_testing schema
     */
    public function it_database_connection(): void
    {
        $database = config('database.connections.mysql.database');
        $this->assertEquals('flashy_testing', $database);
    }
}
