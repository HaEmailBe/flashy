<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Links;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class LinkApiTest extends TestCase
{
    use RefreshDatabase;
    private $validSlug = "aaa-bbb-ccc";
    private $validTargetUrl = "http://example.com";
    private $inValidTargetUrl = "c://example.com";
    private $inValidApiKey = 'secret111';
    private $inValidIsActive = 2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear all cache
        Cache::flush();
        
        // Or clear specific stores
        Cache::store('redis')->flush();
        Cache::store('array')->flush();
    }

    public function test_create_a_link_successfully(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $is_active = random_int(0, 1);

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => $is_active,
        ];

        $response = $this->postJson('/api/links', $data, $headers);

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

        $key = ":link-{$this->validSlug}";

        switch ($is_active) {
            case 0:
                $this->assertFalse(Cache::has($key));
                break;
            case 1:
                $this->assertTrue(Cache::has($key));
                break;
        }
    }

    public function test_create_a_link_when_api_key_is_missing(): void
    {
        $headers = [
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'success' => false,
            ]);

        $this->assertStringContainsString('Invalid API key', $response->json('message'));

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_api_key_is_invalid(): void
    {
        $headers = [
            'X-Api-Key' => $this->inValidApiKey,
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'success' => false,
            ]);

        $this->assertStringContainsString('Invalid API key', $response->json('message'));

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_slug_is_missing(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'target_url' => $this->validTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link created successfully'
            ]);

        $this->assertArrayHasKey('slug', $response->json()['data']);

        $this->assertEquals(1, Links::count());
    }

    public function test_create_a_link_when_target_url_is_missing(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('target_url');

        $errors = $response->json('errors');

        $this->assertEquals('The target url field is required.', $errors['target_url'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_target_url_is_not_valid(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->inValidTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('target_url');

        $errors = $response->json('errors');

        $this->assertEquals('The target url must must be a valid URL.', $errors['target_url'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_target_url_is_to_long(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl . '/' . str_repeat('a', 239),
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('target_url');

        $errors = $response->json('errors');

        $this->assertEquals('The target url must not exceed 255 characters.', $errors['target_url'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_slug_is_to_long(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug . '/' . str_repeat('a', 244),
            'target_url' => $this->validTargetUrl,
            'is_active' => random_int(0, 1),
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('slug');

        $errors = $response->json('errors');

        $maxLength = 254 - (int) Str::length(config('cache.prefix') . '-:link-');

        $this->assertEquals("The slug must not exceed {$maxLength} characters.", $errors['slug'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_slug_is_not_unique(): void
    {
        foreach ([1, 2] as $index) {
            $headers = [
                'X-Api-Key' => config('app.api_key'),
                'Accept' => 'application/json',
            ];

            $data = [
                'slug' => $this->validSlug,
                'target_url' => $this->validTargetUrl,
                'is_active' => random_int(0, 1),
            ];

            $response = $this->postJson('/api/links', $data, $headers);
        }

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('slug');

        $errors = $response->json('errors');

        $this->assertEquals('This slug is already registered.', $errors['slug'][0]);

        $this->assertEquals(1, Links::count());
    }

    public function test_create_a_link_when_is_active_is_missing(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('is_active');

        $errors = $response->json('errors');

        $this->assertEquals('The is active field is required.', $errors['is_active'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_is_active_is_null(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => null
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('is_active');

        $errors = $response->json('errors');

        $this->assertEquals('The is active field is required.', $errors['is_active'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_create_a_link_when_is_active_is_not_valid(): void
    {
        $headers = [
            'X-Api-Key' => config('app.api_key'),
            'Accept' => 'application/json',
        ];

        $data = [
            'slug' => $this->validSlug,
            'target_url' => $this->validTargetUrl,
            'is_active' => $this->inValidIsActive
        ];

        $response = $this->postJson('/api/links', $data, $headers);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Validation errors'
            ])
            ->assertJsonValidationErrors('is_active');

        $errors = $response->json('errors');

        $this->assertEquals('The Is active field must be either 0 or 1.', $errors['is_active'][0]);

        $this->assertEquals(0, Links::count());
    }

    public function test_link_throttle_equal_to_max_attempts(): void
    {
        $maxAttempts = (int) (env('API_KEY_MAX_ATTEMPTS') ?? 30);

        foreach (range(1, $maxAttempts) as $value) {
            $headers = [
                'X-Api-Key' => config('app.api_key'),
                'Accept' => 'application/json',
            ];

            $slug = $this->validSlug . "-{$value}";
            $is_active = random_int(0, 1);

            $data = [
                'slug' => $slug,
                'target_url' => $this->validTargetUrl,
                'is_active' => $is_active
            ];

            $response = $this->postJson('/api/links', $data, $headers);
        }

        $response->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Link created successfully',
                'slug' => $slug,
                'target_url' => $this->validTargetUrl,
            ]);

        $this->assertTrue(in_array($response->json('is_active'), [0, 1]), 'is_active is not 0 or 1');

        $this->assertDatabaseHas('links', [
            'slug' => $slug,
            'target_url' => $this->validTargetUrl,
        ]);

        $key = ":link-{$slug}";

        switch ($is_active) {
            case 0:
                $this->assertFalse(Cache::has($key));
                break;
            case 1:
                $this->assertTrue(Cache::has($key));
                break;
        }
    }

    public function test_link_throttle_above_max_attempts(): void
    {
        $maxAttempts = (int) (env('API_KEY_MAX_ATTEMPTS') ?? 30);

        foreach (range(1, $maxAttempts + 1) as $value) {
            $headers = [
                'X-Api-Key' => config('app.api_key'),
                'Accept' => 'application/json',
            ];

            $slug = $this->validSlug . "-{$value}";
            $is_active = random_int(0, 1);

            $data = [
                'slug' => $slug,
                'target_url' => $this->validTargetUrl,
                'is_active' => $is_active
            ];

            $response = $this->postJson('/api/links', $data, $headers);
        }

        $response->assertStatus(429)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
            ]);
    }
}
