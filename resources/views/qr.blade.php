@extends('layouts.app')
@section('title', 'Route Document')

@section('head')
<script src="https://unpkg.com/html5-qrcode"></script>
<style>
    :root {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --panel-border: rgba(255, 255, 255, 0.08);
        --accent-cyan: #22d3ee;
        --accent-purple: #a855f7;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-orange: #f59e0b;
    }
    .glass-card {
        background: var(--glass-bg);
        border: 1px solid var(--panel-border);
        border-radius: 24px;
        backdrop-filter: blur(10px);
    }
    .form-label-custom {
        color: #94a3b8; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: block;
    }
    .custom-input {
        background: #1e293b !important; border: 1px solid var(--panel-border) !important;
        color: white !important; padding: 12px 16px; border-radius: 12px;
    }
    .custom-input:focus {
        border-color: var(--accent-cyan) !important;
        box-shadow: 0 0 0 2px rgba(34, 211, 238, 0.1);
    }
    
    .priority-pill {
        flex: 1; padding: 10px; border: 1px solid var(--panel-border);
        background: transparent; color: #64748b; border-radius: 12px; 
        font-weight: 600; transition: all 0.2s ease; cursor: pointer;
    }
    .priority-pill:hover { background: rgba(255, 255, 255, 0.05); }
    
    .priority-pill.active[data-value="Low"] { background: rgba(16, 185, 129, 0.1); border-color: var(--accent-green); color: var(--accent-green); }
    .priority-pill.active[data-value="Medium"] { background: rgba(245, 158, 11, 0.1); border-color: var(--accent-orange); color: var(--accent-orange); }
    .priority-pill.active[data-value="High"] { background: rgba(239, 68, 68, 0.1); border-color: var(--accent-red); color: var(--accent-red); }

    .step-line { position: absolute; left: 17px; top: 36px; bottom: -20px; width: 2px; background: rgba(255, 255, 255, 0.1); }
    .qr-preview-box {
        border: 2px dashed rgba(255,255,255,0.1); border-radius: 20px;
        min-height: 300px; display: grid; place-items: center; background: rgba(0,0,0,0.2); overflow: hidden;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px; background: rgba(16, 185, 129, 0.2); color: #10b981;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('qr.store') }}" method="POST"> 
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="glass-card p-4 mb-4">
                    <h5 class="mb-4 d-flex align-items-center gap-2">
                        <span class="text-info">📄</span> Document Information
                    </h5>
                    <div class="mb-3">
                        <label class="form-label-custom">Document Title *</label>
                        <input type="text" name="title" id="doc-title" class="form-control custom-input" placeholder="Enter document title" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label-custom">Description</label>
                        <textarea name="description" class="form-control custom-input" rows="3" placeholder="Brief description of the document"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Document Type</label>
                            <input type="text" name="type" class="form-control custom-input" placeholder="e.g., Enrollment Form">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Priority Level *</label>
                            <input type="hidden" name="priority" id="selected-priority" value="Medium">
                            <div class="d-flex gap-2" id="priority-selector">
                                <button type="button" class="priority-pill" data-value="Low">Low</button>
                                <button type="button" class="priority-pill active" data-value="Medium">Medium</button>
                                <button type="button" class="priority-pill" data-value="High">High</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-4">
                    <h5 class="mb-4 d-flex align-items-center gap-2">
                        <span style="color: var(--accent-purple)">→</span> Routing Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Origin Office *</label>
                            <select name="origin_office_id" class="form-select custom-input" required>
                                <option value="">Select origin office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Destination Office *</label>
                            <select name="destination_office_id" class="form-select custom-input" required>
                                <option value="">Select destination office</option>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn px-5 py-3 fw-bold" style="background: var(--accent-cyan); border-radius: 12px; color: #0f172a;">
                            Generate & Route Document
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card p-4 mb-4">
                    <h5 class="mb-4">QR Scanner</h5>
                    <div id="reader" class="qr-preview-box">
                        <div id="scanner-placeholder">
                            <div class="bg-dark rounded-circle d-inline-block p-3 mb-3 border border-secondary">
                                <span style="font-size: 2rem;">📷</span>
                            </div>
                            <p class="text-muted small">Camera inactive</p>
                        </div>
                    </div>
                    <button type="button" id="start-scan" class="btn w-100 mt-3 p-3 fw-bold text-dark" style="background: #e2e8f0; border-radius: 12px;">
                        Start Camera
                    </button>
                </div>

                <div class="glass-card p-4">
                    <h5 class="mb-4">Routing Flow</h5>
                    <div class="position-relative">
                        @php
                            $steps = [
                                ['n' => 1, 't' => 'Document Created', 'd' => 'Origin office', 'color' => 'var(--accent-cyan)'],
                                ['n' => 2, 't' => 'In Transit', 'd' => 'Being routed', 'color' => 'var(--accent-purple)'],
                                ['n' => 3, 't' => 'Received', 'd' => 'Destination office', 'color' => 'var(--accent-green)'],
                            ];
                        @endphp
                        @foreach($steps as $st)
                        <div class="d-flex mb-4 position-relative align-items-center">
                            @if(!$loop->last) <div class="step-line"></div> @endif
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" 
                                 style="width: 36px; height: 36px; background: {{ $st['color'] }}; color: #000; z-index: 2;">
                                {{ $st['n'] }}
                            </div>
                            <div>
                                <div class="fw-bold small text-white">{{ $st['t'] }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $st['d'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Priority Selector Logic ---
        const pills = document.querySelectorAll('.priority-pill');
        const hiddenInput = document.getElementById('selected-priority');

        pills.forEach(pill => {
            pill.addEventListener('click', function() {
                pills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                hiddenInput.value = this.getAttribute('data-value');
            });
        });

        // --- 2. QR Scanner Logic ---
        const scannerBtn = document.getElementById('start-scan');
        const placeholder = document.getElementById('scanner-placeholder');
        const docTitleInput = document.getElementById('doc-title');
        let html5QrCode;

        scannerBtn.addEventListener('click', function() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    placeholder.style.display = 'block';
                    scannerBtn.innerText = "Start Camera";
                });
                return;
            }

            placeholder.style.display = 'none';
            scannerBtn.innerText = "Stop Camera";
            html5QrCode = new Html5Qrcode("reader");
            
            html5QrCode.start(
                { facingMode: "environment" }, 
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    // Logic: Auto fill the title if scanned
                    docTitleInput.value = decodedText;
                    
                    // Visual feedback
                    docTitleInput.style.borderColor = "var(--accent-green)";
                    setTimeout(() => docTitleInput.style.borderColor = "", 2000);
                    
                    // Stop camera after successful scan
                    html5QrCode.stop().then(() => {
                        placeholder.style.display = 'block';
                        scannerBtn.innerText = "Start Camera";
                    });
                },
                (errorMessage) => { /* quiet scanning */ }
            ).catch((err) => {
                console.error("Camera error:", err);
                placeholder.style.display = 'block';
                scannerBtn.innerText = "Start Camera";
            });
        });
    });
</script>
@endsection