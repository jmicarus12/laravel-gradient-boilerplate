<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Demo user (verified). Factories skip the #[Fillable] guard, so email_verified_at is kept.
        if (User::where('email', 'test@example.com')->doesntExist()) {
            User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        }

        $this->call([AdminSeeder::class, PermissionSeeder::class]);
    }
}
