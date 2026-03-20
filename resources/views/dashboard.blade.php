@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">NAAP Document Routing Analytics</h1>
    <div class="dashboard-meta">
        <span>Last updated: {{ now()->format('M j, Y H:i') }}</span>
        <span>System Status: <span class="status-online">● Online</span></span>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon">📊</div>
        <div class="kpi-content">
            <h3>{{ number_format($stats['total']) }}</h3>
            <p>Total Documents</p>
            <span class="kpi-trend up">+12.5%</span>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon">🚀</div>
        <div class="kpi-content">
            <h3>{{ $stats['completion_rate'] }}%</h3>
            <p>Completion Rate</p>
            <span class="kpi-trend up">+5.2%</span>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon">⏱️</div>
        <div class="kpi-content">
            <h3>{{ $stats['avg_processing_days'] }}</h3>
            <p>Avg Processing Time</p>
            <span class="kpi-trend down">-2.1 days</span>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon">📈</div>
        <div class="kpi-content">
            <h3>{{ $kpis['total_processed_today'] }}</h3>
            <p>Processed Today</p>
            <span class="kpi-trend up">+8.7%</span>
        </div>
    </div>
</div>

<!-- Main Charts Grid -->
<div class="charts-grid">
    <!-- Monthly Trends -->
    <div class="chart-card large">
        <div class="chart-header">
            <h3>Document Trends (12 Months)</h3>
            <div class="chart-controls">
                <button class="chart-btn active">Monthly</button>
                <button class="chart-btn">Weekly</button>
                <button class="chart-btn">Daily</button>
            </div>
        </div>
        <canvas id="monthlyTrendsChart" height="300"></canvas>
    </div>

    <!-- Status Distribution -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Status Distribution</h3>
        </div>
        <canvas id="statusChart" height="250"></canvas>
    </div>

    <!-- Priority Breakdown -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Priority Distribution</h3>
        </div>
        <canvas id="priorityChart" height="250"></canvas>
    </div>

    <!-- Office Performance -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Office Performance</h3>
        </div>
        <canvas id="officeChart" height="250"></canvas>
    </div>

    <!-- Document Flow Timeline -->
    <div class="chart-card large">
        <div class="chart-header">
            <h3>Today's Document Flow</h3>
        </div>
        <canvas id="flowTimelineChart" height="200"></canvas>
    </div>

    <!-- Office Activity Heatmap -->
    <div class="chart-card">
        <div class="chart-header">
            <h3>Office Activity</h3>
        </div>
        <div class="heatmap-container">
            @foreach($docsByOffice as $office)
            <div class="heatmap-row">
                <span class="office-name">{{ $office['name'] }}</span>
                <div class="heatmap-bar">
                    <div class="heatmap-fill" style="width: {{ $office['current'] * 10 }}%"></div>
                    <span class="heatmap-value">{{ $office['current'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Data Tables Section -->
<div class="tables-grid">
    <div class="table-card">
        <div class="table-header">
            <h3>Recent Activity</h3>
            <a href="{{ route('activity.index') }}" class="view-all">View All</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Document</th>
                        <th>User</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activity as $act)
                    <tr>
                        <td><span class="activity-badge {{ $act->action == 'Document created' ? 'create' : 'scan' }}">{{ $act->action }}</span></td>
                        <td>{{ $act->document ? $act->document->title : 'N/A' }}</td>
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
            <h3>Top Performing Offices</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Office</th>
                        <th>Documents</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($officePerformance as $office)
                    <tr>
                        <td>{{ $office->name }}</td>
                        <td>{{ $office->doc_count }}</td>
                        <td>
                            <div class="performance-bar">
                                <div class="performance-fill" style="width: {{ min(100, $office->doc_count * 20) }}%"></div>
                            </div>
                        </td>
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
// Monthly Trends Chart
const monthlyCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyData = @json($monthlyTrends);
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Documents Created',
            data: monthlyData.map(d => d.count),
            borderColor: '#00d7ff',
            backgroundColor: 'rgba(0, 215, 255, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#00d7ff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
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

// Flow Timeline Chart
const flowCtx = document.getElementById('flowTimelineChart').getContext('2d');
const flowData = @json($flowTimeline);
new Chart(flowCtx, {
    type: 'area',
    data: {
        labels: flowData.map(d => d.hour),
        datasets: [{
            label: 'Documents Processed',
            data: flowData.map(d => d.processed),
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.3)',
            fill: true,
            tension: 0.4
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
</script>
@endsection
