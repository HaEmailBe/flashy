<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
class BotFilterMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private $validSlug = "aaa-bbb-ccc";
    private $validTargetUrl = "http://example.com";

    public function test_blocks_requests_from_bots(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
            'User-Agent' => 'dotbot'
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(403);
        $response->assertSee('Access denied for this user agent.');
    }

    public function test_allows_requests_from_bots(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
            'User-Agent' => 'Mozilla/5.0'
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(201);
    }
}
