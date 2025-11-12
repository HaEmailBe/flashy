<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class RedisTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_redis_connection(): void
    {
        $response = Cache::connection()->ping();
        $this->assertNotEmpty($response);
    }

    public function test_redis_set_and_get(): void
    {
        $key = 'test_' . time();
        $value = 'test_value';
        
        Cache::set($key, $value);
        $retrieved = Cache::get($key);
        
        $this->assertEquals($value, $retrieved);
        
        // Cleanup
        Cache::forget($key);
    }

    public function test_redis_cache_driver(): void
    {
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Cache driver is not Redis');
        }

        cache()->put('test_cache', 'value', 60);
        $this->assertEquals('value', cache('test_cache'));
        cache()->forget('test_cache');
    }
}
