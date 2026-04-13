<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentRouting;
use App\Models\Office;
use App\Models\ActivityLog;
use App\Models\User;
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
        $documents = Document::with(['originOffice', 'currentOffice', 'destinationOffice', 'receiverUser.department'])
            ->latest()
            ->paginate(10);

        return view('routing', compact('documents', 'offices', 'users'));
    }

    /**
     * Update document location and log the movement.
     */
    public function routeDocument(Request $request, $id)
    {
        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'receiver_user_ids' => 'nullable|array',
            'receiver_user_ids.*' => 'exists:users,id'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                // Find document or fail with 404
                $document = Document::findOrFail($id);
                $oldOfficeName = $document->currentOffice->name ?? 'Unknown Office';
                $oldReceiverName = optional($document->receiverUser)->name ?? 'Unassigned';
                $fromOfficeId = $document->current_office_id ?? $document->origin_office_id; // GET BEFORE UPDATE
                
                // Find the target office name for the log
                $targetOffice = Office::findOrFail($request->office_id);
                $newStatus = ($request->office_id == $document->destination_office_id) 
                    ? 'Completed' 
                    : 'In Transit';

                $updateData = [
                    'current_office_id' => $request->office_id,
                    'status' => $newStatus
                ];

                // Set first receiver as primary if multiple provided
                $receiverUserIds = $request->input('receiver_user_ids', []);
                if (!empty($receiverUserIds)) {
                    $updateData['receiver_user_id'] = $receiverUserIds[0];
                }

                // 1. Update the Document record
                $document->update($updateData);

                // 2. Create DocumentRouting records - one per receiver (or one general if no receivers)
                if (!empty($receiverUserIds)) {
                    foreach ($receiverUserIds as $receiverId) {
                        DocumentRouting::create([
                            'document_id' => $document->id,
                            'from_office_id' => $fromOfficeId,
                            'to_office_id' => $request->office_id,
                            'receiver_user_id' => $receiverId,
                            'status' => $newStatus,
                            'notes' => $request->notes ?? null,
                        ]);
                    }
                } else {
                    // No receivers selected, create single routing record
                    DocumentRouting::create([
                        'document_id' => $document->id,
                        'from_office_id' => $fromOfficeId,
                        'to_office_id' => $request->office_id,
                        'status' => $newStatus,
                        'notes' => $request->notes ?? null,
                    ]);
                }

                // 3. Log the activity with receiver info
                $receiverNames = User::whereIn('id', $receiverUserIds)->pluck('name')->toArray();
                $meta = [
                    'from_office' => $oldOfficeName,
                    'to_office' => $targetOffice->name,
                    'status' => $newStatus,
                    'timestamp' => now()->toIso8601String(),
                    'receivers_count' => count($receiverUserIds),
                ];

                if (!empty($receiverNames)) {
                    $meta['receivers'] = implode(', ', $receiverNames);
                }

                ActivityLog::create([
                    'user' => session('user_name') ?? 'System Admin',
                    'action' => 'Document Routed',
                    'document_id' => $document->id,
                    'ip' => $request->ip(),
                    'meta' => json_encode($meta)
                ]);

                // 4. Send notification to receiver
                if ($request->filled('receiver_user_id')) {
                    try {
                        $document->notifyReceiver();
                    } catch (\Exception $e) {
                        \Log::error('Notification failed: ' . $e->getMessage());
                    }
                } else if ($document->receiver_user_id) {
                    // Notify existing receiver if no new receiver specified
                    try {
                        $document->notifyReceiver();
                    } catch (\Exception $e) {
                        \Log::error('Notification failed: ' . $e->getMessage());
                    }
                }
            });

            return redirect()->route('track.index')->with('success', 'Document routed successfully! Check tracking to see the update.');
        } catch (\Exception $e) {
            // Log the error for the developer and show a user-friendly message
            \Log::error("Routing Error: " . $e->getMessage());
            return back()->with('error', 'Routing failed: ' . $e->getMessage());
        }
    }
}