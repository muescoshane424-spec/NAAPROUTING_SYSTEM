@extends('layouts.app')

@section('title', 'System Analytics - Admin')

@section('content')
<style>
    :root {
        --bg-dark: #0f172a;
        --card-bg: rgba(30, 41, 59, 0.45);
        --border: rgba(255, 255, 255, 0.08);
        --text-dim: #94a3b8;
        --accent-blue: #22c1ff;
        --accent-purple: #a855f7;
        --accent-green: #22c55e;
        --accent-orange: #f59e0b;
    }
    .dashboard-container { padding: 1.5rem; max-width: 1400px; margin: 0 auto; }
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .kpi-card { 
        background: var(--card-bg); 
        border: 1px solid var(--border); 
        border-radius: 1rem; 
        padding: 1.5rem; 
        backdrop-filter: blur(10px);
        transition: transform 0.2s;
    }
    .kpi-card:hover { transform: translateY(-5px); }
    .kpi-card h3 { font-size: 2rem; font-weight: 800; margin: 0.5rem 0; }
    .label { font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
    .charts-main-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem; }
    .chart-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem; }
    .canvas-container { position: relative; height: 260px; width: 100%; }
    .satisfaction-wrapper { position: relative; height: 250px; display: flex; align-items: center; justify-content: center; }
</style>

<div class="dashboard-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0 text-white">System Analytics</h1>
            <p class="text-muted small">Real-time Admin tracking for {{ now()->format('F d, Y') }}</p>
        </div>
        
        @php 
            $highPriorityCount = \App\Models\Document::where('priority', 'High')->where('status', '!=', 'Completed')->count(); 
        @endphp
        
        @if($highPriorityCount > 0)
            <div class="badge bg-danger p-2 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $highPriorityCount }} High Priority Tasks
            </div>
        @endif
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="label">Total System Docs</div>
            <h3 style="color: var(--accent-blue)">{{ number_format($stats['total'] ?? 0) }}</h3>
        </div>
        <div class="kpi-card">
            <div class="label">Actively Routing</div>
            <h3 style="color: var(--accent-purple)">{{ $stats['in_transit'] ?? 0 }}</h3>
        </div>
        <div class="kpi-card">
            <div class="label">Archive Completed</div>
            <h3 style="color: var(--accent-green)">{{ $stats['completed'] ?? 0 }}</h3>
        </div>
        <div class="kpi-card">
            <div class="label">Total System Users</div>
            <h3 style="color: var(--accent-orange)">{{ $stats['total_users'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="charts-main-grid">
        <div class="chart-card">
            <h5 class="fw-bold mb-4 text-white">7-Day Activity Flow</h5>
            <div class="canvas-container">
                <canvas id="flowChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h5 class="fw-bold mb-4 text-white">System Health Score</h5>
            <div class="satisfaction-wrapper">
                <canvas id="uxChart"></canvas>
                <div style="position: absolute; text-align: center;">
                    <h2 class="mb-0 fw-bold" style="color: var(--accent-green)">92%</h2>
                    <small class="text-muted">OPERATIONAL</small>
                </div>
            </div>
        </div>

        <div class="chart-card">
            <h5 class="fw-bold mb-4 text-white">Top Office Load (Current)</h5>
            <div class="canvas-container">
                <canvas id="officeChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <h5 class="fw-bold mb-4 text-white">Recent System Activity</h5>
            <div class="activity-feed" style="max-height: 260px; overflow-y: auto;">
                @forelse($recentLogs ?? [] as $log)
                    <div class="d-flex gap-3 mb-3 border-bottom border-secondary pb-2">
                        <div class="text-info small fw-bold" style="min-width: 80px;">{{ $log->created_at->diffForHumans() }}</div>
                        <div class="text-white small">
                            <strong>{{ $log->user_name ?? 'System' }}</strong> {{ $log->action }}
                            <br><small class="text-muted">Document: {{ $log->document->title ?? 'N/A' }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center mt-5">No activity logs found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', sans-serif";

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false }, beginAtZero: true },
            x: { grid: { display: false }, border: { display: false } }
        }
    };

    // Flow Chart
    new Chart(document.getElementById('flowChart'), {
        type: 'line',
        data: {
            labels: @json($days ?? []),
            datasets: [{
                data: @json($flowData ?? []),
                borderColor: '#22c1ff',
                backgroundColor: 'rgba(34, 193, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: baseOptions
    });

    // UX Donut
    new Chart(document.getElementById('uxChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [92, 8],
                backgroundColor: ['#22c55e', 'rgba(255,255,255,0.05)'],
                borderWidth: 0,
                cutout: '85%'
            }]
        },
        options: { ...baseOptions, scales: { x: { display: false }, y: { display: false } } }
    });

    // Workload Chart
    new Chart(document.getElementById('officeChart'), {
        type: 'bar',
        data: {
            labels: @json($offices->pluck('name') ?? []),
            datasets: [{
                data: @json($offices->pluck('documents_count') ?? []),
                backgroundColor: '#a855f7',
                borderRadius: 6
            }]
        },
        options: baseOptions
    });
</script>
@endsection