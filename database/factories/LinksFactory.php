<?php

namespace Database\Factories;

use App\Models\Links;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\links>
 */
class LinksFactory extends Factory
{
    protected $model = Links::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('2024-01-01', '2025-01-01');

        return [
            'slug' => Str::slug($this->faker->unique()->regexify('[a-zA-Z ]{10,200}'), true),
            'target_url' => $this->faker->url(),
            'is_active' => $this->faker->boolean(),
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, '2025-12-31'),
        ];
    }
}
