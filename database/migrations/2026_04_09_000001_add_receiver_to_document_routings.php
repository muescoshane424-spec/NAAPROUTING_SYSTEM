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
        Schema::table('document_routings', function (Blueprint $table) {
            if (!Schema::hasColumn('document_routings', 'receiver_user_id')) {
                $table->unsignedBigInteger('receiver_user_id')->nullable()->after('to_office_id');
                $table->foreign('receiver_user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_routings', function (Blueprint $table) {
            if (Schema::hasColumn('document_routings', 'receiver_user_id')) {
                $table->dropForeign(['receiver_user_id']);
                $table->dropColumn('receiver_user_id');
            }
        });
    }
};
