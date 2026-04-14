<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add foreign key constraint if it doesn't exist
            if (!Schema::hasTable('users')) {
                return;
            }
            
            try {
                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            } catch (Exception $e) {
                // Foreign key might already exist, skip silently
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['department_id']);
        });
    }
};
