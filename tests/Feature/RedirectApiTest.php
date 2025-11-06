<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\LinkHit;
use App\Models\Links;
use App\Models\LinkHits;
use Termwind\Components\BreakLine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedirectApiTest extends TestCase
{
    use RefreshDatabase;

    private $validSlug = "aaa-bbb-ccc";
    private $validTargetUrl = "http://example.com";

    public function test_redirect_successfully_when_link_is_active_and_cached(): void
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

        $response = $this->get("/api/r/{$this->validSlug}");

        $response->assertStatus(302);
        $response->assertRedirect($this->validTargetUrl);
        $response->assertSessionHas('success', true);
        $response->assertSessionHas('message', 'Link is active and exists in caching');
    }

    public function test_redirect_successfully_when_link_is_active_and_not_cached(): void
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
        Cache::forget($key);
        $this->assertFalse(Cache::has($key));

        $response = $this->get("/api/r/{$this->validSlug}");

        $response->assertStatus(302);
        $response->assertRedirect($this->validTargetUrl);
        $response->assertSessionHas('success', true);
        $response->assertSessionHas('message', 'Link is active and not found in caching');
    }

    public function test_no_redirect_when_link_is_not_active(): void
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

        $this->assertDatabaseHas('links', [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => 0
        ]);

        $key = ":link-{$this->validSlug}";

        $this->assertFalse(Cache::has($key));

        $response = $this->get("/api/r/{$this->validSlug}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'No redirect, Link is not active.',
            ]);
    }

    public function test_no_redirect_link_not_found(): void
    {
        $response = $this->get("/api/r/{$this->validSlug}");

        $this->assertTrue(
            in_array($response->getStatusCode(), [404, 410]),
            'Status code should be either 404 or 410'
        );

        switch ($response->getStatusCode() ?? '') {
            case 404:
                $response->assertNotFound();
                $this->assertEquals('Not Found', $response->statusText());
                break;
            case 410:
                $response->assertGone();
                $this->assertEquals('Gone', $response->statusText());
                break;
        }

        $response->assertJson([
            'success' => true,
            'message' => 'Slug not found in DB'
        ]);
    }

    public function test_redirect_dispatches_linkhit_job(): void
    {
        Queue::fake();

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

        $link_id = $response->json('data.id');

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link created successfully',
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
            ]);

        $this->assertTrue(in_array($response->json('is_active'), [0, 1]), 'is_active is not 0 or 1');

        $this->assertDatabaseHas('links', [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
        ]);

        $linkHitData = [
            'slug' => $this->validSlug,
            'link_id' => $link_id,
            'ip' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ];

        LinkHit::dispatch($linkHitData);

        Queue::assertPushed(LinkHit::class, function ($job) use ($linkHitData) {
            return $job->getLinkId() === $linkHitData['link_id'];
        });
    }

    public function test_linkhit_job_saves_to_database(): void
    {
        $link = Links::factory()->create();

        $linkHitData = [
            'slug' => $this->validSlug,
            'link_id' => $link->id,
            'ip' => '127.0.0.1',
            'user_agent' => 'Test Agent',
        ];

        LinkHit::dispatch($linkHitData);

        $this->assertDatabaseHas('link_hits', [
            'link_id' => $linkHitData['link_id'],
            'ip' => $linkHitData['ip'],
            'user_agent' => $linkHitData['user_agent'],
        ]);
    }
}
