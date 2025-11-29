<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'sandimvlyadi@gmail.com'],
            [
                'name' => 'Sandi Mulyadi',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            StakeholderSeeder::class,
            EmissionFactorSeeder::class,
            Scope1EmissionSeeder::class,
            Scope2EmissionSeeder::class,
            Scope3EmissionSeeder::class,
            EmissionSeeder2025::class, // Data 2025 hingga hari ini
        ]);
    }
}
