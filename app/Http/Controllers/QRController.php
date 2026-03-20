<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class QRController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }

        $documents = Document::with('currentOffice')->latest()->limit(20)->get();
        return view('qr', compact('documents'));
    }

    public function scan(Request $request)
    {
        $data = $request->validate(['qr' => 'required|string']);
        $document = Document::where('qr_code', $data['qr'])->first();

        $status = $document ? 'found' : 'not_found';
        if ($document) {
            $document->status = 'in_transit';
            $document->save();

            ActivityLog::create(['user' => session('user_email', 'anon'), 'action' => 'QR scanned', 'document_id' => $document->id, 'ip' => $request->ip(), 'meta' => ['qr' => $data['qr']]]);
        }

        return view('qr', compact('document', 'status'));
    }
}
