@extends('layouts.app')

@section('title', 'Document Details')

@section('content')
<div class="container-fluid">
    <div class="card bg-dark text-white border-secondary shadow">
        <div class="card-header border-secondary d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tracking Details for: {{ $document->title }}</h5>
            <span class="badge bg-info">{{ $document->status }}</span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <small class="text-muted d-block">Origin</small>
                    <strong>{{ $document->originOffice->name ?? 'N/A' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Current Location</small>
                    <strong>{{ $document->currentOffice->name ?? 'In Transit' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Destination</small>
                    <strong>{{ $document->destinationOffice->name ?? 'N/A' }}</strong>
                </div>
            </div>

            <h6 class="fw-bold mb-3">Activity Timeline</h6>
            <div class="list-group list-group-flush">
                @foreach($document->activityLogs as $log)
                    <div class="list-group-item bg-transparent text-white border-secondary px-0">
                        <div class="d-flex justify-content-between">
                            <span class="text-info">{{ $log->action }}</span>
                            <small class="text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                        <small class="d-block text-white-50">Processed by: {{ $log->user }}</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection