@extends('layouts.app')

@section('title','Documents')

@section('content')
<style>
    .doc-card { background: #1c2536; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 20px; transition: 0.3s; }
    .doc-card:hover { transform: translateY(-5px); border-color: #22d3ee; }
    .doc-card.overdue { border-color: #ef4444; box-shadow: 0 0 10px rgba(239, 68, 68, 0.3); }
    .file-icon { width: 45px; height: 45px; background: rgba(255, 255, 255, 0.05); border-radius: 10px; display: grid; place-items: center; font-weight: 800; font-size: 0.75rem; color: #94a3b8; }
    .priority-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
    .priority-High { background: #ef4444; box-shadow: 0 0 8px #ef4444; }
    .priority-Med { background: #f59e0b; }
    .priority-Low { background: #38bdf8; }
    .upload-zone { border: 2px dashed rgba(148, 163, 184, 0.2); border-radius: 16px; padding: 30px; cursor: pointer; }

    /* --- FIX FOR BIG PAGINATION BUTTONS --- */
    .pagination nav svg {
        width: 1.25rem !important;
        height: 1.25rem !important;
    }
    .pagination .flex.justify-between.flex-1 {
        display: none !important;
    }
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
                <div class="doc-card p-4 h-100 {{ $doc->due_date && $doc->due_date->isPast() ? 'overdue' : '' }}">
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
                    @if($doc->receiverUser)
                        <div class="mb-4">
                            <small class="text-secondary d-block mb-1">Receiver</small>
                            <span class="small fw-bold">{{ $doc->receiverUser->name }}</span>
                            <div class="small text-muted">{{ $doc->receiverUser->department->name ?? 'No department' }}</div>
                        </div>
                    @endif
                    @if($doc->due_date)
                        <div class="mb-4">
                            <small class="text-secondary d-block mb-1">Due Date</small>
                            <span class="small fw-bold {{ $doc->due_date->isPast() ? 'text-danger' : 'text-warning' }}">
                                {{ $doc->due_date->format('M d, Y') }}
                            </span>
                        </div>
                    @endif
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-outline-info flex-grow-1 border-0" style="background: rgba(34, 211, 238, 0.05); padding: 8px 12px; font-size: 0.85rem;">View</a>
                        <button class="btn btn-outline-warning flex-grow-1 border-0" style="background: rgba(255, 193, 7, 0.05);" data-bs-toggle="modal" data-bs-target="#routeModal" onclick="setDocId({{ $doc->id }})">Route</button>
                        @if($doc->qr_code)
                        <button class="btn btn-outline-secondary flex-grow-1 border-0" style="background: rgba(255, 255, 255, 0.05); padding: 8px 12px; font-size: 0.85rem;" onclick="showQR('{{ asset('storage/' . $doc->qr_code) }}')">QR</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <p class="text-muted">No documents found. Upload your first one!</p>
            </div>
        @endforelse
    </div>

    @if(method_exists($documents, 'links'))
    <div class="d-flex justify-content-center mt-4">
        {{ $documents->links('pagination::bootstrap-4') }}
    </div>
    @endif
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

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Receiver User</label>
                        <select name="receiver_user_id" class="form-select bg-dark border-secondary text-white" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} @if($user->department) ({{ $user->department->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="form-label small fw-bold text-secondary">Set Priority</label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="priority" id="low" value="Low" checked>
                        <label class="btn btn-outline-secondary w-100" for="low">Low</label>
                        <input type="radio" class="btn-check" name="priority" id="med" value="Medium">
                        <label class="btn btn-outline-secondary w-100" for="med">Medium</label>
                        <input type="radio" class="btn-check" name="priority" id="high" value="High">
                        <label class="btn btn-outline-secondary w-100" for="high">High</label>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label small fw-bold text-secondary">SLA</label>
                        <select name="sla" class="form-select bg-dark border-secondary text-white" required>
                            <option value="Standard">Standard</option>
                            <option value="Expedited">Expedited</option>
                            <option value="Critical">Critical</option>
                        </select>
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

    function showQR(src) {
        document.getElementById('qrImage').src = src;
        new bootstrap.Modal(document.getElementById('qrModal')).show();
    }

    const selectedRecipients = new Map();
    const autoSelectedRecipients = new Set();

    function updateRecipientDisplay() {
        const container = document.getElementById('selectedRecipients');
        const selectedIds = document.getElementById('selectedIds');
        
        if (selectedRecipients.size === 0) {
            container.innerHTML = '<small class="text-muted w-100">No recipients selected yet</small>';
            selectedIds.value = '';
        } else {
            const chips = Array.from(selectedRecipients.values()).map(user => `
                <div class="badge bg-info text-dark" style="padding: 8px 12px; font-size: 0.85rem;">
                    ${user.name}
                    <button type="button" class="btn-close btn-close-white ms-2" 
                            onclick="removeRecipient(${user.id})" style="height: 12px; width: 12px;"></button>
                </div>
            `).join('');
            container.innerHTML = chips;
            selectedIds.value = Array.from(selectedRecipients.keys()).join(',');
        }
    }

    function removeRecipient(userId) {
        selectedRecipients.delete(userId);
        autoSelectedRecipients.delete(userId);
        updateRecipientDisplay();
        renderStaffList();
    }

    function addRecipient(user) {
        selectedRecipients.set(user.id, user);
        updateRecipientDisplay();
    }

    async function renderStaffList() {
        const officeSelect = document.getElementById('officeSelect');
        const officeId = officeSelect.value;
        const container = document.getElementById('staffContainer');

        if (!officeId) {
            container.innerHTML = '<div class="text-muted text-center py-4"><small>👇 Select an office to see available staff</small></div>';
            return;
        }

        try {
            const response = await fetch(`/api/offices/${officeId}/staff`);
            const data = await response.json();

            if (data.staff.length === 0) {
                container.innerHTML = '<div class="text-muted text-center py-4"><small>No staff assigned to this office</small></div>';
                return;
            }

            const html = `
                <div class="list-group" style="border: none;">
                    ${data.staff.map(user => {
                        const isSelected = selectedRecipients.has(user.id);
                        const isAutoSelected = autoSelectedRecipients.has(user.id);
                        return `
                            <label class="list-group-item" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 8px; cursor: pointer; ${isAutoSelected ? 'box-shadow: 0 0 10px rgba(0,215,255,0.3);' : ''}">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input me-3" 
                                           ${isSelected ? 'checked' : ''} 
                                           onchange="toggleRecipient(${JSON.stringify(user)}, this.checked)"
                                           ${isAutoSelected ? 'disabled' : ''}>
                                    <div style="flex: 1;">
                                        <div class="small fw-bold text-info">${user.name}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">${user.role} • ${data.office}</div>
                                    </div>
                                    ${isAutoSelected ? '<span class="badge bg-success">Auto-assigned</span>' : ''}
                                </div>
                            </label>
                        `;
                    }).join('')}
                </div>
            `;
            container.innerHTML = html;
        } catch (error) {
            console.error('Error loading staff:', error);
            container.innerHTML = '<div class="text-danger small">Error loading staff members</div>';
        }
    }

    function toggleRecipient(user, isChecked) {
        if (isChecked) {
            addRecipient(user);
        } else {
            selectedRecipients.delete(user.id);
            autoSelectedRecipients.delete(user.id);
            updateRecipientDisplay();
            renderStaffList();
        }
    }

    async function autoAssignStaff() {
        const officeSelect = document.getElementById('officeSelect');
        const officeId = officeSelect.value;
        if (!officeId) return;

        try {
            const response = await fetch(`/api/offices/${officeId}/staff`);
            const data = await response.json();
            selectedRecipients.clear();
            autoSelectedRecipients.clear();
            data.staff.forEach(user => {
                selectedRecipients.set(user.id, user);
                autoSelectedRecipients.add(user.id);
            });
            updateRecipientDisplay();
            renderStaffList();
        } catch (error) { console.error('Error auto-assigning:', error); }
    }

    function clearRecipients() {
        selectedRecipients.clear();
        autoSelectedRecipients.clear();
        updateRecipientDisplay();
        renderStaffList();
    }

    document.getElementById('officeSelect').addEventListener('change', function() {
        clearRecipients();
        renderStaffList();
    });

    document.getElementById('autoAssignToggle').addEventListener('change', function() {
        if (this.checked) autoAssignStaff();
        else clearRecipients();
    });

    function setDocId(docId) {
        document.getElementById('docId').value = docId;
        const form = document.getElementById('routeForm');
        form.action = '/routing/update/' + docId;
        selectedRecipients.clear();
        autoSelectedRecipients.clear();
        document.getElementById('officeSelect').value = '';
        document.getElementById('autoAssignToggle').checked = false;
        updateRecipientDisplay();
        renderStaffList();
    }

    document.getElementById('routeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.delete('receiver_user_ids[]');
        Array.from(selectedRecipients.keys()).forEach(id => {
            formData.append('receiver_user_ids[]', id);
        });

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    });
</script>

<div class="modal fade" id="routeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:#161e31; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; color:white;">
            <form action="/routing/update/0" method="POST" id="routeForm">
                @csrf
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold">📤 Route Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0" style="max-height: 500px; overflow-y: auto;">
                    <input type="hidden" id="docId" name="doc_id">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-info">📍 Send To Office</label>
                        <select name="office_id" id="officeSelect" class="form-select bg-dark border-secondary text-white" required>
                            <option value="">-- Select office --</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->department }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4 p-3" style="background: rgba(0,215,255,0.08); border-radius: 10px; border: 1px solid rgba(0,215,255,0.2);">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="autoAssignToggle">
                            <label class="form-check-label fw-bold text-info" for="autoAssignToggle">⚡ Auto-assign all staff</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-info">👥 Recipients</label>
                        <div id="staffContainer" class="p-3" style="background: rgba(255,255,255,0.04); border-radius: 10px; min-height: 120px; border: 1px solid rgba(255,255,255,0.08);">
                            <div class="text-muted text-center py-4"><small>Select an office first</small></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-success">✅ Selected Recipients</label>
                        <div id="selectedRecipients" class="d-flex flex-wrap gap-2">
                            <small class="text-muted w-100">No recipients selected</small>
                        </div>
                        <input type="hidden" name="receiver_user_ids[]" id="selectedIds" value="">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-info">📝 Notes (Optional)</label>
                        <textarea name="notes" class="form-control bg-dark border-secondary text-white" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-4" style="border-color: rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-5" style="border-radius: 8px; color: #000;">✓ Route Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Document QR Code</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImage" src="" alt="QR Code" class="img-fluid">
            </div>
        </div>
    </div>
</div>
@endsection