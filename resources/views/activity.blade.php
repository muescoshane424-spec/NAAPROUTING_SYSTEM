@extends('layouts.app')

@section('title','Activity Logs')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Activity Logs</h1>
<p class="text-gray-300">Audit entries for user and document actions.</p>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Action</th>
                <th>User</th>
                <th>Document</th>
                <th>IP Address</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td><span class="activity-badge {{ str_contains($log->action, 'created') ? 'create' : 'scan' }}">{{ $log->action }}</span></td>
                <td>{{ $log->user }}</td>
                <td>{{ $log->document ? $log->document->title : 'N/A' }}</td>
                <td>{{ $log->ip }}</td>
                <td>{{ $log->created_at->format('M j, Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $logs->links() }}
@endsection
