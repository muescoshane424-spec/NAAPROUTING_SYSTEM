@extends('layouts.app')

@section('title','Track Documents')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Document Tracking</h1>
<p class="text-gray-300">Track document location and routing history in real-time.</p>

<div class="table-responsive" style="margin-top: 20px;">
    <table class="table table-dark table-hover">
        <thead>
            <tr>
                <th>Document</th>
                <th>Current Location</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
            <tr>
                <td>
                    <strong>{{ $doc->title }}</strong><br>
                    <small class="text-muted">ID: #{{ str_pad($doc->id, 6, '0', STR_PAD_LEFT) }}</small>
                </td>
                <td>{{ $doc->currentOffice->name ?? 'Unknown' }}</td>
                <td>
                    @php
                        $status = strtolower($doc->status);
                        $badge = 'bg-warning text-dark';
                        if($status == 'completed') $badge = 'bg-success';
                        if(in_array($status, ['in_transit', 'uploaded', 'pending'])) $badge = 'bg-info text-white';
                    @endphp
                    <span class="badge {{ $badge }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
                </td>
                <td>
                    <div class="progress-bar-container">
                        @php
                            $progress = 25;
                            if($status == 'in_transit') $progress = 60;
                            if($status == 'completed') $progress = 100;
                        @endphp
                        <div class="progress-fill" style="width: {{ $progress }}%"></div>
                    </div>
                </td>
                <td>
                    <a href="{{ route('track.detail', $doc->id) }}" class="btn btn-sm btn-info text-white">
                        <i class="bi bi-geo-alt"></i> Track Status
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-4 text-gray-500">No documents available to track.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
.progress-bar-container { width: 100px; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, #00d7ff, #9C27B0); transition: width 0.5s ease; }
.table { background: rgba(30, 41, 59, 0.4); border-radius: 12px; overflow: hidden; border: none; }
.table thead { background: rgba(0, 215, 255, 0.1); color: #00d7ff; }
.table td, .table th { border-color: rgba(255,255,255,0.05); vertical-align: middle; }
</style>
@endsection