<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class StatisticsApiTest extends TestCase
{
    use RefreshDatabase;
    private $validSlug = "aaa-bbb-ccc";
    private $validTargetUrl = "http://example.com";
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all cache
        Cache::flush();
        
        // Or clear specific stores
        Cache::store('redis')->flush();
        Cache::store('array')->flush();
    }
    
    public function test_get_statistics_from_database_and_from_cache_by_slug(): void
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

        $this->assertDatabaseHas('links', [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => 1
        ]);

        $key = ":link-{$this->validSlug}";

        $this->assertTrue(Cache::has($key));

        foreach (range(1, 20) as $index) {
            $response = $this->get("/api/r/{$this->validSlug}");
            sleep(1);
        }

        $response->assertStatus(302);
        $response->assertRedirect($this->validTargetUrl);
        $response->assertSessionHas('success', true);
        $response->assertSessionHas('message', 'Link is active and exists in caching');

        // Get statistics from database
        $response = $this->get("/api/links/{$this->validSlug}/stats");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Get statistics from the database',
                'target_url' => $this->validTargetUrl,
                'hits_count' => 20,
            ]);

        $responseData = $response->json('data.formatted_ips');
        $this->assertCount(5, $responseData);

        // Get statistics from cache
        $response = $this->get("/api/links/{$this->validSlug}/stats");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Get statistics from the cache',
                'target_url' => $this->validTargetUrl,
                'hits_count' => 20,
            ]);

        $responseData = $response->json('data.formatted_ips');
        $this->assertCount(5, $responseData);

        $key = ":link-statistics-{$this->validSlug}";
        $this->assertTrue(Cache::has($key));
        sleep(61);
        $this->assertFalse(Cache::has($key));
    }

    public function test_statistics_by_slug_not_found(): void
    {
        $response = $this->get("/api/links/{$this->validSlug}/stats");
        $response->assertNotFound();
        $response->assertJson([
            'success' => true,
            'message' => 'Slug not found in the database'
        ]);
    }

    public function test_statistics_clear_cache(): void
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

        $this->assertDatabaseHas('links', [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => 1
        ]);

        $key = ":link-{$this->validSlug}";

        $this->assertTrue(Cache::has($key));

        foreach (range(1, 5) as $index) {
            $response = $this->get("/api/r/{$this->validSlug}");
            sleep(1);
        }

        $response->assertStatus(302);
        $response->assertRedirect($this->validTargetUrl);
        $response->assertSessionHas('success', true);
        $response->assertSessionHas('message', 'Link is active and exists in caching');

        $response = $this->get("/api/links/{$this->validSlug}/stats");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'target_url' => $this->validTargetUrl,
                //'hits_count' => 20,
            ]);

        $responseData = $response->json('data.formatted_ips');
        $this->assertCount(5, $responseData);

        $key = ":link-statistics-{$this->validSlug}";
        $this->assertTrue(Cache::has($key));

        $response = $this->get("/api/r/{$this->validSlug}");

        $this->assertFalse(Cache::has($key));
    }
}
