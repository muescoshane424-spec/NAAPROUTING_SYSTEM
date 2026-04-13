<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::firstOrCreate([
            'name' => 'Academic Affairs',
        ], [
            'description' => 'Handles academic operations and registrar services',
            'status' => 'active',
        ]);

        Department::firstOrCreate([
            'name' => 'Administration',
        ], [
            'description' => 'Handles administrative, finance, and HR services',
            'status' => 'active',
        ]);
    }
}
