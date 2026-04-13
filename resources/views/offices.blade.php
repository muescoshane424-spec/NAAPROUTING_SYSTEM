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
                <button class="btn-manage-node" data-bs-toggle="modal" data-bs-target="#editOfficeModal" 
                    onclick="editOffice({{ $office->id }}, '{{ $office->name }}', '{{ $office->department }}', '{{ $office->head }}', '{{ $office->status }}')">
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
                <button class="btn-manage-node" data-bs-toggle="modal" data-bs-target="#manageDeptModal" 
                    onclick="loadDepartmentData('{{ $dept->department }}')">
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

{{-- MODAL FOR EDITING OFFICE --}}
<div class="modal fade" id="editOfficeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #161e31; border: 1px solid var(--panel-border); border-radius: 24px; color: white;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Edit Office</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editOfficeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="small text-dim fw-bold mb-2 uppercase">Office Name</label>
                        <input type="text" name="name" id="editOfficeName" class="form-control form-input-dark" required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-dim fw-bold mb-2 uppercase">Department</label>
                        <input type="text" name="department" id="editOfficeDept" class="form-control form-input-dark" required>
                    </div>
                    <div class="mb-3">
                        <label class="small text-dim fw-bold mb-2 uppercase">Head</label>
                        <input type="text" name="head" id="editOfficeHead" class="form-control form-input-dark">
                    </div>
                    <div class="mb-0">
                        <label class="small text-dim fw-bold mb-2 uppercase">Status</label>
                        <select name="status" id="editOfficeStatus" class="form-select form-input-dark">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 py-2 fw-bold" style="border-radius: 12px; background: var(--accent-cyan); border:none; color:#0b1228;">
                        Update
                    </button>
                    <button type="button" class="btn btn-danger flex-grow-1 py-2 fw-bold" id="deleteOfficeBtn" style="border-radius: 12px;">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL FOR MANAGING DEPARTMENT --}}
<div class="modal fade" id="manageDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #161e31; border: 1px solid var(--panel-border); border-radius: 24px; color: white;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold">Manage Department: <span id="deptName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <h6 class="text-cyan fw-bold mb-3">Offices in Department</h6>
                <div id="deptOfficesList" style="max-height: 200px; overflow-y: auto; margin-bottom: 20px;">
                    <p class="text-dim text-center py-3">Loading...</p>
                </div>

                <h6 class="text-cyan fw-bold mb-3">Users in Department</h6>
                <div id="deptUsersList" style="max-height: 200px; overflow-y: auto;">
                    <p class="text-dim text-center py-3">Loading...</p>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary">
                    <label class="small text-dim fw-bold mb-2 uppercase">Rename Department</label>
                    <div class="input-group">
                        <input type="text" id="deptNameInput" class="form-control form-input-dark" placeholder="New name...">
                        <button type="button" class="btn btn-cyan fw-bold" id="renameDeptBtn" style="background: var(--accent-cyan); color: #0b1228; border: none; border-radius: 0 10px 10px 0;">
                            Rename
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Show notification if there's a success message
    @if(session('success'))
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('{{ session('success') }}', 'success');
        });
    @endif

    function loadDepartmentData(deptName) {
        document.getElementById('deptName').textContent = deptName;
        document.getElementById('deptNameInput').value = deptName;
        
        // Fetch offices in this department
        fetch(`/api/departments/${encodeURIComponent(deptName)}/offices`)
            .then(r => r.json())
            .then(offices => {
                const html = offices.length > 0 
                    ? offices.map(o => `
                        <div style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${o.name}</strong>
                                <br><small class="text-dim">${o.head || 'No head assigned'}</small>
                            </div>
                            <span class="badge ${o.status === 'active' ? 'bg-success' : 'bg-secondary'}">${o.status}</span>
                        </div>
                    `).join('')
                    : '<p class="text-center text-dim py-3">No offices in this department</p>';
                
                document.getElementById('deptOfficesList').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('deptOfficesList').innerHTML = '<p class="text-center text-danger">Error loading offices</p>';
            });
        
        // Fetch users in this department
        fetch(`/api/departments/${encodeURIComponent(deptName)}/users`)
            .then(r => r.json())
            .then(users => {
                const html = users.length > 0 
                    ? users.map(u => `
                        <div style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${u.name}</strong>
                                <br><small class="text-dim">${u.email}</small>
                            </div>
                            <span class="badge bg-info">${u.role}</span>
                        </div>
                    `).join('')
                    : '<p class="text-center text-dim py-3">No users in this department yet</p>';
                
                document.getElementById('deptUsersList').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('deptUsersList').innerHTML = '<p class="text-center text-dim">No users in this department</p>';
            });
    }

    document.getElementById('renameDeptBtn').addEventListener('click', function() {
        const oldName = document.getElementById('deptName').textContent;
        const newName = document.getElementById('deptNameInput').value;
        
        if (!newName.trim()) {
            showNotification('Please enter a new department name', 'error');
            return;
        }
        
        fetch('/api/departments/rename', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ old_name: oldName, new_name: newName })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showNotification('Department renamed successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification(data.message || 'Error renaming department', 'error');
            }
        });
    });

    function editOffice(id, name, dept, head, status) {
        document.getElementById('editOfficeName').value = name;
        document.getElementById('editOfficeDept').value = dept;
        document.getElementById('editOfficeHead').value = head || '';
        document.getElementById('editOfficeStatus').value = status;
        
        const form = document.getElementById('editOfficeForm');
        form.action = `/offices/${id}`;
        
        document.getElementById('deleteOfficeBtn').onclick = function() {
            if(confirm('Are you sure you want to delete this office?')) {
                const deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = `/offices/${id}`;
                deleteForm.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(deleteForm);
                deleteForm.submit();
            }
        };
    }
</script>
@endsection