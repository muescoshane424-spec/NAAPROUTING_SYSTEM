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
        // Create or update the seeded admin user
        $user = User::firstOrCreate([
            'email' => 'admin@naap.org',
        ], [
            'name' => 'Admin User',
            'username' => 'admin',
            'role' => 'ADMIN',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        if ($user->role !== 'ADMIN' || $user->username !== 'admin') {
            $user->fill([
                'role' => 'ADMIN',
                'username' => 'admin',
            ]);
            $user->save();
        }

        $this->call([
            DepartmentSeeder::class,
            OfficeSeeder::class,
            DocumentSeeder::class,
        ]);
    }
}
