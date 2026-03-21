@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="main-header" style="justify-content:space-between;">
    <div>
        <h2 style="margin:0;">Documents</h2>
        <p style="color:var(--text); opacity:0.8; margin:4px 0;">Modern routing and state view for the NAAP document archive.</p>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
        <input type="search" placeholder="Search documents..." style="padding:8px 10px; border-radius:10px; border:1px solid var(--panel-border); background:rgba(15,23,42,.65); color:var(--text);" />
        <a href="{{ route('documents.create') }}" class="btn btn-primary">+ New Document</a>
    </div>
</div>

<div class="grid2" style="margin-bottom:14px;">
    <div class="card" style="padding:16px;">
        <div style="font-size:12px; color:#94a3b8;">Total Documents</div>
        <div style="font-size:2rem; font-weight:800; color:#38bdf8;">{{ number_format($stats['total'] ?? 1200) }}</div>
    </div>
    <div class="card" style="padding:16px;">
        <div style="font-size:12px; color:#94a3b8;">In Transit</div>
        <div style="font-size:2rem; font-weight:800; color:#a78bfa;">{{ number_format($stats['in_transit'] ?? 320) }}</div>
    </div>
    <div class="card" style="padding:16px;">
        <div style="font-size:12px; color:#94a3b8;">Pending</div>
        <div style="font-size:2rem; font-weight:800; color:#facc15;">{{ number_format($stats['pending'] ?? 85) }}</div>
    </div>
    <div class="card" style="padding:16px;">
        <div style="font-size:12px; color:#94a3b8;">Completed</div>
        <div style="font-size:2rem; font-weight:800; color:#22c55e;">{{ number_format($stats['completed'] ?? 790) }}</div>
    </div>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>From</th>
                <th>To</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach (range(1, 8) as $idx)
            <tr>
                <td>Document #00{{ $idx }}</td>
                <td><span class="badge-status badge-{{ ['info','success','warning','danger'][array_rand([0,1,2,3])] }}">{{ ['in_transit','completed','pending','error'][array_rand([0,1,2,3])] }}</span></td>
                <td>HR Office</td>
                <td>Finance</td>
                <td>{{ now()->subDays($idx)->format('M d, Y') }}</td>
                <td><a href="{{ route('routing.index') }}" style="color:#38bdf8;">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection