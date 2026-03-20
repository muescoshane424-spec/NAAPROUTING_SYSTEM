@extends('layouts.app')

@section('title','Document Tracking Timeline')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <a href="{{ route('track.index') }}" class="text-cyan-400 mb-4 d-block">← Back to Tracking</a>
    
    <h1 class="text-2xl font-bold mb-6 text-cyan-400">{{ $document->title }}</h1>

    <div class="chart-card" style="margin-bottom: 30px;">
        <div class="chart-header">
            <h3>Document Details</h3>
        </div>
        <div style="padding: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p style="color: #9bc4f9;">Document ID</p>
                    <p>{{ $document->id }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9;">Status</p>
                    <p><span class="badge {{ $document->status == 'completed' ? 'success' : ($document->status == 'in_transit' ? 'info' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $document->status)) }}</span></p>
                </div>
                <div>
                    <p style="color: #9bc4f9;">From</p>
                    <p>{{ $document->originOffice?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9;">To</p>
                    <p>{{ $document->destinationOffice?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9;">Current Location</p>
                    <p>{{ $document->currentOffice?->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p style="color: #9bc4f9;">Created</p>
                    <p>{{ $document->created_at->format('M j, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <h2 style="color: #00d7ff; margin-bottom: 20px;">Routing Timeline</h2>

    <div class="timeline" style="position: relative; padding: 20px 0;">
        <!-- Start point -->
        <div style="display: flex; margin-bottom: 30px;">
            <div style="flex: 0 0 40px; display: flex; justify-content: center;">
                <div style="width: 20px; height: 20px; background: #4CAF50; border-radius: 50%; border: 4px solid rgba(76, 175, 80, 0.3);"></div>
            </div>
            <div style="flex: 1; padding-left: 20px;">
                <p style="color: #00d7ff; font-weight: 600;">Created</p>
                <p style="color: #9bc4f9;">{{ $document->originOffice?->name ?? 'Unknown' }} - {{ $document->created_at->format('M j, Y H:i') }}</p>
                <p style="color: #ccc; font-size: 0.9rem;">Document initiated</p>
            </div>
        </div>

        <!-- Routing history -->
        @foreach($document->routings as $routing)
        <div style="display: flex; margin-bottom: 30px; position: relative;">
            <div style="flex: 0 0 40px; display: flex; justify-content: center; position: relative;">
                <div style="position: absolute; width: 2px; height: 100%; background: rgba(0, 215, 255, 0.3); top: -15px; left: 50%; transform: translateX(-50%);"></div>
                <div style="width: 20px; height: 20px; background: #FF9800; border-radius: 50%; border: 4px solid rgba(255, 152, 0, 0.3); z-index: 1;"></div>
            </div>
            <div style="flex: 1; padding-left: 20px;">
                <p style="color: #00d7ff; font-weight: 600;">{{ ucfirst($routing->status) }}</p>
                <p style="color: #9bc4f9;">{{ $routing->fromOffice?->name ?? 'Unknown' }} → {{ $routing->toOffice?->name ?? 'Unknown' }}</p>
                <p style="color: #ccc; font-size: 0.9rem;">{{ $routing->created_at->format('M j, Y H:i') }}</p>
                @if($routing->notes)
                <p style="color: #aaa; font-size: 0.9rem; margin-top: 5px;"><em>{{ $routing->notes }}</em></p>
                @endif
            </div>
        </div>
        @endforeach

        <!-- End point if completed -->
        @if($document->status == 'completed')
        <div style="display: flex;">
            <div style="flex: 0 0 40px; display: flex; justify-content: center;">
                <div style="width: 20px; height: 20px; background: #4CAF50; border-radius: 50%; border: 4px solid rgba(76, 175, 80, 0.3);"></div>
            </div>
            <div style="flex: 1; padding-left: 20px;">
                <p style="color: #00d7ff; font-weight: 600;">Completed</p>
                <p style="color: #9bc4f9;">{{ $document->destinationOffice?->name ?? 'Unknown' }} - {{ $document->updated_at->format('M j, Y H:i') }}</p>
                <p style="color: #ccc; font-size: 0.9rem;">Document delivery complete</p>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
