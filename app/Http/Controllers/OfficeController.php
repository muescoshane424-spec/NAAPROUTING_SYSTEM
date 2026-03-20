<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::orderBy('name')->paginate(12);
        return view('offices', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate(['name'=>'required|string|max:150','department'=>'nullable|string|max:150','head'=>'nullable|string|max:120','contact'=>'nullable|string|max:64','status'=>'required|in:active,inactive']);
        $office = Office::create($request->only('name','department','head','contact','status'));
        ActivityLog::create([ 'user'=>session('user_email','anon'), 'action'=>'Created office', 'document_id'=>null, 'ip'=>$request->ip(), 'meta'=>['office_id'=>$office->id] ]);
        return redirect()->route('offices.index')->with('success','Office created.');
    }
}
