@extends('layouts.app')

@section('title','Documents')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Documents</h1>

<div class="mb-4">
    <a href="{{ route('documents.create') }}" class="btn btn-primary">Upload New Document</a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="w-full">
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Current Office</th>
            <th>Status</th>
            <th>Uploaded</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($documents as $doc)
        <tr>
            <td>{{ $doc->id }}</td>
            <td>{{ $doc->title }}</td>
            <td>{{ $doc->currentOffice->name ?? 'N/A' }}</td>
            <td><span class="badge {{ $doc->status == 'completed' ? 'success' : ($doc->status == 'in_transit' ? 'info' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $doc->status)) }}</span></td>
            <td>{{ $doc->created_at->diffForHumans() }}</td>
            <td>
                <a href="{{ route('routing.index') }}" class="text-cyan-400 hover:underline">Track</a>
                @if($doc->file_path)
                | <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-cyan-400 hover:underline">Download</a>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $documents->links() }}
@endsection