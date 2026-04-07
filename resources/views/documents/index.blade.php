@extends('layouts.app')

@section('title','Documents')

@section('content')
<style>
    .doc-card { background: #1c2536; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; transition: 0.3s; }
    .doc-card:hover { transform: translateY(-5px); border-color: #22d3ee; }
    .file-icon { width: 45px; height: 45px; background: rgba(255, 255, 255, 0.05); border-radius: 10px; display: grid; place-items: center; font-weight: 800; font-size: 0.75rem; color: #94a3b8; }
    .priority-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
    .priority-High { background: #ef4444; box-shadow: 0 0 8px #ef4444; }
    .priority-Med { background: #f59e0b; }
    .priority-Low { background: #38bdf8; }
    .upload-zone { border: 2px dashed rgba(148, 163, 184, 0.2); border-radius: 16px; padding: 30px; cursor: pointer; }
</style>

<div class="container-fluid p-4">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">Check form for errors.</div> @endif

    <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">
        <div>
            <h1 class="fw-800 mb-1" style="font-weight: 800;">📁 Documents</h1>
            <p style="color:#94a3b8; margin:0;">Manage and track organization files</p>
        </div>
        <button class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#uploadModal">
            + Upload New
        </button>
    </div>

    <div class="row g-4">
        @forelse($documents as $doc)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="doc-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="file-icon text-uppercase">{{ $doc->type ?? 'FILE' }}</div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $doc->title }}</h6>
                                <small style="color:#94a3b8;">{{ $doc->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                        <span class="badge bg-info rounded-pill px-3">{{ $doc->status }}</span>
                    </div>
                    <div class="mb-4">
                        <small class="text-secondary d-block mb-1">Priority</small>
                        <div class="d-flex align-items-center">
                            <span class="priority-dot priority-{{ $doc->priority }}"></span>
                            <span class="small fw-bold">{{ $doc->priority }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-info flex-grow-1 border-0" style="background: rgba(34, 211, 238, 0.05);">View</a>
                        <button class="btn btn-outline-light flex-grow-1 border-0" style="background: rgba(255, 255, 255, 0.05);">Route</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <p class="text-muted">No documents found. Upload your first one!</p>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#161e31; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; color:white;">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold">Upload Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="upload-zone text-center mb-4" onclick="document.getElementById('fileHidden').click()">
                        <div style="font-size: 2rem;" class="mb-2">📤</div>
                        <h6 class="fw-bold mb-1" id="fileNameDisplay">Drop your files here</h6>
                        <p class="small text-secondary mb-0">or click to browse</p>
                        <input type="file" name="file" id="fileHidden" class="d-none" required onchange="updateFileName(this)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Document Title</label>
                        <input type="text" name="title" class="form-control bg-dark border-secondary text-white py-2" required placeholder="Enter file name">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Origin Office</label>
                            <select name="origin_office_id" class="form-select bg-dark border-secondary text-white" required>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Destination</label>
                            <select name="destination_office_id" class="form-select bg-dark border-secondary text-white" required>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="form-label small fw-bold text-secondary">Set Priority</label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="priority" id="low" value="Low" checked>
                        <label class="btn btn-outline-secondary w-100" for="low">Low</label>
                        <input type="radio" class="btn-check" name="priority" id="med" value="Med">
                        <label class="btn btn-outline-secondary w-100" for="med">Med</label>
                        <input type="radio" class="btn-check" name="priority" id="high" value="High">
                        <label class="btn btn-outline-secondary w-100" for="high">High</label>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="border-radius: 12px;">Confirm Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        if (input.files.length > 0) {
            display.innerText = input.files[0].name;
            display.style.color = "#22d3ee";
        }
    }
</script>
@endsection