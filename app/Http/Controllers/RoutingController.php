<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentRouting;
use App\Models\ActivityLog;
use App\Models\Office;
use Illuminate\Http\Request;

class RoutingController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }

        $documents = Document::with(['originOffice', 'currentOffice', 'destinationOffice'])->latest()->paginate(10);
        $offices = Office::all();
        return view('routing', compact('documents', 'offices'));
    }

    public function routeDocument(Request $request, Document $document)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }

        $this->validate($request, [
            'next_office_id' => 'required|exists:offices,id',
            'notes' => 'nullable|string',
        ]);

        $document->update(['current_office_id' => $request->next_office_id, 'status' => 'in_transit']);

        DocumentRouting::create([
            'document_id' => $document->id,
            'from_office_id' => $document->current_office_id,
            'to_office_id' => $request->next_office_id,
            'status' => 'transferred',
            'notes' => $request->notes,
        ]);

        ActivityLog::create(['user' => session('user_email', 'anon'), 'action' => 'Routed document', 'document_id' => $document->id, 'ip' => $request->ip(), 'meta' => ['to_office' => $request->next_office_id]]);

        return back()->with('success', 'Document was routed successfully.');
    }
}
