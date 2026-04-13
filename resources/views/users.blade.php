@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<style>
    .mgmt-card {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 20px;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .custom-table {
        background: transparent !important;
        color: white !important;
    }
    .custom-table thead {
        background: rgba(255, 255, 255, 0.03);
    }
    .custom-table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--accent-cyan);
        padding: 15px;
        border-bottom: 1px solid var(--panel-border);
        background: transparent !important;
    }
    .custom-table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        background: transparent !important;
        color: rgba(255,255,255,0.85) !important;
    }
    .custom-table th {
        color: rgba(255,255,255,0.9) !important;
    }
    .custom-table td.text-dim {
        color: rgba(255,255,255,0.65) !important;
    }
    .table-responsive {
        background: transparent;
    }
    #tableSearch {
        background: rgba(255,255,255,0.08) !important;
        border: 1px solid rgba(255,255,255,0.12) !important;
        color: white !important;
    }
    #tableSearch::placeholder {
        color: rgba(255,255,255,0.65) !important;
    }

    .user-avatar {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 0.9rem; color: #0b1228; /* Darker text for contrast on cyan */
    }

    .form-label { font-weight: 600; font-size: 0.85rem; color: var(--text-dim); background: transparent; }
    .form-control-custom,
    .form-select.form-control-custom {
        background: rgba(255,255,255,0.06) !important;
        border: 1px solid var(--panel-border) !important;
        color: #fff !important;
        border-radius: 12px;
        padding: 12px;
        box-shadow: none !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
    }
    .form-control-custom::placeholder {
        color: rgba(255,255,255,0.75) !important;
    }
    .form-select.form-control-custom option {
        color: #0b1228 !important;
        background: #f8fafc !important;
    }
    .form-select.form-control-custom option:disabled {
        color: rgba(75,85,99,0.75) !important;
    }
    .form-control-custom:focus,
    .form-select.form-control-custom:focus {
        border-color: var(--accent-cyan) !important;
        box-shadow: 0 0 12px rgba(34, 211, 238, 0.12) !important;
    }

    .btn-action {
        width: 35px; height: 35px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; transition: 0.2s;
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--panel-border);
        color: white;
    }
    .btn-edit:hover { color: var(--accent-cyan); border-color: var(--accent-cyan); }
    .btn-delete:hover { color: #fb7185; border-color: #fb7185; }

    /* Custom button overrides to match your theme */
    .btn-theme-cyan {
        background: var(--accent-cyan);
        color: #0b1228;
        border: none;
    }
    .btn-theme-cyan:hover {
        background: #06b6d4;
        color: #0b1228;
        box-shadow: 0 0 20px rgba(34, 211, 238, 0.2);
    }

    .btn-theme-purple {
        background: var(--accent-purple);
        color: white;
        border: none;
    }
    .btn-theme-purple:hover {
        background: #9333ea;
        color: white;
        box-shadow: 0 0 20px rgba(168, 85, 247, 0.2);
    }
</style>

{{-- ALERT NOTIFICATIONS --}}
@if(session('success'))
    <div class="alert alert-success bg-success bg-opacity-10 text-success border-success border-opacity-25 mb-4" style="border-radius:12px;">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-danger border-opacity-25 mb-4" style="border-radius:12px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="mgmt-card h-100">
            <div class="p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-bottom border-secondary border-opacity-10">
                <div>
                    <h5 class="mb-0 fw-bold text-white"><i class="bi bi-people me-2" style="color: var(--accent-cyan);"></i>Users</h5>
                    <small class="text-dim">Manage system access</small>
                </div>
                <div class="d-flex gap-2">
                    <input type="text" id="tableSearch" class="form-control form-control-sm search-input" placeholder="Search users..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; border-radius: 10px; max-width: 300px;">
                    <div class="badge bg-dark border border-secondary px-3 py-2 rounded-pill d-flex align-items-center">
                        Total: {{ $users->count() }}
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table custom-table table-borderless mb-0 text-white">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">{{ substr($user->name, 0, 1) }}</div>
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="text-dim">{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td class="text-dim">{{ $user->department->name ?? 'Unassigned' }}</td>
                            <td class="text-end">
                                <button class="btn-action btn-edit me-1" 
                                        onclick="prepareEdit({{ json_encode($user) }})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="mgmt-card sticky-top" style="top: 20px;">
            <div class="p-4 border-bottom border-secondary border-opacity-10">
                <h5 class="mb-0 fw-bold text-white" id="formTitle">Add New User</h5>
            </div>
            <div class="p-4">
                <form id="userForm" action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div id="methodField"></div> {{-- Placeholder for @method('PUT') --}}
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="userNameInput" value="{{ old('name') }}" class="form-control form-control-custom @error('name') is-invalid @enderror" placeholder="e.g. John Doe" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="userEmailInput" value="{{ old('email') }}" class="form-control form-control-custom @error('email') is-invalid @enderror" placeholder="admin@naap.edu" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="userRoleInput" class="form-select form-control-custom @error('role') is-invalid @enderror" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select role</option>
                            <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>ADMIN</option>
                            <option value="USER" {{ old('role') === 'USER' ? 'selected' : '' }}>USER</option>
                            <option value="HEAD" {{ old('role') === 'HEAD' ? 'selected' : '' }}>HEAD</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" id="userDepartmentInput" class="form-select form-control-custom @error('department_id') is-invalid @enderror" required>
                            <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Select department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="userPassInput" class="form-control form-control-custom" placeholder="••••••••">
                        <small class="text-muted mt-1 d-none" id="passHint">Leave blank to keep current</small>
                    </div>

                    <button type="submit" class="btn btn-theme-cyan w-100 fw-bold py-2 rounded-3" id="submitBtn">
                        Create User Account
                    </button>
                    <button type="button" class="btn btn-link w-100 text-dim mt-2 text-decoration-none d-none" id="cancelBtn" onclick="resetUI()">
                        Cancel & Reset
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // --- SEARCH LOGIC ---
    document.getElementById('tableSearch').oninput = (e) => {
        const term = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#userTableBody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    };

    // --- PREPARE EDIT ---
    window.prepareEdit = (user) => {
        const form = document.getElementById('userForm');
        const methodField = document.getElementById('methodField');
        
        document.getElementById('formTitle').innerText = 'Update User Details';
        document.getElementById('submitBtn').innerText = 'Save Changes';
        // Switching to the Purple theme for "Edit Mode"
        document.getElementById('submitBtn').classList.replace('btn-theme-cyan', 'btn-theme-purple');
        
        document.getElementById('cancelBtn').classList.remove('d-none');
        document.getElementById('passHint').classList.remove('d-none');
        
        form.action = `/users/${user.id}`;
        methodField.innerHTML = `@method('PUT')`;
        
        document.getElementById('userNameInput').value = user.name;
        document.getElementById('userEmailInput').value = user.email;
        document.getElementById('userRoleInput').value = user.role || 'USER';
        document.getElementById('userDepartmentInput').value = user.department_id || '';
        document.getElementById('userPassInput').required = false;
        document.getElementById('userNameInput').focus();
    };

    window.resetUI = () => {
        const form = document.getElementById('userForm');
        const methodField = document.getElementById('methodField');
        
        document.getElementById('formTitle').innerText = 'Add New User';
        document.getElementById('submitBtn').innerText = 'Create User Account';
        // Switching back to Cyan for "Add Mode"
        document.getElementById('submitBtn').classList.replace('btn-theme-purple', 'btn-theme-cyan');
        
        document.getElementById('cancelBtn').classList.add('d-none');
        document.getElementById('passHint').classList.add('d-none');
        
        form.action = "{{ route('users.store') }}";
        methodField.innerHTML = '';
        form.reset();
        document.getElementById('userPassInput').required = true;
        document.getElementById('userRoleInput').value = '';
        document.getElementById('userDepartmentInput').value = '';
    };
</script>
@endsection