@extends('layouts.app')

@section('title', 'User Profile')

@section('content')
<style>
    .profile-header {
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.15), rgba(168, 85, 247, 0.15));
        border: 1px solid var(--panel-border);
        border-radius: 24px;
        padding: 30px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar-lg {
        width: 80px; height: 80px;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
        border-radius: 20px;
        display: grid; place-items: center;
        font-size: 2rem; font-weight: 800; color: white;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    .info-card {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 20px;
        padding: 24px;
        height: 100%;
    }

    .info-label {
        color: var(--text-dim);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    #sig-canvas {
        border: 2px dashed var(--panel-border);
        border-radius: 12px;
        cursor: crosshair;
        background: rgba(255,255,255,0.02);
        width: 100%;
        height: 180px;
        touch-action: none; /* Critical for mobile drawing */
    }

    @media (max-width: 768px) {
        .profile-header { flex-direction: column; text-align: center; }
    }
</style>

<div class="container-fluid p-0">
    @if(session('success'))
        <div class="alert alert-success border-0 bg-success text-white rounded-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="profile-header">
        <div class="profile-avatar-lg">{{ substr($user->email, 0, 1) }}</div>
        <div class="flex-grow-1">
            <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
            <p class="text-info mb-0 small"><i class="bi bi-shield-check me-1"></i> {{ strtoupper($user->role) }}</p>
        </div>
        <div class="form-check form-switch bg-dark px-3 py-2 rounded-pill border border-secondary">
            <input class="form-check-input me-2" type="checkbox" role="switch" id="themeToggle" checked>
            <label class="form-check-label small text-white" for="themeToggle">Dark Mode</label>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="info-card">
                <h6 class="fw-bold mb-4 text-cyan"><i class="bi bi-person-gear me-2"></i>Edit Information</h6>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="info-label">Full Name</label>
                        <input type="text" name="name" class="form-control bg-dark border-secondary text-white rounded-3" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="info-label">Email Address</label>
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-white rounded-3" value="{{ $user->email }}" required>
                    </div>
                    <button type="submit" class="btn btn-info w-100 rounded-3 fw-bold">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="info-card">
                <h6 class="fw-bold mb-4 text-purple"><i class="bi bi-pen me-2"></i>Digital Signature</h6>
                <ul class="nav nav-pills nav-justified mb-3 bg-dark rounded-3 p-1">
                    <li class="nav-item">
                        <button class="nav-link active small py-1" data-bs-toggle="pill" data-bs-target="#draw-sig">Draw</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link small py-1" data-bs-toggle="pill" data-bs-target="#upload-sig">Upload</button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="draw-sig">
                        <canvas id="sig-canvas"></canvas>
                        <form id="sig-form" action="{{ route('profile.signature') }}" method="POST">
                            @csrf
                            <input type="hidden" name="signature_data" id="signature_data">
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary w-50" id="sig-clear">Clear</button>
                                <button type="submit" class="btn btn-sm btn-purple w-50 text-white fw-bold">Save Signature</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="upload-sig">
                        <form action="{{ route('profile.signature') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="py-4 text-center border border-dashed border-secondary rounded-3">
                                <i class="bi bi-cloud-arrow-up fs-2 text-dim"></i>
                                <input type="file" name="sig_file" class="form-control form-control-sm mt-2 bg-transparent border-0 text-white">
                            </div>
                            <button type="submit" class="btn btn-purple btn-sm w-100 mt-3 rounded-3">Upload File</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="info-card">
                <h6 class="fw-bold mb-4 text-warning"><i class="bi bi-key me-2"></i>Security Settings</h6>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="info-label">New Password</label>
                        <input type="password" name="password" class="form-control bg-dark border-secondary text-white rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="info-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control bg-dark border-secondary text-white rounded-3" required>
                    </div>
                    <button type="submit" class="btn btn-outline-warning w-100 rounded-3 fw-bold">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // --- SIGNATURE DRAWING LOGIC ---
    const canvas = document.getElementById('sig-canvas');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    // Adjust canvas resolution
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        ctx.scale(ratio, ratio);
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    function getMousePos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: clientX - rect.left, y: clientY - rect.top };
    }

    function startDrawing(e) { drawing = true; draw(e); }
    function stopDrawing() { drawing = false; ctx.beginPath(); }

    function draw(e) {
        if (!drawing) return;
        const pos = getMousePos(e);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#22d3ee';
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDrawing);

    canvas.addEventListener('touchstart', startDrawing);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDrawing);

    document.getElementById('sig-clear').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });

    document.getElementById('sig-form').addEventListener('submit', function() {
        document.getElementById('signature_data').value = canvas.toDataURL();
    });

    // --- LIGHT/DARK MODE PERSISTENCE ---
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', function() {
        if(this.checked) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    });
</script>
@endsection