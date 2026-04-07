<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents (Main Documents Page).
     */
    public function index(Request $request)
    {
        // Eager load currentOffice to prevent N+1 query issues
        $documents = Document::with(['currentOffice', 'originOffice', 'destinationOffice'])->latest()->get();
        $offices = Office::orderBy('name', 'asc')->get();

        return view('documents.index', compact('documents', 'offices'));
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'required|string',
            'origin_office_id' => 'required|exists:offices,id',
            'destination_office_id' => 'required|exists:offices,id',
            'file' => 'required|file|max:5120' // 5MB Limit
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Handle File Upload
                $file = $request->file('file');
                $path = $file->store('documents', 'public');

                // 2. Create Document record
                $document = Document::create([
                    'title' => $request->title,
                    'description' => $request->description ?? 'No description provided',
                    'type' => strtoupper($file->getClientOriginalExtension()),
                    'priority' => $request->priority,
                    'origin_office_id' => $request->origin_office_id,
                    'current_office_id' => $request->origin_office_id,
                    'destination_office_id' => $request->destination_office_id,
                    'file_path' => $path,
                    'status' => 'Pending',
                    'uploaded_by' => session('user_id') ?? 1,
                ]);

                // 3. Log Activity for the Activity Tab
                ActivityLog::create([
                    'user' => session('user_name') ?? 'Admin User',
                    'action' => 'Document Created',
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'meta' => json_encode(['filename' => $file->getClientOriginalName()])
                ]);

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
        // Eager load activityLogs to show the routing history timeline
        $document = Document::with(['originOffice', 'currentOffice', 'destinationOffice', 'activityLogs' => function($query) {
            $query->latest();
        }])->findOrFail($id);

        return view('documents.show', compact('document'));
    }

    /**
     * Handle the Document Tracking page logic (Route: track.index).
     */
    public function trackIndex(Request $request)
    {
        $query = Document::with(['originOffice', 'currentOffice', 'destinationOffice']);

        // Search logic for Tracking ID or Title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhere('title', 'like', "%$search%");
            });
        }

        $documents = $query->latest()->paginate(10);
        
        return view('track', compact('documents'));
    }

    /**
     * Handle the Activity Logs page logic (Route: activity.index).
     */
    public function activityIndex()
    {
        // Fetch logs and the documents they belong to
        $logs = ActivityLog::with('document')->latest()->paginate(15);
        
        return view('activity', compact('logs'));
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Delete the physical file from storage
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted successfully.');
    }
}