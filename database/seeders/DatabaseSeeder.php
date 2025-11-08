<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Links;
use App\Models\LinkHits;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->truncate();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Schema::disableForeignKeyConstraints();
        Links::truncate();
        LinkHits::truncate();
        Schema::enableForeignKeyConstraints();

        Links::factory(50)->create();
        LinkHits::factory(400)->create();
    }
}
