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
            if (!Schema::hasColumn('document_routings', 'document_id')) {
                $table->unsignedBigInteger('document_id')->after('id');
            }
            if (!Schema::hasColumn('document_routings', 'from_office_id')) {
                $table->unsignedBigInteger('from_office_id')->nullable()->after('document_id');
            }
            if (!Schema::hasColumn('document_routings', 'to_office_id')) {
                $table->unsignedBigInteger('to_office_id')->nullable()->after('from_office_id');
            }
            if (!Schema::hasColumn('document_routings', 'status')) {
                $table->string('status')->default('pending')->after('to_office_id');
            }
            if (!Schema::hasColumn('document_routings', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            if (!Schema::hasColumn('document_routings', 'document_id')) {
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            }
            if (!Schema::hasColumn('document_routings', 'from_office_id')) {
                $table->foreign('from_office_id')->references('id')->on('offices')->onDelete('set null');
            }
            if (!Schema::hasColumn('document_routings', 'to_office_id')) {
                $table->foreign('to_office_id')->references('id')->on('offices')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_routings', function (Blueprint $table) {
            if (Schema::hasColumn('document_routings', 'document_id')) {
                $table->dropForeign(['document_id']);
                $table->dropColumn('document_id');
            }
            if (Schema::hasColumn('document_routings', 'from_office_id')) {
                $table->dropForeign(['from_office_id']);
                $table->dropColumn('from_office_id');
            }
            if (Schema::hasColumn('document_routings', 'to_office_id')) {
                $table->dropForeign(['to_office_id']);
                $table->dropColumn('to_office_id');
            }
            if (Schema::hasColumn('document_routings', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('document_routings', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
