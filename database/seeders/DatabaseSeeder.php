<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;
use Database\Seeders\LinksSeeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\LinkHitsSeeder;
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
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        DB::table('links')->truncate();
        DB::table('link_hits')->truncate();
        Schema::enableForeignKeyConstraints();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            LinksSeeder::class,
            LinkHitsSeeder::class,
        ]);
    }
}
