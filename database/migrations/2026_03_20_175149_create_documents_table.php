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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('origin_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('current_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('destination_office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->string('uploaded_by')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['in_transit', 'completed', 'pending'])->default('pending');
            $table->string('qr_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
