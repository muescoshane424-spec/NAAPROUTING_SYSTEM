<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use App\Models\ActivityLog;
use App\Models\DocumentRouting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRController extends Controller
{
    /**
     * Display the QR Scanner and routing interface.
     */
    public function index()
    {
        // Fetch real offices for the dropdowns
        $offices = Office::orderBy('name', 'asc')->get();
        
        // Fetch recent documents to show status in the scanner UI
        $documents = Document::with('receiverUser', 'currentOffice', 'destinationOffice')
            ->latest()
            ->take(10)
            ->get();

        return view('qr', compact('offices', 'documents'));
    }

    /**
     * Process a scanned QR code with optional signature for delivery proof
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'signature' => 'nullable|string', // Base64 encoded signature
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Extract document ID from QR data (it's the route to the document)
                // The QR code likely contains something like: https://example.com/documents/123
                $qrData = $request->qr_data;
                
                // Try to extract ID from URL
                $documentId = $this->extractDocumentIdFromQR($qrData);
                
                $document = Document::with(['receiverUser', 'currentOffice', 'destinationOffice'])->find($documentId);

                if (!$document) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid QR Code. Document not found.',
                        'error' => true
                    ], 404);
                }

                // Mark document as scanned
                $document->update([
                    'qr_scanned_at' => now(),
                ]);

                // If signature is provided, mark as received with proof
                if ($request->filled('signature')) {
                    $document->update([
                        'receiver_signature' => $request->signature,
                        'received_at' => now(),
                        'status' => 'Completed',
                    ]);

                    // Log signature activity
                    ActivityLog::create([
                        'user' => session('user_name') ?? 'System User',
                        'action' => 'QR Scanned - Signed',
                        'document_id' => $document->id,
                        'ip' => $request->ip(),
                        'meta' => json_encode([
                            'receiver' => $document->receiverUser->name ?? 'Unknown',
                            'proof_of_delivery' => true,
                            'timestamp' => now()->toIso8601String(),
                        ])
                    ]);

                    // Send notification to uploader
                    try {
                        $document->notifyUploader($document->receiverUser->name ?? 'Unknown');
                    } catch (\Exception $e) {
                        \Log::error('Notification failed: ' . $e->getMessage());
                    }
                } else {
                    // Log QR scan activity
                    ActivityLog::create([
                        'user' => session('user_name') ?? 'System User',
                        'action' => 'QR Scanned',
                        'document_id' => $document->id,
                        'ip' => $request->ip(),
                        'meta' => json_encode([
                            'receiver' => $document->receiverUser->name ?? 'Unknown',
                            'timestamp' => now()->toIso8601String(),
                        ])
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $request->filled('signature') 
                        ? 'Document received and signed successfully!' 
                        : 'QR Code scanned successfully!',
                    'document' => [
                        'id' => $document->id,
                        'title' => $document->title,
                        'status' => $document->status,
                        'receiver' => $document->receiverUser?->name,
                        'current_office' => $document->currentOffice?->name,
                        'has_signature' => $document->receiver_signature !== null,
                        'scanned_at' => $document->qr_scanned_at?->format('M j, Y H:i'),
                    ]
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('QR Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing QR code: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }

    /**
     * Store a new document with QR generation
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'origin_office_id' => 'required|exists:offices,id',
            'destination_office_id' => 'required|exists:offices,id',
            'priority' => 'required|string',
            'sla' => 'required|in:Standard,Expedited,Critical',
            'receiver_user_id' => 'nullable|exists:users,id',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Create the Document Entry
                $document = Document::create([
                    'title' => $request->title,
                    'description' => $request->description ?? 'Created via QR Interface',
                    'type' => 'DOCUMENT',
                    'priority' => $request->priority,
                    'sla' => $request->sla,
                    'origin_office_id' => $request->origin_office_id,
                    'current_office_id' => $request->origin_office_id,
                    'destination_office_id' => $request->destination_office_id,
                    'receiver_user_id' => $request->receiver_user_id,
                    'status' => 'In Transit',
                    'uploaded_by' => session('user_id') ?? 1,
                    'due_date' => $this->calculateDueDate($request->sla),
                ]);

                // 2. Generate QR Code
                $qrCode = new QrCode(route('documents.show', $document->id), size: 300);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $qrCodeData = $result->getString();
                $qrPath = 'qr_codes/' . $document->id . '.png';
                \Illuminate\Support\Facades\Storage::disk('public')->put($qrPath, $qrCodeData);
                $document->update(['qr_code' => $qrPath]);

                // 3. Log the activity
                ActivityLog::create([
                    'user' => session('user_name') ?? 'System Admin',
                    'action' => 'Document Created - QR Generated',
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'meta' => json_encode([
                        'method' => 'QR Interface',
                        'origin' => Office::find($request->origin_office_id)->name,
                        'destination' => Office::find($request->destination_office_id)->name,
                        'qr_generated' => true,
                    ])
                ]);

                // 4. Notify receiver
                if ($request->filled('receiver_user_id')) {
                    $this->notifyReceiver($document);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Document created with QR code!',
                    'document' => [
                        'id' => $document->id,
                        'title' => $document->title,
                        'qr_code' => asset('storage/' . $document->qr_code),
                    ]
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('QR Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'error' => true
            ], 500);
        }
    }

    /**
     * Extract document ID from QR data
     */
    private function extractDocumentIdFromQR($qrData)
    {
        // The QR code contains a URL route, extract the ID
        // Example: https://example.com/documents/123
        if (preg_match('/documents[\/|%2F]+(\d+)/', $qrData, $matches)) {
            return $matches[1];
        }
        
        // If it's just a number
        if (is_numeric($qrData)) {
            return $qrData;
        }

        return null;
    }

    /**
     * Calculate due date based on SLA
     */
    private function calculateDueDate($sla)
    {
        return match ($sla) {
            'Critical' => now()->addDay(),
            'Expedited' => now()->addDays(3),
            default => now()->addDays(7),
        };
    }

    /**
     * Notify the receiver about the new document
     */
    private function notifyReceiver(Document $document)
    {
        if ($document->receiver_user_id) {
            try {
                $receiver = User::find($document->receiver_user_id);
                // TODO: Implement your notification system (email, SMS, database notification, etc.)
                \Log::info("Notification: Document '{$document->title}' sent to {$receiver->name}");
            } catch (\Exception $e) {
                \Log::error('Notification failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify the uploader when document is received
     */
    private function notifyUploader(Document $document, $message)
    {
        try {
            $uploader = User::find($document->uploaded_by);
            // TODO: Implement your notification system
            \Log::info("Notification to uploader: {$message} - Document: {$document->title}");
        } catch (\Exception $e) {
            \Log::error('Uploader notification failed: ' . $e->getMessage());
        }
    }
}