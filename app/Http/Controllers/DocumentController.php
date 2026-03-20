<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }
        $documents = Document::with(['originOffice', 'currentOffice', 'destinationOffice'])->latest()->paginate(10);
        return view('documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }
        $offices = Office::orderBy('name')->get();
        return view('documents.create', compact('offices'));
    }

    public function store(Request $request)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:100',
            'priority' => 'required|in:low,medium,high',
            'origin_office_id' => 'required|exists:offices,id',
            'destination_office_id' => 'required|exists:offices,id',
            'document_file' => 'required|file|max:10240',
        ]);

        $documentFile = $request->file('document_file');
        $path = $documentFile->store('documents', 'public');

        $doc = Document::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'general',
            'priority' => $data['priority'],
            'origin_office_id' => $data['origin_office_id'],
            'current_office_id' => $data['origin_office_id'],
            'destination_office_id' => $data['destination_office_id'],
            'uploaded_by' => session('user_email', 'anon'),
            'file_path' => $path,
            'status' => 'in_transit',
            'qr_code' => base64_encode('NAAP-'.time().'-'.uniqid()),
        ]);

        ActivityLog::create([ 'user' => session('user_email', 'anon'), 'action' => 'Document created', 'document_id' => $doc->id, 'ip' => $request->ip(), 'meta' => ['title' => $doc->title] ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded and routed successfully.');
    }
}
