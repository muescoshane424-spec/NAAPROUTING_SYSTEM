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
        Schema::table('documents', function (Blueprint $table) {
            // Add QR ID for tracking
            if (!Schema::hasColumn('documents', 'qr_id')) {
                $table->string('qr_id')->unique()->nullable()->after('qr_code');
            }

            // Add multiple destinations as JSON
            if (!Schema::hasColumn('documents', 'destination_offices')) {
                $table->json('destination_offices')->nullable()->after('destination_office_id')->comment('JSON array of destination office IDs and users');
            }

            // Add routing history
            if (!Schema::hasColumn('documents', 'routing_history')) {
                $table->json('routing_history')->nullable()->after('destination_offices')->comment('JSON array tracking the document path');
            }

            // Add 2FA columns to users if not present
        });

        // Update users table for 2FA
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('USER')->after('password');
            }
            if (!Schema::hasColumn('users', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'signature')) {
                $table->text('signature')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('signature');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'qr_id')) {
                $table->dropUnique(['qr_id']);
                $table->dropColumn('qr_id');
            }
            if (Schema::hasColumn('documents', 'destination_offices')) {
                $table->dropColumn('destination_offices');
            }
            if (Schema::hasColumn('documents', 'routing_history')) {
                $table->dropColumn('routing_history');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'role',
                'department_id',
                'username',
                'phone',
                'avatar',
                'signature',
                'status'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
