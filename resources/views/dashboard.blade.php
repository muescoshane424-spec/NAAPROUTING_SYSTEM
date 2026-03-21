@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">NAAP Enterprise Dashboard</h1>
    <div class="dashboard-meta">
        <span>Updated: {{ now()->format('M j, Y H:i') }}</span>
        <span>System Health: <span class="status-online">● Online</span></span>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon">📚</div>
        <div class="kpi-content">
            <h3>{{ number_format($stats['total'] ?? 0) }}</h3>
            <p>Total Documents</p>
            <span class="kpi-trend up">{{ number_format(($stats['total'] ?? 0) * 0.03, 1) }}% ▲</span>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon">🚚</div>
        <div class="kpi-content">
            <h3>{{ number_format($stats['in_transit'] ?? 0) }}</h3>
            <p>In Transit</p>
            <span class="kpi-trend warning">{{ number_format(($stats['in_transit'] ?? 0) / max(1, ($stats['total'] ?? 1)) * 100,1) }}%</span>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon">⏱️</div>
        <div class="kpi-content">
            <h3>{{ $stats['avg_processing_days'] ?? 0 }} days</h3>
            <p>Avg Processing</p>
            <span class="kpi-trend down">-0.7 days</span>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon">✅</div>
        <div class="kpi-content">
            <h3>{{ number_format($stats['completed'] ?? 0) }}</h3>
            <p>Completed</p>
            <span class="kpi-trend up">{{ $stats['completion_rate'] ?? 0 }}%</span>
        </div>
    </div>
</div>

<div class="chart-card" style="margin-bottom: 20px;">
    <div class="chart-header">
        <h3>Quick Actions</h3>
        <div class="chart-controls">
            <a href="{{ route('documents.create') }}" class="btn btn-primary" style="font-size:0.85rem; padding:7px 11px;">Route Document</a>
            <a href="{{ route('qr.scan') }}" class="btn btn-primary" style="font-size:0.85rem; padding:7px 11px;">Scan QR</a>
            <a href="{{ route('users.index') }}" class="btn btn-primary" style="font-size:0.85rem; padding:7px 11px;">Add User</a>
        </div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-header"><h3>Document Activity</h3></div>
        <canvas id="monthlyTrendsChart" height="260"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-header"><h3>Office Performance</h3></div>
        <canvas id="officeChart" height="260"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-header"><h3>Status Overview</h3></div>
        <canvas id="statusChart" height="260"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-header"><h3>System Health</h3></div>
        <canvas id="systemHealthChart" height="260"></canvas>
    </div>
</div>

<div class="tables-grid" style="margin-top:20px;">
    <div class="table-card">
        <div class="table-header">
            <h3>Live Activity Feed</h3>
            <span style="color:#a5b4fc; font-size:0.85rem;">Updating every 30s</span>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Event</th><th>Document</th><th>User</th><th>Time</th></tr>
                </thead>
                <tbody>
                    @foreach($activity->take(7) as $act)
                    <tr>
                        <td>{{ $act->action }}</td>
                        <td>{{ $act->document?->title ?? 'N/A' }}</td>
                        <td>{{ $act->user }}</td>
                        <td>{{ $act->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="table-card">
        <div class="table-header">
            <h3>Top Office Routing</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Office</th><th>Docs</th><th>Trend</th></tr></thead>
                <tbody>
                    @foreach($officePerformance as $office)
                    <tr>
                        <td>{{ $office->name }}</td>
                        <td>{{ $office->doc_count }}</td>
                        <td><span class="badge {{ $office->doc_count >= 10 ? 'success' : 'warning' }}">{{ $office->doc_count >= 10 ? 'High' : 'Moderate'}}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Trends Chart (Monthly / Weekly / Daily)
const trendCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const trendData = {
    monthly: @json($monthlyTrends),
    weekly: @json($weeklyTrends),
    daily: @json($dailyTrends)
};

let activeTrend = 'monthly';

function getTrendConfig(scope) {
    const items = trendData[scope] || [];
    return {
        labels: items.map(d => d.month || d.label),
        datasets: [{
            label: 'Documents Created',
            data: items.map(d => d.count),
            borderColor: '#00d7ff',
            backgroundColor: 'rgba(0, 215, 255, 0.18)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#00d7ff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    };
}

let trendChart = new Chart(trendCtx, {
    type: 'line',
    data: getTrendConfig(activeTrend),
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: context => `${context.dataset.label}: ${context.parsed.y}`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.1)' }
            },
            x: {
                grid: { color: 'rgba(255,255,255,0.1)' }
            }
        }
    }
});

function updateTrendData(scope) {
    if (!trendData[scope]) return;
    activeTrend = scope;
    const cfg = getTrendConfig(scope);
    trendChart.data.labels = cfg.labels;
    trendChart.data.datasets = cfg.datasets;
    trendChart.update();
}

document.querySelectorAll('.chart-btn').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.chart-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        updateTrendData(button.textContent.trim().toLowerCase());
    });
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusData = @json($statusDistribution);
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'In Transit', 'Pending'],
        datasets: [{
            data: [statusData.completed, statusData.in_transit, statusData.pending],
            backgroundColor: ['#4CAF50', '#FF9800', '#F44336'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Priority Chart
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
const priorityData = @json($priorityData);
new Chart(priorityCtx, {
    type: 'bar',
    data: {
        labels: Object.keys(priorityData),
        datasets: [{
            label: 'Count',
            data: Object.values(priorityData),
            backgroundColor: ['#2196F3', '#FFC107', '#E91E63'],
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Office Performance Chart
const officeCtx = document.getElementById('officeChart').getContext('2d');
const officeData = @json($docsByOffice);
new Chart(officeCtx, {
    type: 'radar',
    data: {
        labels: officeData.map(d => d.name),
        datasets: [{
            label: 'Current Documents',
            data: officeData.map(d => d.current),
            borderColor: '#9C27B0',
            backgroundColor: 'rgba(156, 39, 176, 0.2)',
            pointBackgroundColor: '#9C27B0'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } }
    }
});

// System Health Chart
const systemHealthCtx = document.getElementById('systemHealthChart').getContext('2d');
const systemHealthData = @json($systemHealth);
new Chart(systemHealthCtx, {
    type: 'bar',
    data: {
        labels: systemHealthData.map(d => d.label),
        datasets: [{
            label: 'Health %',
            data: systemHealthData.map(d => d.value),
            backgroundColor: ['#43A047', '#1976D2', '#6A1B9A', '#FDD835'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: { stepSize: 20 }
            }
        }
    }
});

// Flow Timeline Chart
const flowCtx = document.getElementById('flowTimelineChart').getContext('2d');
const flowData = @json($flowTimeline);
new Chart(flowCtx, {
    type: 'line',
    data: {
        labels: flowData.map(d => d.hour),
        datasets: [{
            label: 'Documents Processed',
            data: flowData.map(d => d.processed),
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.35)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#4CAF50'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true },
            x: { grid: { color: 'rgba(255,255,255,0.1)' } }
        }
    }
});
</script>
@endsection
