<?php

namespace Database\Factories;

use App\Models\Links;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LinkHits>
 */
class LinkHitsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' =>  Links::inRandomOrder()->first()->id,
            'ip' => $this->faker->unique()->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
