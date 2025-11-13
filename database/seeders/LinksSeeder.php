<?php

namespace Database\Seeders;

use App\Models\Links;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Links::factory(50)->create();
    }
}
