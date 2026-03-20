@extends('layouts.app')

@section('title','Track Documents')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-cyan-400">Document Tracking</h1>
<p class="text-gray-300">Track document location and routing history in real-time.</p>

<div class="table-responsive" style="margin-top: 20px;">
    <table>
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
            @foreach($documents as $doc)
            <tr>
                <td><strong>{{ $doc->title }}</strong></td>
                <td>{{ $doc->currentOffice?->name ?? 'Unknown' }}</td>
                <td><span class="badge {{ $doc->status == 'completed' ? 'success' : ($doc->status == 'in_transit' ? 'info' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $doc->status)) }}</span></td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $doc->status == 'completed' ? 100 : ($doc->status == 'in_transit' ? 50 : 25) }}%"></div>
                    </div>
                </td>
                <td>
                    <a href="{{ route('track.detail', $doc->id) }}" class="text-cyan-400 hover:underline">View Timeline</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $documents->links() }}

<style>
.progress-bar { width: 100px; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, #00d7ff, #9C27B0); }
</style>
@endsection
