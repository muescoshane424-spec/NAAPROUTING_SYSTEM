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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user')->nullable(); // Stores name or role of the actor
            $table->string('action');           // e.g., 'Document Created', 'Forwarded'
            $table->unsignedBigInteger('document_id')->nullable();
            $table->string('ip')->nullable();   // Stores the user's IP address
            $table->json('meta')->nullable();   // Stores extra details as an array/JSON
            $table->timestamps();

            // Optional: Adds a constraint so logs are deleted if the document is deleted
            $table->foreign('document_id')
                  ->references('id')
                  ->on('documents')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};