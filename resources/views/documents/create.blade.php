@extends('layouts.app')

@section('title','Route Document')

@section('content')
<div class="main-header" style="justify-content:space-between;">
    <div>
        <h2 style="margin:0;">Route Document</h2>
        <p style="color:var(--text); opacity:0.8; margin:4px 0;">Create a document, generate QR code, and set up routing flow.</p>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        <a href="{{ route('documents.index') }}" class="btn btn-primary">Back to Documents</a>
    </div>
</div>

<form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="card" style="padding:20px; border-radius:18px;">
    @csrf

    <div class="grid2" style="gap:14px; margin-bottom:14px;">
        <div class="form-group" style="width:100%;">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required style="width:100%; padding:10px; border-radius:10px; border:1px solid var(--panel-border); background:rgba(15,23,42,0.65); color:var(--text);" />
        </div>
        <div class="form-group" style="width:100%;">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4" style="width:100%; padding:10px; border-radius:10px; border:1px solid var(--panel-border); background:rgba(15,23,42,0.65); color:var(--text);"></textarea>
        </div>
    </div>

    <div class="grid2" style="gap:14px; margin-bottom:14px;">
        <div class="form-group">
            <label for="priority">Priority</label>
            <select name="priority" id="priority" required style="width:100%; padding:10px; border-radius:10px; border:1px solid var(--panel-border); background:rgba(15,23,42,0.65); color:var(--text);">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </div>
        <div class="form-group">
            <label for="origin_office_id">Origin Office</label>
            <select name="origin_office_id" id="origin_office_id" required style="width:100%; padding:10px; border-radius:10px; border:1px solid var(--panel-border); background:rgba(15,23,42,0.65); color:var(--text);">
                @foreach($offices as $office)
                <option value="{{ $office->id }}">{{ $office->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="destination_office_id">Destination Office</label>
            <select name="destination_office_id" id="destination_office_id" required style="width:100%; padding:10px; border-radius:10px; border:1px solid var(--panel-border); background:rgba(15,23,42,0.65); color:var(--text);">
                @foreach($offices as $office)
                <option value="{{ $office->id }}">{{ $office->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group" style="margin-bottom:16px;">
        <label for="document_file">File Upload</label>
        <div style="border:2px dashed rgba(148,163,184,0.4); border-radius:14px; padding:20px; display:flex; justify-content:center; align-items:center; color:rgba(226,232,240,0.8);">
            Drag & Drop file here or click to upload
            <input type="file" name="document_file" id="document_file" required style="position:absolute; left:0; top:0; width:100%; height:100%; opacity:0; cursor:pointer;" />
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="margin-top:8px;">Generate QR & Route</button>
</form>

<div class="card" style="margin-top:18px; padding:18px; border-radius:16px;">
    <h3 style="margin:0 0 10px 0;">QR Preview</h3>
    <div id="qrPreview" style="width:120px; height:120px; background:linear-gradient(135deg, rgba(6,182,212,0.25), rgba(59,130,246,0.25)); display:flex; justify-content:center; align-items:center; border-radius:14px; color:#e0f2fe;">QRCODE</div>
</div>

<div class="card" style="margin-top:14px; padding:18px; border-radius:16px;">
    <h3 style="margin:0 0 10px 0;">Routing Flow Preview</h3>
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:space-between;">
        <span style="padding:8px 12px; border-radius:999px; background:rgba(56,189,248,0.2);">HR</span>
        <span>→</span>
        <span style="padding:8px 12px; border-radius:999px; background:rgba(59,130,246,0.2);">Admin</span>
        <span>→</span>
        <span style="padding:8px 12px; border-radius:999px; background:rgba(16,185,129,0.2);">Finance</span>
        <span>→</span>
        <span style="padding:8px 12px; border-radius:999px; background:rgba(37,99,235,0.2);">Completed</span>
    </div>
</div>
@endsection