@extends('layouts.app')

@section('title','Track Documents')

@section('content')
<style>
    .filter-panel {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid rgba(0, 215, 255, 0.2);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .filter-input {
        background: rgba(255, 255, 255, 0.08) !important;
        border: 1px solid rgba(0, 215, 255, 0.3) !important;
        color: white !important;
        border-radius: 8px;
        padding: 10px 12px;
    }
    .filter-input::placeholder {
        color: rgba(255, 255, 255, 0.5) !important;
    }
    .filter-input:focus {
        border-color: #00d7ff !important;
        box-shadow: 0 0 10px rgba(0, 215, 255, 0.2) !important;
        outline: none;
    }
    .btn-search {
        background: linear-gradient(90deg, #00d7ff, #00b8d4);
        border: none;
        color: #0b1228;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 24px;
        transition: 0.3s;
    }
    .btn-search:hover {
        background: linear-gradient(90deg, #00e5ff, #00d7ff);
        box-shadow: 0 0 20px rgba(0, 215, 255, 0.3);
        transform: translateY(-2px);
    }
    .btn-reset {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        font-weight: 500;
        border-radius: 8px;
        padding: 10px 20px;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-reset:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .progress-bar-container { width: 100px; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #00d7ff, #9C27B0); transition: width 0.5s ease; }
    .table { background: rgba(30, 41, 59, 0.4); border-radius: 12px; overflow: hidden; border: none; }
    .table thead { background: rgba(0, 215, 255, 0.1); color: #00d7ff; }
    .table tr { background: transparent !important; }
    .table td, .table th { border-color: rgba(255,255,255,0.05); vertical-align: middle; }

    /* --- FIX FOR BIG BUTTONS / SVGs --- */
    .pagination nav svg {
        width: 1.25rem; /* Forces the huge Laravel icons to shrink */
        height: 1.25rem;
    }
    .pagination .flex.justify-between.flex-1 {
        display: none !important; /* Hides the extra text blocks that break formatting */
    }
    /* ---------------------------------- */

    .pagination .page-link {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(0, 215, 255, 0.2);
        color: #00d7ff;
    }
    .pagination .page-link:hover {
        background: rgba(0, 215, 255, 0.1);
        border-color: #00d7ff;
    }
    .pagination .page-item.active .page-link {
        background: #00d7ff;
        border-color: #00d7ff;
        color: #0b1228;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold mb-2 text-cyan-400">Document Tracking</h1>
        <p class="text-gray-300">Track document location and routing history in real-time.</p>
    </div>
</div>

<div class="filter-panel">
    <form method="GET" action="{{ route('track.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label text-cyan-300 small fw-bold">Search (ID or Title)</label>
            <input type="text" name="search" class="form-control filter-input" placeholder="Document ID or Title..." value="{{ request('search') }}">
        </div>
        
        <div class="col-md-2">
            <label class="form-label text-cyan-300 small fw-bold">From Date</label>
            <input type="date" name="from_date" class="form-control filter-input" value="{{ request('from_date') }}">
        </div>
        
        <div class="col-md-2">
            <label class="form-label text-cyan-300 small fw-bold">To Date</label>
            <input type="date" name="to_date" class="form-control filter-input" value="{{ request('to_date') }}">
        </div>

        @if(session('user_role') === 'ADMIN')
        <div class="col-md-2">
            <label class="form-label text-cyan-300 small fw-bold">Status</label>
            <select name="status" class="form-control filter-input">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        @endif

        <div class="col-md-12 d-flex gap-2 pt-3">
            <button type="submit" class="btn btn-search">
                <i class="bi bi-search me-2"></i>Search
            </button>
            <a href="{{ route('track.index') }}" class="btn btn-reset">
                <i class="bi bi-arrow-clockwise me-2"></i>Reset
            </a>
        </div>
    </form>
</div>

<div class="table-responsive" style="margin-top: 20px;">
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>Document</th>
                <th>From</th>
                <th>To</th>
                <th>Current Location</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
            <tr>
                <td>
                    <strong>{{ $doc->title }}</strong><br>
                    <small style="color: #9bc4f9; font-size: 0.85rem;">ID: #{{ str_pad($doc->id, 6, '0', STR_PAD_LEFT) }}</small>
                </td>
                <td>{{ $doc->originOffice?->name ?? 'Unknown' }}</td>
                <td>{{ $doc->destinationOffice?->name ?? 'Unknown' }}</td>
                <td>{{ $doc->currentOffice?->name ?? 'Unknown' }}</td>
                <td>
                    @php
                        $status = strtolower($doc->status);
                        $badge = 'bg-warning text-dark';
                        if($status == 'completed') $badge = 'bg-success';
                        if(in_array($status, ['in_transit', 'uploaded', 'pending'])) $badge = 'bg-info text-white';
                    @endphp
                    <span class="badge {{ $badge }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
                </td>
                <td>
                    <div class="progress-bar-container">
                        @php
                            $progress = 25;
                            if($status == 'in_transit') $progress = 60;
                            if($status == 'completed') $progress = 100;
                        @endphp
                        <div class="progress-fill" style="width: {{ $progress }}%"></div>
                    </div>
                </td>
                <td>
                    <a href="{{ route('track.detail', $doc->id) }}" class="btn btn-sm btn-info text-white">
                        <i class="bi bi-geo-alt"></i> Track Status
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4 text-gray-500">No documents available to track.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($documents, 'links'))
    <div class="d-flex justify-content-center mt-4">
        {{-- Updated to use Bootstrap pagination layout --}}
        {{ $documents->links('pagination::bootstrap-4') }}
    </div>
@endif
@endsection