@extends('layouts.app')

@section('title','Dashboard')

@section('styles')
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #0f172a;
        color: #e0e0e0;
    }

    h1 {
        font-size: 2.25rem;
        font-weight: bold;
        color: #06b6d4;
        animation: pulse 2s infinite;
        margin-bottom: 1.5rem;
    }

    .grid {
        display: grid;
        gap: 1.5rem;
    }

    .grid-cols-4 {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transition: transform 0.5s ease-in-out;
    }

    .card:hover {
        transform: scale(1.05);
    }

    .card h3 {
        color: #d1d5db;
        margin-bottom: 0.5rem;
    }

    .card p {
        font-size: 2rem;
        font-weight: bold;
    }

    .cyan { color: #06b6d4; }
    .purple { color: #a78bfa; }
    .green { color: #22c55e; }
    .yellow { color: #facc15; }

    .animate-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .list-item {
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: background 0.3s;
    }

    .list-item:hover {
        background: rgba(255,255,255,0.2);
    }

    .routing-map {
        height: 16rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-weight: bold;
        animation: pulse 2s infinite;
        border-radius: 1rem;
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.1);
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<h1>Dashboard</h1>

<div class="grid grid-cols-4 mb-10">
    @foreach ([
        ['title'=>'Total Documents','count'=>128,'color'=>'cyan'],
        ['title'=>'In Transit','count'=>54,'color'=>'purple'],
        ['title'=>'Completed','count'=>72,'color'=>'green'],
        ['title'=>'Pending Approvals','count'=>15,'color'=>'yellow'],
    ] as $card)
    <div class="card">
        <h3>{{ $card['title'] }}</h3>
        <p class="{{ $card['color'] }}">{{ $card['count'] }}</p>
    </div>
    @endforeach
</div>

<div class="routing-map">
    Document Flow Map / Routing Graph (motion placeholder)
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
    <div class="card">
        <h3>Recent Documents</h3>
        <ul>
            <li class="list-item">Document A - In Transit</li>
            <li class="list-item">Document B - Completed</li>
            <li class="list-item">Document C - Pending</li>
        </ul>
    </div>
    <div class="card">
        <h3>Office Stats</h3>
        <ul>
            <li>Registrar: 12 Docs</li>
            <li>Accounting: 8 Docs</li>
            <li>Dean's Office: 5 Docs</li>
        </ul>
    </div>
    <div class="card">
        <h3>QR Scans Today</h3>
        <p class="cyan" style="font-size:2rem; font-weight:bold;">23</p>
    </div>
</div>
@endsection