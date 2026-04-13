<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Academic Affairs Department Offices
        Office::firstOrCreate(['name' => 'Registrar'], ['department' => 'Academic Affairs']);
        Office::firstOrCreate(['name' => 'Academic Dean'], ['department' => 'Academic Affairs']);

        // Administration Department Offices
        Office::firstOrCreate(['name' => 'Finance Office'], ['department' => 'Administration']);
        Office::firstOrCreate(['name' => 'HR Office'], ['department' => 'Administration']);
    }
}
