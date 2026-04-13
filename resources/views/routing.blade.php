@extends('layouts.app')

@section('title', 'Document Routing')

@section('content')
<style>
    /* Header Styling */
    .page-header { margin-bottom: 30px; }
    .page-header h2 { font-weight: 700; color: #fff; margin: 0; }
    .page-header p { color: #94a3b8; margin: 5px 0 0 0; }

    /* Routing Card Styling */
    .routing-card { 
        background: #1e293b; 
        border: 1px solid rgba(255, 255, 255, 0.05); 
        border-radius: 24px; 
        padding: 28px; 
        margin-bottom: 25px; 
        transition: 0.2s;
    }
    .routing-card:hover { border-color: rgba(34, 211, 238, 0.2); }

    /* The Tracker */
    .tracker-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 40px 0;
        position: relative;
        padding: 0 10px;
    }
    
    .tracker-line-bg {
        position: absolute;
        top: 15px; left: 0; right: 0;
        height: 4px;
        background: rgba(255, 255, 255, 0.05);
        z-index: 1;
        border-radius: 10px;
    }

    .tracker-line-fill {
        position: absolute;
        top: 15px; left: 0;
        height: 4px;
        background: linear-gradient(90deg, #a855f7, #22d3ee);
        z-index: 2;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(34, 211, 238, 0.3);
    }

    .step-node {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 120px;
    }

    .step-dot {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: #0f172a;
        border: 3px solid #334155;
        display: grid; place-items: center;
        font-size: 0.8rem; font-weight: 700;
        transition: 0.3s;
        color: #64748b;
    }

    .step-node.completed .step-dot {
        background: #a855f7;
        border-color: #a855f7;
        color: white;
    }

    .step-node.active .step-dot {
        background: #22d3ee;
        border-color: #22d3ee;
        color: #000;
        box-shadow: 0 0 20px rgba(34, 211, 238, 0.4);
    }

    .step-label {
        margin-top: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
    }
    .active .step-label, .completed .step-label { color: #fff; }

    /* Form Controls */
    .form-glass {
        background: rgba(15, 23, 42, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        color: white !important;
        padding: 12px 15px;
    }
    .form-glass:focus { border-color: #22d3ee; box-shadow: none; }

    .btn-route {
        background: linear-gradient(135deg, #22d3ee, #a855f7);
        border: none; color: white; font-weight: 700;
        padding: 12px 25px; border-radius: 12px;
        transition: 0.2s;
    }
    .btn-route:hover { opacity: 0.9; transform: scale(1.02); }
</style>

<div class="page-header">
    <h2>Active Routing</h2>
    <p>Monitor and update document progress across offices.</p>
</div>

@if(session('success'))
    <div class="alert alert-success bg-success text-white border-0 mb-4" style="border-radius: 15px;">
        {{ session('success') }}
    </div>
@endif

@foreach($documents as $doc)
<div class="routing-card">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <span class="badge mb-2" style="background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2);">
                {{ $doc->priority }} Priority
            </span>
            <h4 class="text-white mb-1">{{ $doc->title }}</h4>
            <div class="text-muted small">Tracking ID: <span class="text-info">#{{ str_pad($doc->id, 6, '0', STR_PAD_LEFT) }}</span></div>
        </div>
        <div class="text-end">
            <div class="small text-muted">Receiver</div>
            <div class="small fw-bold text-white">{{ optional($doc->receiverUser)->name ?? 'Unassigned' }}</div>
            <div class="small text-muted">{{ optional(optional($doc->receiverUser)->department)->name ?? 'No department' }}</div>
            <div class="small text-muted mt-2">Last Update</div>
            <div class="small fw-bold text-white">{{ $doc->updated_at->diffForHumans() }}</div>
        </div>
    </div>

    @php
        // Logic to determine tracker width
        $isFinished = ($doc->current_office_id == $doc->destination_office_id);
        $progressWidth = $isFinished ? '100%' : '50%';
    @endphp

    <div class="tracker-container">
        <div class="tracker-line-bg"></div>
        <div class="tracker-line-fill" style="width: {{ $progressWidth }};"></div> 
        
        <div class="step-node completed">
            <div class="step-dot">✓</div>
            <div class="step-label text-truncate" style="max-width: 100px;">{{ $doc->originOffice->name ?? 'Origin' }}</div>
        </div>

        <div class="step-node {{ $isFinished ? 'completed' : 'active' }}">
            <div class="step-dot">{{ $isFinished ? '✓' : '2' }}</div>
            <div class="step-label text-truncate" style="max-width: 100px;">{{ $doc->currentOffice->name ?? 'Current' }}</div>
        </div>

        <div class="step-node {{ $isFinished ? 'completed' : '' }}">
            <div class="step-dot">3</div>
            <div class="step-label text-truncate" style="max-width: 100px;">{{ $doc->destinationOffice->name ?? 'Destination' }}</div>
        </div>
    </div>
    <div class="row text-white mt-3">
        <div class="col-md-4">
            <small class="text-muted">Receiver</small>
            <div class="fw-bold">{{ optional($doc->receiverUser)->name ?? 'Unassigned' }}</div>
            <small class="text-muted">{{ optional(optional($doc->receiverUser)->department)->name ?? 'No department' }}</small>
        </div>
        <div class="col-md-4">
            <small class="text-muted">SLA</small>
            <div class="fw-bold">{{ $doc->sla ?? 'Standard' }}</div>
        </div>
        <div class="col-md-4">
            <small class="text-muted">Status</small>
            <div class="fw-bold">{{ $doc->status }}</div>
        </div>
    </div>

    <hr style="border-color: rgba(255,255,255,0.05); margin: 25px 0;">

    <form action="{{ route('routing.route', $doc->id) }}" method="POST" class="row g-3 align-items-center">
        @csrf
        <div class="col-md-8">
            <label class="small text-muted mb-2 d-block">Move to next department:</label>
            <select name="office_id" class="form-select form-glass w-100" required>
                <option value="" selected disabled>Select Target Office...</option>
                @foreach($offices as $office)
                    <option value="{{ $office->id }}" {{ $doc->current_office_id == $office->id ? 'disabled' : '' }}>
                        {{ $office->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label class="small text-muted mb-2 d-block">Assign or reassign receiver:</label>
            <select name="receiver_user_id" class="form-select form-glass w-100">
                <option value="" disabled {{ optional($doc->receiverUser)->id ? '' : 'selected' }}>Keep current receiver</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ optional($doc->receiverUser)->id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} @if($user->department) ({{ $user->department->name }}) @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mt-md-4">
            <button type="submit" class="btn-route w-100 mt-2">Update Location & Receiver</button>
        </div>
    </form>
</div>
@endforeach

<div class="d-flex justify-content-center mt-4">
    {{ $documents->links() }}
</div>
@endsection