<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any roles that aren't ADMIN or USER to USER
        DB::table('users')->whereNotIn('role', ['ADMIN', 'USER'])->update(['role' => 'USER']);
        
        // Set default role for null values
        DB::table('users')->whereNull('role')->update(['role' => 'USER']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
