<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Office;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class RoutingController extends Controller
{
    /**
     * Display the active routing dashboard with pagination.
     */
    public function index()
    {
        // 1. Fetch real offices for the dropdown
        $offices = Office::orderBy('name', 'asc')->get();

        // 2. Fetch documents with relationships to avoid N+1 query issues
        $documents = Document::with(['originOffice', 'currentOffice', 'destinationOffice'])
            ->latest()
            ->paginate(10);

        return view('routing', compact('documents', 'offices'));
    }

    /**
     * Update document location and log the movement.
     */
    public function routeDocument(Request $request, $id)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                // Find document or fail with 404
                $document = Document::findOrFail($id);
                $oldOfficeName = $document->currentOffice->name ?? 'Unknown Office';
                
                // Find the target office name for the log
                $targetOffice = Office::findOrFail($request->office_id);

                // Determine status: If target is the destination, set to 'Received/Completed'
                $newStatus = ($request->office_id == $document->destination_office_id) 
                    ? 'Completed' 
                    : 'In Transit';

                // 1. Update the Document record
                $document->update([
                    'current_office_id' => $request->office_id,
                    'status' => $newStatus
                ]);

                // 2. Log the activity
                // Note: Ensure your ActivityLog model has 'meta' in $casts as 'array'
                ActivityLog::create([
                    'user' => session('user_name') ?? 'System Admin',
                    'action' => 'Document Routed',
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'meta' => [
                        'from_office' => $oldOfficeName,
                        'to_office' => $targetOffice->name,
                        'status' => $newStatus
                    ]
                ]);
            });

            return back()->with('success', 'Document location updated successfully!');
        } catch (\Exception $e) {
            // Log the error for the developer and show a user-friendly message
            \Log::error("Routing Error: " . $e->getMessage());
            return back()->with('error', 'Routing failed: Something went wrong while updating the location.');
        }
    }
}