<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

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
        $documents = Document::latest()->take(5)->get();

        return view('qr', compact('offices', 'documents'));
    }

    /**
     * Process a scanned QR code (Route: qr.scan)
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        // Logic to find document by its QR identifier/ID
        $document = Document::where('id', $request->qr_data)->first();

        if (!$document) {
            return redirect()->back()->with('error', 'Invalid QR Code. Document not found.');
        }

        // Return to view with the scanned document details
        return redirect()->back()->with([
            'success' => 'QR Code Scanned: ' . $document->title,
            'scanned_doc' => $document
        ]);
    }

    /**
     * Create a new document route via the QR interface (Route: qr.store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'origin_office_id' => 'required|exists:offices,id',
            'destination_office_id' => 'required|exists:offices,id',
            'priority' => 'required|string'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Create the Document Entry
                $document = Document::create([
                    'title' => $request->title,
                    'description' => $request->description ?? 'Generated via QR System',
                    'type' => $request->type ?? 'DOCUMENT',
                    'priority' => $request->priority,
                    'origin_office_id' => $request->origin_office_id,
                    'current_office_id' => $request->origin_office_id,
                    'destination_office_id' => $request->destination_office_id,
                    'status' => 'Pending',
                    'uploaded_by' => session('user_id') ?? 1, // Using session since your login uses session()->put()
                ]);

                // 2. Log the activity for the Activity tab
                ActivityLog::create([
                    'user' => session('user_name') ?? 'System Admin',
                    'action' => 'QR Route Created',
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'meta' => json_encode([
                        'method' => 'QR Interface',
                        'origin' => $request->origin_office_id,
                        'destination' => $request->destination_office_id
                    ])
                ]);
            });

            return redirect()->route('qr.index')->with('success', 'Document routed successfully via QR system!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}