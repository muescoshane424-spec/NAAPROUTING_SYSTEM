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
            // Add signature field for proof of delivery
            if (!Schema::hasColumn('documents', 'receiver_signature')) {
                $table->longText('receiver_signature')->nullable()->after('status')->comment('Base64 encoded signature image');
            }
            
            // Track when QR was scanned with signature
            if (!Schema::hasColumn('documents', 'qr_scanned_at')) {
                $table->timestamp('qr_scanned_at')->nullable()->after('receiver_signature')->comment('When QR was scanned as proof of delivery');
            }
            
            // Track when document was actually received
            if (!Schema::hasColumn('documents', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('qr_scanned_at')->comment('When document was received');
            }

            // Add notes field for routing
            if (!Schema::hasColumn('documents', 'routing_notes')) {
                $table->text('routing_notes')->nullable()->after('received_at');
            }
        });

        Schema::table('document_routings', function (Blueprint $table) {
            // Add notes and timestamps to track routing history
            if (!Schema::hasColumn('document_routings', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('document_routings', 'signed_by')) {
                $table->string('signed_by')->nullable()->after('notes')->comment('User who signed for received document');
            }
            
            if (!Schema::hasColumn('document_routings', 'signature')) {
                $table->longText('signature')->nullable()->after('signed_by')->comment('Signature image for this routing');
            }

            if (!Schema::hasColumn('document_routings', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('signature');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['receiver_signature', 'qr_scanned_at', 'received_at', 'routing_notes']);
        });

        Schema::table('document_routings', function (Blueprint $table) {
            $table->dropColumn(['notes', 'signed_by', 'signature', 'received_at']);
        });
    }
};
