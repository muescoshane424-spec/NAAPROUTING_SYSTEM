@extends('layouts.app')

@section('title','Upload Document')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Upload Document</h1>

@if($errors->any())
<div class="alert alert-warning">
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="title">Document Title</label>
        <input type="text" name="title" id="title" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description"></textarea>
    </div>
    <div class="form-group">
        <label for="type">Type</label>
        <input type="text" name="type" id="type" placeholder="e.g., application, report">
    </div>
    <div class="form-group">
        <label for="priority">Priority</label>
        <select name="priority" id="priority" required>
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
    </div>
    <div class="form-group">
        <label for="origin_office_id">Origin Office</label>
        <select name="origin_office_id" id="origin_office_id" required>
            @foreach($offices as $office)
            <option value="{{ $office->id }}">{{ $office->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="destination_office_id">Destination Office</label>
        <select name="destination_office_id" id="destination_office_id" required>
            @foreach($offices as $office)
            <option value="{{ $office->id }}">{{ $office->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="document_file">Document File</label>
        <input type="file" name="document_file" id="document_file" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload & Route Document</button>
</form>

@endsection