<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use App\Models\ActivityLog;
use App\Models\DocumentRouting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents (Main Documents Page).
     */
    public function index(Request $request)
    {
        $query = Document::with(['currentOffice', 'originOffice', 'destinationOffice', 'receiverUser.department', 'receiverUsers.department'])->latest();

        if (session('user_role') !== 'ADMIN') {
            $query->where('uploaded_by', session('user_id'));
        }

        $documents = $query->paginate(15);
        $offices = Office::orderBy('name', 'asc')->get();
        $users = User::with('department')->orderBy('name', 'asc')->get();

        return view('documents.index', compact('documents', 'offices', 'users'));
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|string',
            'sla' => 'required|in:Standard,Expedited,Critical',
            'origin_office_id' => 'required|exists:offices,id',
            'destination_office_id' => 'required|exists:offices,id',
            'receiver_user_ids' => 'required_without:receiver_user_id|array',
            'receiver_user_ids.*' => 'exists:users,id',
            'receiver_user_id' => 'required_without:receiver_user_ids|exists:users,id',
            'file' => 'required|file|max:5120' // 5MB Limit
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Handle File Upload
                $file = $request->file('file');
                $path = $file->store('documents', 'public');

                // 2. Derive due date from SLA selection
                $dueDate = null;
                if (Schema::hasColumn('documents', 'due_date')) {
                    $dueDate = match ($request->sla) {
                        'Critical' => Carbon::now()->addDay(),
                        'Expedited' => Carbon::now()->addDays(3),
                        default => Carbon::now()->addDays(7),
                    };
                }

                // 3. Generate unique QR ID
                $qrId = 'QR-' . strtoupper(uniqid('DOC-', true));

                // 4. Build document payload safely
                $receiverUserIds = collect($request->input('receiver_user_ids', []))->filter()->map(fn($id) => (int)$id)->unique()->values()->all();
                if ($request->filled('receiver_user_id')) {
                    array_unshift($receiverUserIds, (int) $request->receiver_user_id);
                    $receiverUserIds = array_values(array_unique($receiverUserIds));
                }

                $documentData = [
                    'title' => $request->title,
                    'description' => $request->description ?? 'No description provided',
                    'type' => strtoupper($file->getClientOriginalExtension()),
                    'priority' => $request->priority,
                    'origin_office_id' => $request->origin_office_id,
                    'current_office_id' => $request->origin_office_id,
                    'destination_office_id' => $request->destination_office_id,
                    'receiver_user_id' => $receiverUserIds[0] ?? null,
                    'file_path' => $path,
                    'status' => 'Pending',
                    'uploaded_by' => session('user_id') ?? 1,
                ];

                if (Schema::hasColumn('documents', 'sla')) {
                    $documentData['sla'] = $request->sla;
                }

                if (Schema::hasColumn('documents', 'due_date') && $dueDate) {
                    $documentData['due_date'] = $dueDate;
                }

                if (Schema::hasColumn('documents', 'qr_id')) {
                    $documentData['qr_id'] = $qrId;
                }

                $document = Document::create($documentData);

                // Generate QR Code for the document
                $qrCode = new QrCode(route('documents.show', $document->id), size: 300);
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $qrCodeData = $result->getString();
                $qrPath = 'qr_codes/' . $document->id . '.png';
                Storage::disk('public')->put($qrPath, $qrCodeData);
                $document->update(['qr_code' => $qrPath]);

                // Initialize routing history with origin office
                if (Schema::hasColumn('documents', 'routing_history')) {
                    $routingHistory = [
                        [
                            'office_id' => $request->origin_office_id,
                            'status' => 'Origin',
                            'timestamp' => now()->toIso8601String(),
                        ]
                    ];
                    $document->update(['routing_history' => $routingHistory]);
                }

                if (!empty($receiverUserIds)) {
                    foreach ($receiverUserIds as $receiverId) {
                        DocumentRouting::create([
                            'document_id' => $document->id,
                            'from_office_id' => $request->origin_office_id,
                            'to_office_id' => $request->destination_office_id,
                            'receiver_user_id' => $receiverId,
                            'status' => 'Pending',
                            'notes' => 'Initial upload receiver',
                        ]);
                    }
                }

                // 5. Log Activity for the Activity Tab
                ActivityLog::create([
                    'user' => session('user_name') ?? 'Admin User',
                    'action' => 'Document Created',
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'meta' => json_encode(['filename' => $file->getClientOriginalName()])
                ]);

                // 6. Send notifications to selected receivers
                try {
                    if (!empty($receiverUserIds)) {
                        foreach ($receiverUserIds as $receiverId) {
                            $receiver = User::find($receiverId);
                            if ($receiver) {
                                $receiver->notify(new \App\Notifications\DocumentRoutedNotification($document));
                            }
                        }
                    } else {
                        $document->notifyReceiver();
                    }
                } catch (\Exception $e) {
                    \Log::error('Notification failed: ' . $e->getMessage());
                }

                return redirect()->route('documents.index')->with('success', 'Document uploaded and initialized successfully!');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Display specific document tracking details.
     * Points to resources/views/documents/show.blade.php
     */
    public function show($id)
    {
        // Eager load activityLogs and receiver to show the routing history timeline
        $document = Document::with(['originOffice', 'currentOffice', 'destinationOffice', 'receiverUser.department', 'receiverUsers.department', 'activityLogs' => function($query) {
            $query->latest();
        }, 'routings.fromOffice', 'routings.toOffice'])->findOrFail($id);

        if (session('user_role') !== 'ADMIN' && $document->uploaded_by !== session('user_id') && $document->receiver_user_id !== session('user_id')) {
            abort(403, 'You are not authorized to view this document.');
        }

        return view('documents.show', compact('document'));
    }

    /**
     * Handle the Document Tracking page logic (Route: track.index).
     */
    public function trackIndex(Request $request)
    {
        $query = Document::with(['originOffice', 'currentOffice', 'destinationOffice']);

        if (session('user_role') !== 'ADMIN') {
            $query->where(function($q) {
                $q->where('uploaded_by', session('user_id'))
                  ->orWhere('receiver_user_id', session('user_id'));
            });
        }

        // Search logic for Tracking ID or Title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('title', 'like', "%$search%");
            });
        }

        // Date range filtering
        if ($request->filled('from_date')) {
            $fromDate = \Carbon\Carbon::parse($request->from_date)->startOfDay();
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = \Carbon\Carbon::parse($request->to_date)->endOfDay();
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest()->paginate(10);
        
        return view('track', compact('documents'));
    }

    /**
     * Handle the Activity Logs page logic (Route: activity.index).
     */
    public function activityIndex(Request $request)
    {
        $this->authorizeAdmin();

        $query = ActivityLog::with(['document.originOffice', 'document.destinationOffice']);

        // Search by document title or user
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('document', function($d) use ($search) {
                    $d->where('title', 'like', "%$search%");
                })->orWhere('user', 'like', "%$search%");
            });
        }

        // Date range filtering
        if ($request->filled('from_date')) {
            $fromDate = \Carbon\Carbon::parse($request->from_date)->startOfDay();
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = \Carbon\Carbon::parse($request->to_date)->endOfDay();
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Action filter
        if ($request->filled('action')) {
            $action = $request->action;
            $query->whereRaw('LOWER(action) LIKE ?', ["%$action%"]);
        }

        $logs = $query->latest()->paginate(15);
        
        return view('activity', compact('logs'));
    }

    protected function authorizeAdmin()
    {
        if (session('user_role') !== 'ADMIN') {
            abort(403, 'Administrator privileges are required to access this page.');
        }
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        if (session('user_role') !== 'ADMIN' && $document->uploaded_by !== session('user_id')) {
            abort(403, 'You are not authorized to delete this document.');
        }

        // Delete the physical file from storage
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully.');
    }

    /**
     * Check document status for real-time updates (API endpoint)
     */
    public function checkStatus($id)
    {
        $document = Document::with(['routings' => function($query) {
            $query->latest()->limit(1);
        }])->findOrFail($id);

        // Get the last routing update
        $lastRouting = $document->routings()->latest()->first();
        
        return response()->json([
            'status' => $document->status,
            'current_office' => $document->currentOffice?->name,
            'last_update' => $lastRouting?->updated_at ?? $document->updated_at,
            'received_at' => $document->received_at,
            'updated' => $document->updated_at->diffInMinutes(now()) < 1 // True if updated in last minute
        ]);
    }
}