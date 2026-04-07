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
            $table->string('type')->nullable(); // e.g., 'Letter', 'Invoice'
            $table->string('priority')->default('Normal'); // 'Low', 'Normal', 'High'
            
            // Foreign keys to the offices table
            $table->unsignedBigInteger('origin_office_id')->nullable();
            $table->unsignedBigInteger('current_office_id')->nullable();
            $table->unsignedBigInteger('destination_office_id')->nullable();
            
            // File and Tracking info
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('file_path')->nullable();
            $table->string('qr_code')->nullable();
            
            $table->string('status')->default('Pending Approval');
            $table->timestamps();

            // Optional: Foreign key constraints (uncomment if offices table exists)
            // $table->foreign('origin_office_id')->references('id')->on('offices')->onDelete('set null');
            // $table->foreign('current_office_id')->references('id')->on('offices')->onDelete('set null');
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