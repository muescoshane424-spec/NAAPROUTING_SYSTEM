<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NAAP Admin | @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .card { background-color: rgba(30,41,59,0.8); border-radius: 12px; }
        .sidebar { width: 250px; position: fixed; height: 100%; background-color: #1e293b; }
        .sidebar a { display: block; padding: 10px; color: #cbd5e1; text-decoration: none; }
        .sidebar a:hover { background-color: #334155; color: #fff; }
        .topbar { height: 60px; background-color: #1e293b; display:flex; align-items:center; padding:0 20px; }
        .main { margin-left: 250px; padding: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center pt-3">NAAP Admin</h4>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <a href="{{ route('documents.index') }}">Documents</a>
        <a href="{{ route('routing.index') }}">Routing</a>
        <a href="{{ route('qr.scan') }}">QR Scan</a>
        <a href="{{ route('offices.index') }}">Offices</a>
        <a href="{{ route('users.index') }}">Users</a>
    </div>

    <div class="main">
        <div class="topbar">
            <h5>@yield('title')</h5>
        </div>
        <div class="content mt-3">
            @yield('content')
        </div>
    </div>
</body>
</html>