@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<style>
    :root {
        --primary-bg: #1e293b;
        --secondary-bg: #0f172a;
        --accent-cyan: #00d7ff;
        --accent-success: #10b981;
        --border-color: rgba(0, 215, 255, 0.1);
        --text-primary: #e2e8f0;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
    }

    /* Dark Mode Adjustments */
    body.dark-mode {
        background: var(--secondary-bg);
    }

    body.light-mode {
        --primary-bg: #f8fafc;
        --secondary-bg: #ffffff;
        --accent-cyan: #0891b2;
        --border-color: rgba(2, 132, 199, 0.15);
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #64748b;
    }

    .filter-panel {
        background: var(--primary-bg);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .filter-input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-primary) !important;
        border-radius: 8px;
        padding: 10px 12px;
    }

    .activity-table {
        background: var(--primary-bg) !important;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        --bs-table-bg: var(--primary-bg) !important;
        --bs-table-hover-bg: rgba(0, 215, 255, 0.05) !important;
    }

    .activity-table thead {
        background: rgba(0, 215, 255, 0.08) !important;
    }

    .activity-table th {
        color: var(--accent-cyan) !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 14px 16px;
        border-color: var(--border-color) !important;
    }

    .activity-table td {
        color: var(--text-primary) !important;
        padding: 14px 16px;
        border-color: var(--border-color) !important;
    }

    /* --- PAGINATION STYLING --- */
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
        color: var(--accent-cyan);
        border-radius: 8px !important;
        margin: 0 3px;
        padding: 8px 16px;
        transition: 0.3s;
    }

    .pagination .page-link:hover {
        background: rgba(0, 215, 255, 0.1);
        border-color: var(--accent-cyan);
        color: var(--accent-cyan);
        transform: translateY(-1px);
    }

    .pagination .page-item.active .page-link {
        background: var(--accent-cyan);
        border-color: var(--accent-cyan);
        color: #0b1228 !important;
        font-weight: 700;
    }

    body.light-mode .pagination .page-link {
        background: #ffffff;
        border-color: rgba(2, 132, 199, 0.15);
        color: #475569;
    }

    body.light-mode .pagination .page-item.active .page-link {
        background: var(--accent-cyan);
        color: #ffffff !important;
    }

    .btn-search {
        background: linear-gradient(135deg, var(--accent-cyan), #0891b2);
        border: none;
        color: #000;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 24px;
        transition: 0.3s;
    }

    .btn-search:hover {
        box-shadow: 0 0 20px rgba(0, 215, 255, 0.3);
        transform: translateY(-2px);
    }

    .btn-reset {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-primary);
        border-radius: 8px;
        padding: 10px 20px;
        text-decoration: none;
        transition: 0.3s;
    }

    .activity-badge {
        padding: 6px 14px;
        border-radius: 24px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
        border: 1px solid;
    }

    .activity-badge.create { background: rgba(16, 185, 129, 0.15); color: #10b981; border-color: rgba(16, 185, 129, 0.35); }
    .activity-badge.routed { background: rgba(0, 215, 255, 0.15); color: var(--accent-cyan); border-color: rgba(0, 215, 255, 0.35); }
    .activity-badge.completed { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border-color: rgba(59, 130, 246, 0.35); }
    .activity-badge.scan { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border-color: rgba(245, 158, 11, 0.35); }

    .doc-title { color: var(--accent-cyan); text-decoration: none; font-weight: 500; }
    .doc-title:hover { text-decoration: underline; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title" style="font-size: 2rem; color: var(--accent-cyan); font-weight: 700;">Activity Logs</h1>
        <p class="page-subtitle" style="color: var(--text-secondary);">Audit entries for user and document actions.</p>
    </div>
</div>

<div class="filter-panel">
    <form method="GET" action="{{ route('activity.index') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label" style="color: var(--accent-cyan); font-size: 0.85rem; font-weight: 600;">Search</label>
            <input type="text" name="search" class="form-control filter-input" placeholder="Document or user..." value="{{ request('search') }}">
        </div>
        
        <div class="col-md-2">
            <label class="form-label" style="color: var(--accent-cyan); font-size: 0.85rem; font-weight: 600;">From Date</label>
            <input type="date" name="from_date" class="form-control filter-input" value="{{ request('from_date') }}">
        </div>
        
        <div class="col-md-2">
            <label class="form-label" style="color: var(--accent-cyan); font-size: 0.85rem; font-weight: 600;">To Date</label>
            <input type="date" name="to_date" class="form-control filter-input" value="{{ request('to_date') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label" style="color: var(--accent-cyan); font-size: 0.85rem; font-weight: 600;">Action</label>
            <select name="action" class="form-control filter-input">
                <option value="">All Actions</option>
                <option value="Document Created" {{ request('action') === 'Document Created' ? 'selected' : '' }}>Created</option>
                <option value="Document Routed" {{ request('action') === 'Document Routed' ? 'selected' : '' }}>Routed</option>
                <option value="QR Scanned" {{ request('action') === 'QR Scanned' ? 'selected' : '' }}>Scanned</option>
            </select>
        </div>

        <div class="col-md-12 d-flex gap-2 pt-3">
            <button type="submit" class="btn btn-search">
                <i class="bi bi-search me-2"></i>Search
            </button>
            <a href="{{ route('activity.index') }}" class="btn btn-reset">
                <i class="bi bi-arrow-clockwise me-2"></i>Reset
            </a>
        </div>
    </form>
</div>

<div class="table-responsive shadow-lg" style="border-radius: 12px;">
    <table class="table table-hover mb-0 activity-table">
        <thead>
            <tr>
                <th>Action</th>
                <th>User</th>
                <th>Document</th>
                <th>From</th>
                <th>To</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="align-middle">
                    @php
                        $action = strtolower($log->action);
                        $badgeClass = 'routed';
                        if (str_contains($action, 'created')) $badgeClass = 'create';
                        elseif (str_contains($action, 'completed')) $badgeClass = 'completed';
                        elseif (str_contains($action, 'scan')) $badgeClass = 'scan';
                    @endphp
                    <span class="activity-badge {{ $badgeClass }}">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="align-middle">
                    <span style="color: var(--text-secondary);">{{ $log->user }}</span>
                </td>
                <td class="align-middle">
                    @if($log->document)
                        <a href="{{ route('track.detail', $log->document->id) }}" class="doc-title">{{ $log->document->title }}</a>
                    @else
                        <span style="color: var(--text-muted);">System</span>
                    @endif
                </td>
                <td class="align-middle" style="color: var(--text-secondary);">
                    {{ $log->document?->originOffice?->name ?? 'N/A' }}
                </td>
                <td class="align-middle" style="color: var(--text-secondary);">
                    {{ $log->document?->destinationOffice?->name ?? 'N/A' }}
                </td>
                <td class="align-middle">
                    <span style="color: var(--text-secondary); font-size: 0.9rem;">
                        {{ is_string($log->created_at) ? $log->created_at : $log->created_at->format('M j, Y H:i') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5" style="color: var(--text-muted);">
                    <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p class="mt-2">No activity logs found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 pagination d-flex justify-content-center">
    @if(method_exists($logs, 'links'))
        {{ $logs->links('pagination::bootstrap-4') }}
    @endif
</div>

<script>
    function setActivityLogTheme() {
        const isDarkMode = localStorage.getItem('theme') === 'dark' || 
                          (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            document.body.classList.remove('light-mode');
        } else {
            document.body.classList.add('light-mode');
            document.body.classList.remove('dark-mode');
        }
    }

    document.addEventListener('DOMContentLoaded', setActivityLogTheme);
    window.addEventListener('storage', (e) => {
        if (e.key === 'theme') setActivityLogTheme();
    });
</script>
@endsection