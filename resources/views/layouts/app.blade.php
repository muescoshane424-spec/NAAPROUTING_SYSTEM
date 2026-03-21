<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'NAAP Admin')</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg: #0f172a;
            --side: rgba(15, 23, 42, 0.98);
            --panel: rgba(30, 41, 59, 0.7);
            --panel-border: rgba(148, 163, 184, 0.15);
            --text: #e2e8f0;
            --accent-a: #06b6d4;
            --accent-b: #3b82f6;
            --success: #22c55e;
            --danger: #ef4444;
        }

        /* Base Reset */
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { 
            margin: 0; 
            min-height: 100vh; 
            font-family: 'Inter', sans-serif; 
            color: var(--text); 
            background: #0b1228; 
            overflow-x: hidden; 
        }

        /* App Layout Shell */
        .app-shell { 
            display: grid; 
            grid-template-columns: 280px 1fr; 
            min-height: 100vh; 
            transition: all 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar { 
            background: var(--side); 
            border-right: 1px solid rgba(56, 189, 248, 0.1); 
            padding: 24px 18px; 
            display: flex; 
            flex-direction: column; 
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .sidebar .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 30px; }
        .sidebar .logo { 
            width: 40px; height: 40px; border-radius: 12px; 
            background: linear-gradient(135deg, var(--accent-a), var(--accent-b)); 
            display: grid; place-items: center; font-weight: 800; color: #fff; 
        }

        .sidebar nav { display: flex; flex-direction: column; gap: 8px; flex: 1; }
        .sidebar a { 
            display: flex; align-items: center; gap: 12px; color: var(--text); 
            text-decoration: none; padding: 12px 16px; border-radius: 12px; 
            transition: 0.2s; background: rgba(255,255,255,0.02);
        }
        .sidebar a:hover, .sidebar a.active { 
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.2), rgba(59, 130, 246, 0.2)); 
            border: 1px solid rgba(56, 189, 248, 0.3);
            color: #fff;
        }

        /* Main Content Area */
        .main { 
            padding: 24px; 
            width: 100%; 
            max-width: 100vw;
            overflow-x: hidden;
        }

        .top-bar { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 24px; background: var(--panel); 
            padding: 12px 20px; border-radius: 16px; border: 1px solid var(--panel-border);
        }

        .card { 
            background: var(--panel); border: 1px solid var(--panel-border); 
            border-radius: 20px; padding: 20px; margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Table Responsiveness Fix */
        .table-container { width: 100%; overflow-x: auto; border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { padding: 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }

        /* --- MOBILE OPTIMIZATIONS (The Fix) --- */
        @media (max-width: 992px) {
            .app-shell { grid-template-columns: 1fr; }

            /* Hide Sidebar by default on Mobile */
            .sidebar { 
                position: fixed; left: -300px; width: 280px; 
                transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
                box-shadow: 15px 0 50px rgba(0,0,0,0.8);
            }
            .sidebar.open { left: 0; }

            .main { padding: 15px; padding-bottom: 100px; }

            /* Show Bottom Navigation */
            .bottom-nav { 
                display: flex !important; 
                position: fixed; bottom: 0; left: 0; right: 0; 
                background: rgba(15, 23, 42, 0.95); 
                backdrop-filter: blur(10px);
                border-top: 1px solid rgba(255,255,255,0.1);
                padding: 12px; justify-content: space-around; z-index: 1000;
            }
        }

        /* Bottom Nav Hidden on Desktop */
        .bottom-nav { display: none; }
        .bottom-nav a { 
            text-decoration: none; color: #94a3b8; 
            font-size: 11px; display: flex; flex-direction: column; align-items: center; gap: 4px;
        }
        .bottom-nav a.active { color: var(--accent-a); font-weight: 700; }
        .bottom-nav .icon { font-size: 22px; }

        /* Backdrop for Mobile Sidebar */
        .backdrop { 
            display: none; position: fixed; inset: 0; 
            background: rgba(0,0,0,0.7); z-index: 90; 
        }
        .backdrop.active { display: block; }

        /* KPI Grid Fix */
        .grid-responsive { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 16px; 
        }

        button.menu-toggle {
            background: linear-gradient(135deg, var(--accent-a), var(--accent-b));
            border: none; color: white; padding: 10px 16px; border-radius: 10px;
            font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;
        }
    </style>
</head>
<body>

    <div class="backdrop" id="backdrop"></div>

    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <div class="logo">NA</div>
                <h1 style="margin:0; font-size: 1.4rem;">NAAP Admin</h1>
            </div>
            <nav>
                <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}"><span>🏠</span> Dashboard</a>
                <a href="{{ route('documents.index') }}" class="{{ request()->is('documents*') ? 'active' : '' }}"><span>📁</span> Documents</a>
                <a href="{{ route('qr.scan') }}" class="{{ request()->is('qr-scan*') ? 'active' : '' }}"><span>📷</span> QR Scanner</a>
                <a href="{{ route('offices.index') }}"><span>🏢</span> Offices</a>
                <a href="{{ route('reports.index') }}"><span>📊</span> Reports</a>
                <hr style="width:100%; opacity: 0.1; margin: 10px 0;">
                <a href="{{ route('logout') }}" style="color: var(--danger);"><span>🚪</span> Logout</a>
            </nav>
        </aside>

        <main class="main">
            <header class="top-bar">
                <button class="menu-toggle" id="menuBtn">
                    <span>☰</span> Menu
                </button>
                <div style="text-align: right;">
                    <div style="font-weight: 700; font-size: 0.9rem;">{{ session('user_email', 'User') }}</div>
                    <div style="font-size: 0.75rem; color: var(--accent-a);">System Online</div>
                </div>
            </header>

            @if(session('success'))
                <div class="card" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); color: #a7f3d0; padding: 15px;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <div class="content-body">
                @yield('content')
            </div>
        </main>

        <nav class="bottom-nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <span class="icon">🏠</span> Dashboard
            </a>
            <a href="{{ route('documents.index') }}" class="{{ request()->is('documents*') ? 'active' : '' }}">
                <span class="icon">📄</span> Docs
            </a>
            <a href="{{ route('qr.scan') }}" class="{{ request()->is('qr-scan*') ? 'active' : '' }}">
                <span class="icon">📷</span> Scan
            </a>
            <a href="{{ route('reports.index') }}">
                <span class="icon">📊</span> Reports
            </a>
        </nav>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuBtn = document.getElementById('menuBtn');
        const backdrop = document.getElementById('backdrop');

        // Toggle Sidebar on Mobile
        menuBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
            backdrop.classList.add('active');
        });

        // Close Sidebar when clicking backdrop
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('open');
            backdrop.classList.remove('active');
        });

        // Auto-close sidebar on link click (mobile)
        document.querySelectorAll('.sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('open');
                    backdrop.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>