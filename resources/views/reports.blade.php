@extends('layouts.app')
@section('title', 'Reports & Analytics')

@section('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    :root {
        --glass-bg: rgba(30, 41, 59, 0.4);
        --glass-border: rgba(255, 255, 255, 0.05);
        --accent-cyan: #22d3ee;
        --accent-purple: #a855f7;
    }
    .glass-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 1.5rem;
        backdrop-filter: blur(12px);
    }
    .flow-card { background: linear-gradient(135deg, rgba(30, 41, 59, 0.6), rgba(15, 23, 42, 0.8)); }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: white; }
    .stat-label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .btn-export {
        background: rgba(34, 211, 238, 0.1);
        border: 1px solid var(--accent-cyan);
        color: var(--accent-cyan);
        transition: 0.3s;
    }
    .btn-export:hover { background: var(--accent-cyan); color: #0f172a; }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white mb-0">Reports & Analytics</h2>
        <a href="{{ route('reports.export') }}" class="btn btn-export rounded-pill px-4">
            <i class="bi bi-download me-2"></i>Export CSV Data
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="glass-card text-center">
                <div class="stat-label">Total Documents</div>
                <div class="stat-value">{{ number_format($summary['total_processed']) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center">
                <div class="stat-label">Avg Processing Time</div>
                <div class="stat-value" style="color: var(--accent-cyan);">{{ $summary['avg_time'] }}h</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center">
                <div class="stat-label">Most Active Office</div>
                <div class="stat-value" style="color: var(--accent-purple);">{{ $summary['most_active'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card text-center">
                <div class="stat-label">System QR Scans</div>
                <div class="stat-value">{{ number_format($summary['qr_scans']) }}</div>
            </div>
        </div>
    </div>

    <div class="glass-card flow-card mb-4">
        <h5 class="text-white mb-3"><i class="bi bi-graph-up me-2 text-info"></i>Weekly Volume Flow</h5>
        <div style="height:220px;"><canvas id="flowChart"></canvas></div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="glass-card h-100">
                <h6 class="text-white mb-3">Documents per Office</h6>
                <div style="height:250px;"><canvas id="reportBar"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card h-100">
                <h6 class="text-white mb-3">Daily Scan Activity</h6>
                <div style="height:250px;"><canvas id="reportLine"></canvas></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.onload = function () {
    Chart.defaults.color = '#94a3b8';

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true },
            x: { grid: { display: false } }
        }
    };

    // 1. Flow Chart
    new Chart(document.getElementById('flowChart'), {
        type: 'line',
        data: {
            labels: @json($flowLabels),
            datasets: [{
                data: @json($flowData),
                borderColor: '#22d3ee',
                backgroundColor: 'rgba(34,211,238,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: commonOptions
    });

    // 2. Bar Chart
    new Chart(document.getElementById('reportBar'), {
        type: 'bar',
        data: {
            labels: @json($officeNames),
            datasets: [{
                data: @json($processingTimes),
                backgroundColor: '#22d3ee',
                borderRadius: 8
            }]
        },
        options: commonOptions
    });

    // 3. Line Chart
    new Chart(document.getElementById('reportLine'), {
        type: 'line',
        data: {
            labels: @json($days),
            datasets: [{
                data: @json($scanCounts),
                borderColor: '#a855f7',
                tension: 0.4,
                pointRadius: 5
            }]
        },
        options: commonOptions
    });
};
</script>
@endsection