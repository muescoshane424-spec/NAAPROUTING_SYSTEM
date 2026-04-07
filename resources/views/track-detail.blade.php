@extends('layouts.app')

@section('title','Document Tracking Timeline')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <a href="{{ route('track.index') }}" class="text-cyan-400 mb-4 d-block text-decoration-none">
        <i class="bi bi-arrow-left"></i> Back to Tracking
    </a>
    
    <h1 class="text-2xl font-bold mb-6 text-cyan-400">{{ $document->title }}</h1>

    <div class="chart-card shadow-lg" style="margin-bottom: 30px; background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(0, 215, 255, 0.2); border-radius: 15px;">
        <div class="chart-header" style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 class="text-lg font-semibold">Document Details</h3>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div>
                    <p style="color: #9bc4f9; font-size: 0.8rem; margin-bottom: 4px;">Document ID</p>
                    <p class="font-mono">{{ $document->id }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9; font-size: 0.8rem; margin-bottom: 4px;">Status</p>
                    <span class="badge {{ $document->status == 'completed' ? 'bg-success' : ($document->status == 'in_transit' ? 'bg-info' : 'bg-warning text-dark') }}">
                        {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                    </span>
                </div>
                <div>
                    <p style="color: #9bc4f9; font-size: 0.8rem; margin-bottom: 4px;">Origin</p>
                    <p>{{ $document->originOffice?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9; font-size: 0.8rem; margin-bottom: 4px;">Destination</p>
                    <p>{{ $document->destinationOffice?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9; font-size: 0.8rem; margin-bottom: 4px;">Current Location</p>
                    <p class="text-cyan-300 fw-bold">{{ $document->currentOffice?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9; font-size: 0.8rem; margin-bottom: 4px;">Created Date</p>
                    <p>{{ \Carbon\Carbon::parse($document->created_at)->format('M j, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <h2 style="color: #00d7ff; margin-bottom: 25px; font-weight: 600;">Routing Timeline</h2>

    <div class="timeline-container" style="position: relative; padding-left: 20px;">
        <div style="position: absolute; left: 9px; top: 10px; bottom: 10px; width: 2px; background: rgba(0, 215, 255, 0.2);"></div>

        <div style="display: flex; margin-bottom: 35px; position: relative; z-index: 1;">
            <div style="flex: 0 0 20px;">
                <div style="width: 20px; height: 20px; background: #4CAF50; border-radius: 50%; border: 4px solid rgba(76, 175, 80, 0.3);"></div>
            </div>
            <div style="flex: 1; padding-left: 25px;">
                <p style="color: #00d7ff; font-weight: 600; margin-bottom: 2px;">Document Created</p>
                <p style="color: #9bc4f9; margin-bottom: 4px;">{{ $document->originOffice?->name }}</p>
                <p style="color: #888; font-size: 0.85rem;">{{ \Carbon\Carbon::parse($document->created_at)->format('M j, Y H:i') }}</p>
            </div>
        </div>

        @foreach($document->routings as $routing)
        <div style="display: flex; margin-bottom: 35px; position: relative; z-index: 1;">
            <div style="flex: 0 0 20px;">
                <div style="width: 20px; height: 20px; background: #FF9800; border-radius: 50%; border: 4px solid rgba(255, 152, 0, 0.3);"></div>
            </div>
            <div style="flex: 1; padding-left: 25px;">
                <p style="color: #00d7ff; font-weight: 600; margin-bottom: 2px;">{{ ucfirst($routing->status) }}</p>
                <p style="color: #9bc4f9; margin-bottom: 4px;">{{ $routing->fromOffice?->name }} <i class="bi bi-arrow-right mx-1"></i> {{ $routing->toOffice?->name }}</p>
                <p style="color: #888; font-size: 0.85rem; margin-bottom: 8px;">{{ \Carbon\Carbon::parse($routing->created_at)->format('M j, Y H:i') }}</p>
                @if($routing->notes)
                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px; border-left: 3px solid #FF9800;">
                    <p style="color: #ccc; font-size: 0.9rem; margin: 0; font-style: italic;">"{{ $routing->notes }}"</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach

        @if($document->status == 'completed')
        <div style="display: flex; position: relative; z-index: 1;">
            <div style="flex: 0 0 20px;">
                <div style="width: 20px; height: 20px; background: #4CAF50; border-radius: 50%; border: 4px solid rgba(76, 175, 80, 0.3);"></div>
            </div>
            <div style="flex: 1; padding-left: 25px;">
                <p style="color: #00d7ff; font-weight: 600; margin-bottom: 2px;">Completed</p>
                <p style="color: #9bc4f9; margin-bottom: 4px;">Arrived at {{ $document->destinationOffice?->name }}</p>
                <p style="color: #888; font-size: 0.85rem;">{{ \Carbon\Carbon::parse($document->updated_at)->format('M j, Y H:i') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection