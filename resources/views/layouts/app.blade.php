<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'NAAP Admin')</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #0f172a;
        --side: rgba(15, 23, 42, 0.95);
        --panel: rgba(30, 41, 59, 0.72);
        --panel-border: rgba(148, 163, 184, 0.18);
        --text: #e2e8f0;
        --accent-a: #06b6d4;
        --accent-b: #3b82f6;
        --success: #22c55e;
        --warning: #facc15;
        --danger: #ef4444;
    }
    body { margin:0; min-height:100vh; font-family:'Inter', 'Poppins', sans-serif; color:var(--text); background: linear-gradient(140deg, #0b1228 0%, #121e3f 50%, #0f172a 100%); transition: background .4s ease, color .4s ease; }
    body.light { --bg: #e2e8f0; --side: rgba(255,255,255,0.95); --panel: rgba(255,255,255,0.18); --panel-border: rgba(148, 163, 184, 0.28); --text: #0f172a; background: linear-gradient(140deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%); }
    * { box-sizing: border-box; }
    .app-shell { display:grid; grid-template-columns: 280px 1fr; min-height:100vh; transition: grid-template-columns .25s ease; }
    .sidebar { background: var(--side); border-right: 1px solid rgba(56, 189, 248, .2); backdrop-filter: blur(16px); padding: 22px 18px; display:flex; flex-direction:column; border-top-right-radius: 20px; border-bottom-right-radius: 20px; transition: width .25s ease, transform .25s ease; }
    .sidebar.collapsed { width: 76px; overflow: hidden; }
    .sidebar.collapsed .brand h1, .sidebar.collapsed nav a span.label, .sidebar.collapsed .bottom { display:none; }
    .sidebar.collapsed .brand .logo { margin-right: 0; }
    .app-shell.sidebar-collapsed { grid-template-columns: 76px 1fr; }

    .sidebar .brand { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
    .sidebar .brand .logo { width:36px; height:36px; border-radius:12px; background:linear-gradient(135deg, var(--accent-a), var(--accent-b)); display:grid; place-items:center; font-weight:800; color:#fff; }
    .sidebar .brand h1 { margin:0; font-size:1.4rem; }
    .sidebar nav { display:flex; flex-direction:column; gap:8px; flex:1; overflow-y:auto; }
    .sidebar a { display:flex; align-items:center; gap:10px; color:var(--text); text-decoration:none; padding:10px 12px; border-radius:12px; border:1px solid var(--panel-border); background: linear-gradient(135deg, rgba(255,255,255,.04), rgba(7, 14, 32, .35)); transition:all .2s ease; font-weight:500; }
    .sidebar a span.label { display:inline-block; transition: opacity .2s ease; }
    .sidebar a:hover, .sidebar a.active { background: linear-gradient(135deg, rgba(14,165,233,0.35), rgba(59,130,246,0.35)); border-color: rgba(56,189,248,0.55); color:#fff; transform:translateX(2px); }
    .sidebar .bottom { margin-top:14px; color:rgba(206, 232, 255, .8); font-size:0.82rem; }

    .top-nav { position: sticky; top:0; z-index:20; display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; padding:12px 16px; background:linear-gradient(135deg, rgba(15,23,42,0.95), rgba(30,41,59,0.85)); border:1px solid var(--panel-border); border-radius:16px; backdrop-filter: blur(10px); }
    .top-nav .search-wrap { flex:1; max-width:540px; }
    .top-nav input { width:100%; border:1px solid rgba(148,163,184,0.3); border-radius:12px; padding:10px 12px; background:rgba(15,23,42,0.72); color:var(--text); }
    .top-nav .btns { display:flex; gap:8px; }
    .top-nav button { border:none; border-radius:12px; padding:8px 12px; font-weight:600; color: #0f172a; cursor:pointer; transition: transform .2s ease; background: linear-gradient(135deg, var(--accent-a), var(--accent-b)); }
    .top-nav button:hover { transform: scale(1.03); }

    .main { padding:18px 24px; overflow:auto; }
    .main-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:8px; }
    .main-header h2 { margin:0; font-size:1.9rem; font-weight:800; color: #e0f2fe; }

    .card { background: var(--panel); border:1px solid var(--panel-border); border-radius:20px; backdrop-filter: blur(14px); box-shadow:0 18px 38px rgba(10,15,33,0.35); color: var(--text); transition: transform .24s ease, box-shadow .24s ease; }
    .card:hover { transform: translateY(-2px); box-shadow:0 24px 44px rgba(12,17,44,0.42); }
    .card-inner { padding: 16px; }

    .grid2 { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:12px; }
    .table-wrapper { overflow:auto; }
    table { width:100%; border-collapse:collapse; border-radius:14px; overflow:hidden; background:rgba(15,23,42,0.72); }
    th, td { padding:12px 14px; border-bottom:1px solid rgba(148,163,184,0.2); }
    th { text-transform:uppercase; color:#a5b4fc; font-size:.8rem; }
    td { color:var(--text); }
    .badge-status { border-radius:999px; font-size:.75rem; font-weight:700; padding:4px 10px; text-transform:capitalize; }
    .badge-success { background:rgba(34,197,94,0.2); color:#a7f3d0; }
    .badge-warning { background:rgba(250,204,21,0.2); color:#fef08a; }
    .badge-danger { background:rgba(239,68,68,0.2); color:#fecaca; }
    .badge-info { background:rgba(56,189,248,0.2); color:#bfdbfe; }

    .bottom-nav { display:none; position:fixed; bottom:0; left:0; right:0; background:rgba(15,23,42,0.96); border-top:1px solid rgba(148,163,184,0.2); backdrop-filter: blur(10px); padding:8px 14px; display:flex; justify-content:space-around; align-items:center; z-index:30; }
    .mobile-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 38; opacity: 0; visibility: hidden; transition: opacity .2s ease, visibility .2s ease; }
    .mobile-backdrop.visible { opacity: 1; visibility: visible; }
    .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 1000; display: flex; flex-direction: column; gap: 10px; }
    .toast { min-width: 200px; background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(14, 165, 233, 0.4); color: #fff; padding: 12px 14px; border-radius: 12px; box-shadow: 0 10px 30px rgba(7, 13, 30, 0.45); transform: translateY(20px); opacity: 0; animation: toastIn .25s ease forwards; }
    @keyframes toastIn { to { transform: translateY(0); opacity: 1; } }
    .bottom-nav a { display:flex; flex-direction:column; align-items:center; gap:4px; color:#cbd5e1; text-decoration:none; font-size:.72rem; }
    .bottom-nav a.active { color:#38bdf8; }

    .btn { display:inline-flex; align-items:center; justify-content:center; gap:0.35rem; padding:10px 16px; border:none; border-radius:10px; font-size:0.9rem; font-weight:700; background:linear-gradient(135deg, #0ea5e9, #06b6d4); color:#fff; transition: transform .2s ease, box-shadow .2s ease; cursor:pointer; }
    .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0, 120, 180, 0.25); }
    .btn:focus-visible { outline: 2px solid rgba(56, 189, 248, 0.8); outline-offset: 2px; }
    .btn-primary { background:linear-gradient(135deg, #38bdf8, #0ea5e9); color:#fff; }
    .btn-secondary { background: rgba(15, 23, 42, 0.75); color: #e2e8f0; border: 1px solid rgba(148, 163, 184, 0.35); }

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
    <div class="mobile-backdrop" id="mobileBackdrop"></div>
    <div class="toast-container" id="toastContainer"></div>
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">NA</div>
            <h1>NAAP Admin</h1>
        </div>
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span>🏠</span><span class="label">Dashboard</span></a>
            <a href="{{ route('documents.index') }}" class="{{ request()->routeIs('documents.*') ? 'active' : '' }}"><span>📁</span><span class="label">Documents</span></a>
            <a href="{{ route('documents.create') }}" class="{{ request()->routeIs('documents.create') ? 'active' : '' }}"><span>➕</span><span class="label">Route Doc</span></a>
            <a href="{{ route('routing.index') }}" class="{{ request()->routeIs('routing.*') ? 'active' : '' }}"><span>🔁</span><span class="label">Routing</span></a>
            <a href="{{ route('qr.scan') }}" class="{{ request()->routeIs('qr.scan') ? 'active' : '' }}"><span>📷</span><span class="label">QR Scanner</span></a>
            <a href="{{ route('offices.index') }}" class="{{ request()->routeIs('offices.*') ? 'active' : '' }}"><span>🏢</span><span class="label">Offices</span></a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><span>👥</span><span class="label">Users</span></a>
            <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*') ? 'active' : '' }}"><span>🕒</span><span class="label">Activity</span></a>
            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><span>📊</span><span class="label">Reports</span></a>
            <a href="{{ route('logout') }}"><span>🚪</span><span class="label">Logout</span></a>
        </nav>
        <div class="bottom">Welcome <strong>{{ session('user_email', 'Guest') }}</strong></div>
    </aside>

    <main class="main">
        <div class="top-nav">
            <div class="search-wrap">
                <input type="search" placeholder="Search documents, offices, users..." id="globalSearch" />
            </div>
            <div class="btns">
                <button id="themeToggle" type="button">Light Mode</button>
                <button id="refreshDashboard" type="button">Refresh</button>
            </div>
        </div>

        <div class="main-header">
            <button id="sidebarToggle" class="btn btn-primary" style="padding:6px 12px; font-size:0.85rem;">☰</button>
            <div style="display:flex; flex-direction:column; gap:4px;">
                <h2 style="margin:0;">@yield('title', 'Dashboard')</h2>
                <small class="text-muted">University Document Routing & Tracking System</small>
            </div>
        </div>

        @if(session('success')) <div class="card card-inner" style="margin-bottom:12px; background:rgba(55,181,131,0.22); border-color:rgba(55,181,131,0.3);">{{ session('success') }}</div> @endif

        @yield('content')
    </main>

    <nav class="bottom-nav" aria-label="Quick Actions">
        <a href="{{ route('dashboard') }}" class="active"><span>🏠</span><span>Dashboard</span></a>
        <a href="{{ route('documents.index') }}"><span>📄</span><span>Docs</span></a>
        <a href="{{ route('routing.index') }}"><span>↔️</span><span>Routing</span></a>
        <a href="{{ route('qr.scan') }}"><span>📷</span><span>QR</span></a>
        <a href="{{ route('reports.index') }}"><span>📊</span><span>Reports</span></a>
    </nav>
</div>
<script>
    const rootShell = document.querySelector('.app-shell');
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const themeToggle = document.getElementById('themeToggle');
    const refreshBtn = document.getElementById('refreshDashboard');
    const searchInput = document.getElementById('globalSearch');
    const mobileBackdrop = document.getElementById('mobileBackdrop');

    const applySidebarState = (collapsed) => {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            rootShell.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            rootShell.classList.remove('sidebar-collapsed');
        }
    };

    const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    applySidebarState(collapsed);

    toggle.addEventListener('click', () => {
        const currentlyCollapsed = sidebar.classList.contains('collapsed');
        applySidebarState(!currentlyCollapsed);
        localStorage.setItem('sidebarCollapsed', !currentlyCollapsed);
    });

    const applyTheme = (mode) => {
        document.body.classList.toggle('light', mode === 'light');
        themeToggle.textContent = mode === 'light' ? 'Dark Mode' : 'Light Mode';
        localStorage.setItem('dashboardTheme', mode);
    };

    const savedTheme = localStorage.getItem('dashboardTheme') || 'dark';
    applyTheme(savedTheme);

    themeToggle.addEventListener('click', () => {
        const next = document.body.classList.contains('light') ? 'dark' : 'light';
        applyTheme(next);
    });

    refreshBtn.addEventListener('click', () => {
        window.location.reload();
    });

    function showToast(message) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    window.toast = showToast;

    searchInput.addEventListener('input', (e) => {
        const q = e.target.value.trim();
        if (q.length > 2) {
            window.toast('Quick search: ' + q);
        }
    });

    const showMobileSidebar = () => {
        sidebar.classList.add('open');
        mobileBackdrop.classList.add('visible');
    };

    const hideMobileSidebar = () => {
        sidebar.classList.remove('open');
        mobileBackdrop.classList.remove('visible');
    };

    const initMobile = () => {
        if (window.innerWidth <= 768) {
            hideMobileSidebar();
            sidebar.style.position = 'fixed';
            sidebar.style.left = '-100%';
        } else {
            sidebar.style.position = '';
            sidebar.style.left = '';
            mobileBackdrop.classList.remove('visible');
        }
    };

    initMobile();
    window.addEventListener('resize', initMobile);

    toggle.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            if (sidebar.classList.contains('open')) {
                hideMobileSidebar();
            } else {
                showMobileSidebar();
            }
            return;
        }

        const currentlyCollapsed = sidebar.classList.contains('collapsed');
        applySidebarState(!currentlyCollapsed);
        localStorage.setItem('sidebarCollapsed', !currentlyCollapsed);
    });

    mobileBackdrop.addEventListener('click', hideMobileSidebar);
</script>
</body>
</html>