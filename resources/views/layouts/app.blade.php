<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'NAAP Admin')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #060b1f;
        --side: rgba(5, 10, 34, 0.95);
        --panel: rgba(10, 17, 45, 0.75);
        --cyan: #00e4ff;
        --blue: #009dff;
        --purple: #b350ff;
        --text: #edf5ff;
    }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; font-family:'Inter', 'Poppins', sans-serif; color:var(--text); background: linear-gradient(120deg, #03122b 0%, #06153b 50%, #040a22 100%); }
    .app-shell { display:grid; grid-template-columns: 280px 1fr; min-height:100vh; }
    .sidebar { background: var(--side); border-right: 1px solid rgba(9, 123, 255, .16); backdrop-filter: blur(6px); padding: 20px; display:flex; flex-direction:column; }
    .sidebar .brand { display:flex; align-items:center; gap:10px; margin-bottom:16px; }
    .sidebar .brand .logo { width:34px; height:34px; border-radius:10px; background:linear-gradient(135deg, var(--cyan), var(--purple)); display:grid; place-items:center; font-weight:700; color:#031029; }
    .sidebar .brand h1 { margin:0; font-size:1.4rem; }
    .sidebar nav { display:flex; flex-direction:column; gap:7px; flex:1; overflow-y:auto; }
    .sidebar a { color:#b7d1ff; text-decoration:none; padding:10px 11px; border-radius:10px; border:1px solid rgba(120,200,255,0.15); background:rgba(10,20,45,0.45); transition:.2s; }
    .sidebar a:hover, .sidebar a.active { background:rgba(11,94,255,0.35); border-color:rgba(0,200,255,0.45); color:#ffffff; }
    .sidebar .bottom { margin-top:16px; color:#96b6dc; font-size:0.82rem; }

    .main { padding:18px; overflow:auto; }
    .main-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
    .main-header h2 { margin:0; font-size:1.4rem; }
    .card { background:var(--panel); border:1px solid rgba(93, 177, 255, 0.2); border-radius:14px; box-shadow:0 12px 34px rgba(0,90,170,0.2); color:#e9f4ff; }
    .card-inner { padding: 13px; }

    .grid2 { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:12px; }
    .table-wrapper { overflow:auto; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px 8px; border-bottom:1px solid rgba(119, 154, 217, 0.19); }
    th { text-transform:uppercase; color:#9bc4f9; font-size:.8rem; }
    td { font-size:.9rem; }
    .badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:0.72rem; font-weight:600; }
    .success { background:rgba(59,226,160,0.19); color:#adecca; }
    .info { background:rgba(132,112,255,0.18); color:#d8caff; }
    .warning { background:rgba(255,195,90,0.2); color:#ffd58b; }

    .modal { position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); }
    .modal-content { background:var(--panel); margin:15% auto; padding:20px; border:1px solid rgba(93, 177, 255, 0.2); border-radius:14px; width:90%; max-width:500px; }
    .close { color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer; }
    .close:hover { color:#fff; }
    .hidden { display:none; }
    .form-group { margin-bottom:15px; }
    .form-group label { display:block; margin-bottom:5px; color:#9bc4f9; }
    .form-group input, .form-group select, .form-group textarea { width:100%; padding:8px; background:rgba(10,20,45,0.45); border:1px solid rgba(120,200,255,0.15); border-radius:6px; color:#fff; }
    .btn { padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600; }
    .btn-primary { background:var(--cyan); color:#031029; }

    /* Professional Dashboard Styles */
    .dashboard-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.1); }
    .dashboard-title { font-size:2rem; font-weight:700; color:#00d7ff; margin:0; }
    .dashboard-meta { display:flex; gap:20px; font-size:0.9rem; color:#9bc4f9; }
    .status-online { color:#4CAF50; font-weight:600; }

    .kpi-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:16px; margin-bottom:32px; }
    .kpi-card { background:var(--panel); border:1px solid rgba(93, 177, 255, 0.2); border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; transition:.2s; }
    .kpi-card:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(0,90,170,0.3); }
    .kpi-icon { font-size:2.5rem; }
    .kpi-content h3 { margin:0; font-size:2rem; font-weight:700; color:#fff; }
    .kpi-content p { margin:4px 0 8px 0; color:#9bc4f9; font-size:0.9rem; }
    .kpi-trend { font-size:0.8rem; font-weight:600; padding:2px 6px; border-radius:12px; }
    .kpi-trend.up { background:rgba(76,175,80,0.2); color:#81C784; }
    .kpi-trend.down { background:rgba(244,67,54,0.2); color:#EF5350; }

    .charts-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap:20px; margin-bottom:32px; }
    .chart-card { background:var(--panel); border:1px solid rgba(93, 177, 255, 0.2); border-radius:12px; padding:20px; }
    .chart-card.large { grid-column: 1 / -1; }
    .chart-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .chart-header h3 { margin:0; color:#fff; }
    .chart-controls { display:flex; gap:8px; }
    .chart-btn { padding:4px 12px; border:1px solid rgba(255,255,255,0.2); background:none; color:#9bc4f9; border-radius:6px; cursor:pointer; font-size:0.8rem; }
    .chart-btn.active { background:#00d7ff; color:#031029; border-color:#00d7ff; }

    .heatmap-container { display:flex; flex-direction:column; gap:12px; }
    .heatmap-row { display:flex; align-items:center; gap:12px; }
    .office-name { flex:1; color:#9bc4f9; font-size:0.9rem; }
    .heatmap-bar { flex:3; height:24px; background:rgba(255,255,255,0.1); border-radius:12px; position:relative; overflow:hidden; }
    .heatmap-fill { height:100%; background:linear-gradient(90deg, #00d7ff, #9C27B0); transition:.3s; }
    .heatmap-value { position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:0.8rem; font-weight:600; color:#fff; }

    .tables-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap:20px; }
    .table-card { background:var(--panel); border:1px solid rgba(93, 177, 255, 0.2); border-radius:12px; overflow:hidden; }
    .table-header { display:flex; justify-content:space-between; align-items:center; padding:20px; border-bottom:1px solid rgba(255,255,255,0.1); }
    .table-header h3 { margin:0; color:#fff; }
    .view-all { color:#00d7ff; text-decoration:none; font-size:0.9rem; font-weight:500; }
    .view-all:hover { text-decoration:underline; }
    .table-responsive { overflow-x:auto; }
    .table-responsive table { width:100%; border-collapse:collapse; }
    .table-responsive th, .table-responsive td { padding:12px 20px; text-align:left; border-bottom:1px solid rgba(255,255,255,0.1); }
    .table-responsive th { background:rgba(255,255,255,0.05); color:#9bc4f9; font-weight:600; font-size:0.9rem; }
    .table-responsive td { color:#e9f4ff; font-size:0.9rem; }
    .activity-badge { display:inline-block; padding:4px 8px; border-radius:12px; font-size:0.75rem; font-weight:600; }
    .activity-badge.create { background:rgba(76,175,80,0.2); color:#81C784; }
    .activity-badge.scan { background:rgba(33,150,243,0.2); color:#64B5F6; }
    .performance-bar { width:100px; height:8px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden; }
    .performance-fill { height:100%; background:linear-gradient(90deg, #4CAF50, #00d7ff); }

    @media (max-width: 1000px) { .app-shell { grid-template-columns: 1fr; } .sidebar { position:relative; width:100%; height:auto; } .main { margin-left:0; } }
    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: 1fr; }
        .charts-grid { grid-template-columns: 1fr; }
        .tables-grid { grid-template-columns: 1fr; }
        .dashboard-header { flex-direction:column; align-items:flex-start; gap:8px; }
        .chart-header { flex-direction:column; align-items:flex-start; gap:8px; }
    }
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">NA</div>
            <h1>NAAP Admin</h1>
        </div>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'active' : '' }}">Documents</a>
            <a href="{{ route('documents.create') }}" class="{{ request()->routeIs('documents.create') ? 'active' : '' }}">Route Document</a>
            <a href="{{ route('routing.index') }}" class="{{ request()->routeIs('routing.*') ? 'active' : '' }}">Track Document</a>
            <a href="{{ route('qr.scan') }}" class="{{ request()->routeIs('qr.scan') ? 'active' : '' }}">QR Scanner</a>
            <a href="{{ route('offices.index') }}" class="{{ request()->routeIs('offices.*') ? 'active' : '' }}">Offices</a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*') ? 'active' : '' }}">Activity Log</a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('logout') }}">Logout</a>
        </nav>
        <div class="bottom">Welcome <strong>{{ session('user_email', 'Guest') }}</strong></div>
    </aside>

    <main class="main">
        <div class="main-header">
            <h2>@yield('title', 'Dashboard')</h2>
            <small class="text-muted">University Document Routing & Tracking System</small>
        </div>

        @if(session('success')) <div class="card card-inner" style="margin-bottom:12px; background:rgba(55,181,131,0.22); border-color:rgba(55,181,131,0.3);">{{ session('success') }}</div> @endif

        @yield('content')
    </main>
</div>
</body>
</html>