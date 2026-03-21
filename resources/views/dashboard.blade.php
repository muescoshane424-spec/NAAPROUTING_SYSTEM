@extends('layouts.app')

@section('head')
<script src="{{ asset('js/chart.js') }}"></script>
<style>
    :root {
        --bg-dark: #0b0e14;
        --card-bg: #161b22;
        --border: #30363d;
        --text-gray: #8b949e;
        --blue: #58a6ff;
        --purple: #bc8cff;
    }

    .dashboard-container { background: var(--bg-dark); color: white; padding: 20px; min-height: 100vh; }
    
    /* Grid for the 4 Top Cards */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    .kpi-card { background: var(--card-bg); border: 1px solid var(--border); padding: 25px; border-radius: 12px; position: relative; }
    .kpi-label { color: var(--text-gray); font-size: 13px; font-weight: 600; }
    .kpi-value { font-size: 32px; font-weight: 800; margin-top: 10px; }
    .trend-tag { position: absolute; top: 25px; right: 25px; font-size: 11px; padding: 4px 8px; border-radius: 20px; background: rgba(63, 185, 80, 0.1); color: #3fb950; }

    /* Grid for Charts */
    .chart-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
    .chart-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; }
    
    /* FIX: Force height so charts don't disappear */
    .canvas-wrapper { position: relative; height: 300px; width: 100%; margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <div style="margin-bottom: 25px;">
        <h1 style="margin:0;">Dashboard</h1>
        <p style="color: var(--text-gray);">Real-time Insights • {{ now()->format('D, M d, Y') }}</p>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <span class="kpi-label">Total Documents</span>
            <div class="kpi-value">{{ number_format($stats['total']) }}</div>
            <span class="trend-tag">+12%</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">In Transit</span>
            <div class="kpi-value">{{ $stats['in_transit'] }}</div>
            <span class="trend-tag">+8%</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Completed</span>
            <div class="kpi-value">{{ $stats['completed'] }}</div>
            <span class="trend-tag">+15%</span>
        </div>
        <div class="kpi-card">
            <span class="kpi-label">Pending Approvals</span>
            <div class="kpi-value" style="color: #f85149;">{{ $stats['pending'] }}</div>
            <span class="trend-tag" style="color: #f85149; background: rgba(248,81,73,0.1);">-5%</span>
        </div>
    </div>

    <div class="chart-grid">
        <div class="chart-card">
            <h3 style="margin:0;">Document Flow</h3>
            <div class="canvas-wrapper"><canvas id="flowChart"></canvas></div>
        </div>
        <div class="chart-card">
            <h3 style="margin:0;">Office Activity</h3>
            <div class="canvas-wrapper"><canvas id="officeChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Flow Chart
    new Chart(document.getElementById('flowChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($weeklyTrends, 'label')) !!},
            datasets: [{
                data: {!! json_encode(array_column($weeklyTrends, 'count')) !!},
                borderColor: '#58a6ff',
                backgroundColor: 'rgba(88, 166, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#30363d' }, ticks: { color: '#8b949e' } },
                x: { grid: { display: false }, ticks: { color: '#8b949e' } }
            }
        }
    });

    // Office Activity
    new Chart(document.getElementById('officeChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($officePerformance->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($officePerformance->pluck('doc_count')) !!},
                backgroundColor: '#bc8cff',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', // Matches horizontal bars
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#30363d' }, ticks: { color: '#8b949e' } },
                y: { grid: { display: false }, ticks: { color: '#8b949e' } }
            }
        }
    });
});
</script>
@endsection