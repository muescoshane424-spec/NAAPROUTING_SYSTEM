<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'NAAP Admin')</title>
@vite('resources/css/app.css')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#0f172a] text-white flex h-screen font-sans">

<!-- Sidebar -->
<div class="w-64 bg-[#111827] p-6 flex flex-col">
    <h1 class="text-2xl font-bold mb-8 text-cyan-400">NAAP Admin</h1>
    <nav class="space-y-2 flex-1">
        <a href="{{ route('dashboard') }}" class="block p-2 rounded hover:bg-white/10 transition">Dashboard</a>
        <a href="{{ route('documents.index') }}" class="block p-2 rounded hover:bg-white/10 transition">Documents</a>
        <a href="{{ route('documents.create') }}" class="block p-2 rounded hover:bg-white/10 transition">Route Document</a>
        <a href="{{ route('routing.index') }}" class="block p-2 rounded hover:bg-white/10 transition">Routing</a>
        <a href="{{ route('qr.scan') }}" class="block p-2 rounded hover:bg-white/10 transition">QR Scanner</a>
        <a href="{{ route('offices.index') }}" class="block p-2 rounded hover:bg-white/10 transition">Offices</a>
        <a href="{{ route('users.index') }}" class="block p-2 rounded hover:bg-white/10 transition">Users</a>
        <a href="{{ route('activity.index') }}" class="block p-2 rounded hover:bg-white/10 transition">Activity Log</a>
        <a href="{{ route('reports.index') }}" class="block p-2 rounded hover:bg-white/10 transition">Reports</a>
    </nav>
</div>

<!-- Main Content -->
<div class="flex-1 overflow-auto p-6">
    @yield('content')
</div>

</body>
</html>