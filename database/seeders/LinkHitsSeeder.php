<?php

namespace Database\Seeders;

use App\Models\LinkHits;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LinkHitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LinkHits::factory(400)->create();
    }
}
