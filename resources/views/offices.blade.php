@extends('layouts.app')

@section('title', 'Departments & Offices')

@section('content')
<style>
    /* --- KEEPING YOUR EXACT STYLES --- */
    .tab-container {
        background: rgba(15, 23, 42, 0.4);
        padding: 5px;
        border-radius: 14px;
        display: inline-flex;
        border: 1px solid var(--panel-border);
        margin-bottom: 30px;
    }

    .tab-btn {
        padding: 10px 24px;
        border-radius: 12px;
        border: none;
        background: transparent;
        color: var(--text-dim);
        font-weight: 600;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .tab-btn i { font-size: 1.1rem; }

    .tab-btn.active {
        background: var(--accent-cyan);
        color: #0b1228;
        box-shadow: 0 0 20px rgba(34, 211, 238, 0.3);
    }

    .mgmt-card {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 20px;
        padding: 24px;
        height: 100%;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        position: relative;
    }

    .mgmt-card:hover {
        transform: translateY(-5px);
        border-color: rgba(34, 211, 238, 0.4);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .icon-sq {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; margin-bottom: 16px;
    }

    .icon-dept { background: rgba(168, 85, 247, 0.1); color: var(--accent-purple); border: 1px solid rgba(168, 85, 247, 0.2); }
    .icon-offi { background: rgba(34, 211, 238, 0.1); color: var(--accent-cyan); border: 1px solid rgba(34, 211, 238, 0.2); }

    .card-title { font-weight: 700; font-size: 1.05rem; color: #fff; margin-bottom: 4px; }
    .card-subtitle { color: var(--text-dim); font-size: 0.85rem; margin-bottom: 20px; }

    .btn-manage-node {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--accent-cyan);
        width: 100%;
        padding: 10px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.85rem;
        display: flex; align-items: center; justify-content: center;
        gap: 8px;
        transition: 0.2s;
    }

    .btn-manage-node:hover {
        background: var(--accent-cyan);
        color: #0b1228;
        border-color: var(--accent-cyan);
    }

    .form-input-dark {
        background: rgba(15, 23, 42, 0.8) !important;
        border: 1px solid var(--panel-border) !important;
        color: white !important;
        border-radius: 10px !important;
        padding: 12px !important;
    }
</style>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-800 mb-1">Departments & Offices</h2>
        <p class="text-dim">Manage institutional structural nodes and leadership.</p>
    </div>
    <button class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#registerNodeModal">
        <i class="bi bi-plus-lg me-2"></i> Register New
    </button>
</div>

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div class="alert alert-success bg-success text-white border-0 mb-4" style="border-radius: 12px;">
        {{ session('success') }}
    </div>
@endif

<div class="tab-container">
    <a href="?view=departments" class="tab-btn {{ $view != 'offices' ? 'active' : '' }} text-decoration-none">
        <i class="bi bi-layers-fill"></i> Departments
    </a>
    <a href="?view=offices" class="tab-btn {{ $view == 'offices' ? 'active' : '' }} text-decoration-none">
        <i class="bi bi-building-fill"></i> Offices
    </a>
</div>

<div class="row g-4">
    @if($view == 'offices')
        {{-- REAL OFFICES GRID --}}
        @forelse($offices as $office)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="mgmt-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-sq icon-offi">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="card-title text-truncate">{{ $office->name }}</div>
                        <div class="card-subtitle text-truncate">{{ $office->head ?? 'No Head Assigned' }}</div>
                    </div>
                </div>
                <button class="btn-manage-node">
                    <i class="bi bi-pencil-square"></i> Manage
                </button>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-dim">No offices registered yet.</p>
        </div>
        @endforelse
    @else
        {{-- REAL DEPARTMENTS GRID --}}
        @forelse($offices as $dept)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="mgmt-card">
                <div class="d-flex align-items-start gap-3">
                    <div class="icon-sq icon-dept">
                        <i class="bi bi-layers"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="card-title text-truncate">{{ $dept->department }}</div>
                        <div class="card-subtitle small">
                            {{-- Dynamic count of offices under this department --}}
                            @php
                                $count = \App\Models\Office::where('department', $dept->department)->count();
                            @endphp
                            <i class="bi bi-building me-1"></i> {{ $count }} Offices
                        </div>
                    </div>
                </div>
                <button class="btn-manage-node">
                    <i class="bi bi-pencil-square"></i> Manage
                </button>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <p class="text-dim">No departments found.</p>
        </div>
        @endforelse
    @endif
</div>

{{-- PAGINATION LINKS --}}
<div class="mt-5">
    {{ $offices->appends(['view' => $view])->links() }}
</div>

{{-- MODAL FOR REGISTERING NEW OFFICE --}}
<div class="modal fade" id="registerNodeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #161e31; border: 1px solid var(--panel-border); border-radius: 24px; color: white;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Register New Office</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('offices.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-dim fw-bold mb-2 uppercase">Official Name</label>
                        <input type="text" name="name" class="form-control form-input-dark" placeholder="e.g. Accounting Office" required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-dim fw-bold mb-2 uppercase">Parent Department</label>
                        <input type="text" name="department" class="form-control form-input-dark" placeholder="e.g. Finance & Admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-dim fw-bold mb-2 uppercase">Assigned Head</label>
                        <input type="text" name="head" class="form-control form-input-dark" placeholder="Full Name">
                    </div>
                    <div class="mb-0">
                        <label class="small text-dim fw-bold mb-2 uppercase">Status</label>
                        <select name="status" class="form-select form-input-dark">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px; background: var(--accent-cyan); border:none; color:#0b1228;">
                        Create Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection