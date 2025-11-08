<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ToggleApiTest extends TestCase
{
    use RefreshDatabase;
    private $validSlug = "aaa-bbb-ccc";
    private $validTargetUrl = "http://example.com";
    /**
     * A basic feature test example.
     */
    public function test_link_toggle_when_is_active_is_truthy(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => 1,
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link created successfully',
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
            ]);

        $data = [
            'slug' => $this->validSlug,
        ];

        $response = $this->putJson('/api/links', $data, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link updated successfully',
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
            ]);

        $key = ":link-{$this->validSlug}";
        $this->assertFalse(Cache::has($key));
    }

    public function test_link_toggle_when_is_active_is_falsy(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => 0,
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link created successfully',
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
            ]);

        $data = [
            'slug' => $this->validSlug,
        ];

        $response = $this->putJson('/api/links', $data, $headers);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link updated successfully',
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
            ]);

        $key = ":link-{$this->validSlug}";
        $this->assertTrue(Cache::has($key));
    }

    public function test_link_toggle_is_active_when_slug_not_exists(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => 0,
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link created successfully',
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
            ]);

        $data = [
            'slug' => $this->validSlug.'R',
        ];

        $response = $this->putJson('/api/links', $data, $headers);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'success' => false,
                'message'=> 'No query results for model [App\Models\Links].',
            ]);

    }
}
