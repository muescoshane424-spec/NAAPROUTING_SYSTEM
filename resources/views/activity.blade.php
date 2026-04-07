@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Activity Logs</h1>
<p class="text-gray-300 mb-6">Audit entries for user and document actions.</p>

<div class="table-responsive shadow-lg rounded-xl" style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(0, 215, 255, 0.1);">
    <table class="table table-dark table-hover mb-0">
        <thead style="background: rgba(0, 215, 255, 0.05);">
            <tr>
                <th class="border-0 text-cyan-300">Action</th>
                <th class="border-0 text-cyan-300">User</th>
                <th class="border-0 text-cyan-300">Document</th>
                <th class="border-0 text-cyan-300">IP Address</th>
                <th class="border-0 text-cyan-300">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="align-middle">
                    @php
                        $isCreated = str_contains(strtolower($log->action), 'created');
                        $badgeClass = $isCreated ? 'create' : 'scan';
                    @endphp
                    <span class="activity-badge {{ $badgeClass }}">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="align-middle text-gray-200">{{ $log->user }}</td>
                <td class="align-middle">
                    <span class="text-info">{{ $log->document ? $log->document->title : 'System' }}</span>
                </td>
                <td class="align-middle font-mono small text-gray-400">{{ $log->ip }}</td>
                <td class="align-middle text-gray-300">
                    {{-- Check if created_at is a Carbon instance before formatting --}}
                    {{ is_string($log->created_at) ? $log->created_at : $log->created_at->format('M j, Y H:i') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-gray-500">No activity logs found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{-- Only show links if the $logs variable is a Paginator --}}
    @if(method_exists($logs, 'links'))
        {{ $logs->links() }}
    @endif
</div>

<style>
    .activity-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    .activity-badge.create {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .activity-badge.scan {
        background: rgba(0, 215, 255, 0.2);
        color: #00d7ff;
        border: 1px solid rgba(0, 215, 255, 0.3);
    }
    .table td, .table th {
        padding: 16px;
        border-color: rgba(255, 255, 255, 0.05);
    }
</style>
@endsection