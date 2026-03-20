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
        Office::create(['name' => 'Registrar', 'department' => 'Academic Affairs', 'head' => 'Dr. Smith', 'contact' => 'registrar@naap.edu']);
        Office::create(['name' => 'Accounting', 'department' => 'Finance', 'head' => 'Ms. Johnson', 'contact' => 'accounting@naap.edu']);
        Office::create(['name' => 'HR', 'department' => 'Human Resources', 'head' => 'Mr. Brown', 'contact' => 'hr@naap.edu']);
        Office::create(['name' => 'Dean\'s Office', 'department' => 'Administration', 'head' => 'Dean Lee', 'contact' => 'dean@naap.edu']);
        Office::create(['name' => 'Library', 'department' => 'Academic Support', 'head' => 'Ms. Davis', 'contact' => 'library@naap.edu']);
    }
}
