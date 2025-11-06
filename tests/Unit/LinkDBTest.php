<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Links;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class LinkDBTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fetching link by id.
     */
    public function test_check_adding_one_link(): void
    {
        Links::factory(10)->create();
        $this->assertDatabaseCount('links', 10);
    }

    /**
     * Adding ten links via factory.
     */
    public function test_check_adding_multiple_links(): void
    {
        Links::factory(10)->create();
        $this->assertDatabaseCount('links', 10);
    }

    public function test_adding_link_when_slug_is_null()
    {
        try {
            Links::factory(1)->create(['slug' => null]);
            $this->fail('Expected QueryException was not thrown');
        } catch (QueryException $e) {
            $this->assertStringContainsString('slug', $e->getMessage());
            $this->assertStringContainsString('cannot be null', strtolower($e->getMessage()));
        }
    }

    public function test_adding_link_when_target_url_is_null()
    {
        try {
            Links::factory(1)->create(['target_url' => null]);
            $this->fail('Expected QueryException was not thrown');
        } catch (QueryException $e) {
            $this->assertStringContainsString('target_url', $e->getMessage());
            $this->assertStringContainsString('cannot be null', strtolower($e->getMessage()));
        }
    }
    public function test_adding_link_when_is_active_is_null()
    {
        try {
            Links::factory(1)->create(['is_active' => null]);
            $this->fail('Expected QueryException was not thrown');
        } catch (QueryException $e) {
            $this->assertStringContainsString('is_active', $e->getMessage());
            $this->assertStringContainsString('cannot be null', strtolower($e->getMessage()));
        }
    }

    public function test_adding_link_when_slug_is_not_unique()
    {
        try {
            Links::factory(2)->create(['slug' => 'slug']);
            $this->fail('Expected QueryException was not thrown');
        } catch (QueryException $e) {
            $this->assertStringContainsString('slug_unique', $e->getMessage());
            $this->assertStringContainsString('duplicate entry', strtolower($e->getMessage()));
        }
    }

    public function test_adding_link_slug_max_255_characters()
    {
        try {
            Links::factory(1)->create([
                'slug' => str_repeat('a', 256),
            ]);
            $this->fail('Expected QueryException was not thrown');
        } catch (QueryException $e) {
            $this->assertStringContainsString('slug', $e->getMessage());
            $this->assertStringContainsString('data too long for column', strtolower($e->getMessage()));
        }
    }

    public function test_adding_link_target_url_max_255_characters()
    {
        try {
            Links::factory(1)->create([
                'target_url' => str_repeat('a', 256),
            ]);
            $this->fail('Expected QueryException was not thrown');
        } catch (QueryException $e) {
            $this->assertStringContainsString('target_url', $e->getMessage());
            $this->assertStringContainsString('data too long for column', strtolower($e->getMessage()));
        }
    }
}
