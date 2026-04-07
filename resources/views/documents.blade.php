<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function create()
    {
        // Example: Offices dropdown
        $offices = ['Registrar', 'Accounting', 'Dean', 'HR', 'IT'];

        return view('documents.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'nullable|string|max:50',
            'priority' => 'required|string',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'file' => 'required|file|max:5120'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/documents', $filename);
            $sizeKB = $file->getSize() / 1024;
            $size = $sizeKB > 1024
                ? round($sizeKB / 1024, 2) . ' MB'
                : round($sizeKB, 2) . ' KB';
        }

        // Save document info to session (mock storage)
        $documents = session('documents', []);
        $documents[] = (object)[
            'filename' => $request->title,
            'type' => strtoupper($request->type ?? $file->getClientOriginalExtension()),
            'priority' => $request->priority,
            'status' => 'Created',
            'origin' => $request->origin,
            'destination' => $request->destination,
            'date' => now()->format('M d, Y h:i A'),
            'size' => $size
        ];
        session(['documents' => $documents]);

        return redirect()->route('documents.index')
            ->with('success', 'Document routed successfully!');
    }
}