@extends('layouts.app')

@section('title', 'Document Details')

@section('head')
<style>
    .tracking-map {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px;
        background: linear-gradient(90deg, rgba(34, 211, 238, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
        border-radius: 12px;
        margin: 20px 0;
    }
    .tracking-office {
        text-align: center;
        flex: 1;
        opacity: 0.65;
        transition: opacity 0.2s ease;
    }
    .tracking-office.active {
        opacity: 1;
    }
    .tracking-office-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: bold;
        color: #ffffff;
        background: rgba(148, 163, 184, 0.16);
        border: 2px solid rgba(148, 163, 184, 0.25);
        transition: all 0.2s ease;
    }
    .tracking-office.active .tracking-office-circle {
        background: #22d3ee;
        border-color: #22d3ee;
        box-shadow: 0 0 18px rgba(34, 211, 238, 0.35);
    }
    .tracking-office-name {
        font-size: 0.9rem;
        margin-bottom: 5px;
        color: #cbd5e1;
        transition: color 0.2s ease;
    }
    .tracking-office.active .tracking-office-name {
        color: #ffffff;
    }
    .tracking-office-type {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .tracking-arrow {
        flex: 0.5;
        text-align: center;
        color: rgba(34, 211, 238, 0.7);
        font-size: 24px;
    }
    .sla-card {
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.1) 0%, rgba(168, 85, 247, 0.05) 100%);
        border: 1px solid rgba(34, 211, 238, 0.3);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .sla-field {
        margin-bottom: 10px;
    }
    .sla-field-label {
        font-size: 0.8rem;
        color: #94a3b8;
        text-transform: uppercase;
        display: block;
    }
    .sla-field-value {
        color: white;
        font-weight: 600;
        margin-top: 3px;
    }
</style>
@endsection

@section('content')
@php
    $originId = optional($document->originOffice)->id;
    $destinationId = optional($document->destinationOffice)->id;
    $currentId = optional($document->currentOffice)->id;
    $statusKey = strtolower(str_replace(' ', '_', $document->status ?? ''));
    $originActive = $currentId && $currentId === $originId && $statusKey !== 'in_transit';
    $destinationActive = $currentId && $currentId === $destinationId && $statusKey !== 'in_transit';
    $currentActive = !$originActive && !$destinationActive;
    $currentLabel = $document->currentOffice?->name ?? 'In Transit';
@endphp
<div class="container-fluid p-4">
    <div class="card bg-dark text-white border-secondary shadow" style="border-radius: 16px;">
        <div class="card-header border-secondary d-flex justify-content-between align-items-center" style="border-radius: 16px 16px 0 0;">
            <h5 class="mb-0">📦 {{ $document->title }}</h5>
            <div>
                <span class="badge me-2" style="background: #22d3ee; color: #0f172a;">{{ $document->status }}</span>
                <span class="badge" style="background: #{{ $document->priority_color === 'danger' ? 'ef4444' : ($document->priority_color === 'warning' ? 'f59e0b' : '10b981') }};">{{ $document->priority }}</span>
            </div>
        </div>
        <div class="card-body">
            <!-- SLA Information Card -->
            <div class="sla-card">
                <div class="row">
                    <div class="col-md-3">
                        <div class="sla-field">
                            <span class="sla-field-label">SLA Type</span>
                            <span class="sla-field-value">{{ $document->sla ?? 'Standard' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sla-field">
                            <span class="sla-field-label">Due Date</span>
                            <span class="sla-field-value">{{ $document->due_date ? $document->due_date->format('M d, Y h:i A') : 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sla-field">
                            <span class="sla-field-label">SLA Status</span>
                            <span class="badge {{ $document->sla_status_class }}">{{ $document->sla_status_label }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sla-field">
                            <span class="sla-field-label">Time Remaining</span>
                            <span class="sla-field-value" id="time-remaining">
                                @if($document->due_date)
                                    {{ $document->due_date->diffForHumans() }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking Map -->
            <h6 class="fw-bold mb-3">📍 Document Route</h6>
            <div class="tracking-map">
                <!-- Origin Office -->
                <div class="tracking-office {{ $originActive ? 'active' : '' }}">
                    <div class="tracking-office-circle">
                        📤
                    </div>
                    <div class="tracking-office-name">{{ $document->originOffice->name ?? 'N/A' }}</div>
                    <div class="tracking-office-type">Origin</div>
                </div>

                <!-- Arrow -->
                <div class="tracking-arrow">→</div>

                <!-- Current Office -->
                <div class="tracking-office {{ $currentActive ? 'active' : '' }}">
                    <div class="tracking-office-circle">
                        📍
                    </div>
                    <div class="tracking-office-name">{{ $currentLabel }}</div>
                    <div class="tracking-office-type">Current</div>
                </div>

                <!-- Arrow -->
                <div class="tracking-arrow">→</div>

                <!-- Destination Office -->
                <div class="tracking-office {{ $destinationActive ? 'active' : '' }}">
                    <div class="tracking-office-circle">
                        📥
                    </div>
                    <div class="tracking-office-name">{{ $document->destinationOffice->name ?? 'N/A' }}</div>
                    <div class="tracking-office-type">Destination</div>
                </div>
            </div>

            <!-- Basic Information -->
            <hr class="border-secondary">
            <div class="row mb-4">
                <div class="col-md-3">
                    <small class="text-muted d-block">📌 Receiver</small>
                    @if($document->receiverUsers && $document->receiverUsers->isNotEmpty())
                        <div class="d-flex flex-column gap-1">
                            @foreach($document->receiverUsers as $receiver)
                                <div>
                                    <strong>{{ $receiver->name }}</strong>
                                    <div class="small text-muted">{{ optional($receiver->department)->name ?? 'No department' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <strong>{{ optional($document->receiverUser)->name ?? 'Unassigned' }}</strong>
                        <div class="small text-muted">{{ optional(optional($document->receiverUser)->department)->name ?? 'No department' }}</div>
                    @endif
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">📄 Document Type</small>
                    <strong>{{ $document->type }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">👤 Uploaded By</small>
                    <strong>{{ optional($document->uploader)->name ?? 'System' }}</strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">📅 Created</small>
                    <strong>{{ $document->created_at->format('M d, Y') }}</strong>
                </div>
            </div>

            @if($document->qr_code)
            <div class="mb-4 text-center">
                <small class="text-muted d-block mb-2">Dynamic QR Code (Scan to View)</small>
                <img src="{{ asset('storage/' . $document->qr_code) }}" alt="QR Code" class="img-fluid" style="max-width: 200px; border-radius: 8px;">
            </div>
            @endif

            <!-- Activity Timeline -->
            <h6 class="fw-bold mb-3">📋 Activity Timeline</h6>
            <div class="list-group list-group-flush">
                @forelse($document->activityLogs as $log)
                    <div class="list-group-item bg-transparent text-white border-secondary px-0 py-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-info fw-bold">{{ $log->action }}</span>
                            <small class="text-muted">{{ $log->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                        <small class="d-block text-white-50">👤 {{ $log->user }}</small>
                    </div>
                @empty
                    <div class="text-muted text-center py-3">
                        No activity recorded yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection