<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NAAP Admin - @yield('title')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @yield('head')

    <style>
        :root {
            --bg: #0b1228;
            --sidebar-bg: #161e31;
            --sidebar-width: 280px;
            --accent-cyan: #22d3ee;
            --accent-purple: #a855f7;
            --text-dim: #94a3b8;
            --panel: rgba(30, 41, 59, 0.45);
            --panel-border: rgba(255, 255, 255, 0.08);
        }

        body { 
            background-color: var(--bg); 
            color: #f8fafc; 
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* SIDEBAR STYLING */
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--sidebar-bg); 
            border-right: 1px solid var(--panel-border);
            display: flex;
            flex-direction: column;
            padding: 25px 20px;
            position: fixed; 
            height: 100vh; 
            z-index: 3000; 
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #sidebarOverlay {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            z-index: 2500; display: none;
        }

        .brand-section {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 35px; padding-left: 10px;
        }

        .logo-box {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
            border-radius: 10px;
            display: grid; place-items: center;
            font-weight: 800; color: white;
            box-shadow: 0 4px 12px rgba(34, 211, 238, 0.3);
        }

        nav { flex: 1; overflow-y: auto; }

        .nav-link { 
            color: var(--text-dim); 
            text-decoration: none; 
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 12px; margin-bottom: 4px;
            transition: 0.2s; font-size: 0.95rem;
        }

        .nav-link:hover, .nav-link.active { 
            background: rgba(34, 211, 238, 0.1); 
            color: var(--accent-cyan); 
            font-weight: 600;
        }

        /* PAGINATION STYLING */
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .pagination .page-link {
            background: var(--panel) !important;
            border: 1px solid var(--panel-border) !important;
            color: var(--accent-cyan) !important;
            border-radius: 8px !important;
            margin: 0 4px;
            font-weight: 500;
            transition: 0.3s;
        }

        .pagination .page-link:hover {
            background: rgba(34, 211, 238, 0.2) !important;
            border-color: var(--accent-cyan) !important;
            box-shadow: 0 0 10px rgba(34, 211, 238, 0.2);
        }

        .pagination .page-item.active .page-link {
            background: var(--accent-cyan) !important;
            border-color: var(--accent-cyan) !important;
            color: #0b1228 !important;
        }

        .pagination .page-item.disabled .page-link {
            background: rgba(30, 41, 59, 0.3) !important;
            border-color: rgba(255, 255, 255, 0.05) !important;
            color: #64748b !important;
            cursor: not-allowed;
        }

        /* Pagination span and arrow text colors */
        .pagination .page-link span {
            color: var(--text-dim) !important;
        }

        .pagination .page-item.active .page-link span {
            color: #0b1228 !important;
        }

        /* HEADER & NOTIFICATIONS */
        header {
            background: rgba(11, 18, 40, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 2000;
            border-bottom: 1px solid var(--panel-border);
        }

        .notif-btn {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            color: var(--accent-cyan);
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; cursor: pointer;
            transition: 0.2s;
        }

        .notif-badge {
            position: absolute; top: -5px; right: -5px;
            background: #fb7185; color: white;
            font-size: 0.65rem; font-weight: 800;
            padding: 2px 6px; border-radius: 20px;
            border: 2px solid var(--bg);
        }

        .main-container { flex: 1; margin-left: var(--sidebar-width); min-height: 100vh; transition: 0.3s; }

        /* USER CARD */
        .user-profile-card {
            background: var(--panel); border-radius: 16px;
            padding: 12px; margin-bottom: 10px;
            display: flex; align-items: center; gap: 10px;
            border: 1px solid var(--panel-border);
        }

        .avatar {
            width: 35px; height: 35px;
            background: linear-gradient(to bottom right, var(--accent-purple), #6366f1);
            border-radius: 8px; display: grid; place-items: center;
            font-weight: bold; font-size: 0.8rem; color: white;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main-container { margin-left: 0; }
            body.sidebar-open .sidebar { transform: translateX(0); }
            body.sidebar-open #sidebarOverlay { display: block; }
        }
    </style>
</head>

<body>
    <div id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="brand-section">
            <div class="logo-group d-flex align-items-center gap-2">
                <div class="logo-box">NA</div>
                <div>
                    <h5 class="m-0 fw-bold">NAAP</h5>
                    <small class="text-info" style="font-size: 0.6rem; letter-spacing: 1px; font-weight: 700;">DOC ROUTING</small>
                </div>
            </div>
            <button class="btn d-lg-none text-white p-0" id="closeSidebar">
                <i class="bi bi-x-lg fs-4"></i>
            </button>
        </div>

        @php $isAdmin = session('user_role') === 'ADMIN'; @endphp
        <nav>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text-fill"></i> Documents
            </a>
            <a href="{{ route('qr.index') }}" class="nav-link {{ request()->routeIs('qr.*') ? 'active' : '' }}">
                <i class="bi bi-qr-code-scan"></i> QR Scanner
            </a>
            <a href="{{ route('track.index') }}" class="nav-link {{ request()->routeIs('track.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i> Tracking
            </a>

            @if($isAdmin)
                <a href="{{ route('offices.index') }}" class="nav-link {{ request()->routeIs('offices.*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill"></i> Offices
                </a>
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i> User Management
                </a>
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> Reports
                </a>
                <a href="{{ route('activity.index') }}" class="nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Activity
                </a>
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i> Settings
                </a>
            @endif

            <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </nav>

        <div class="mt-auto pt-3">
            <div class="user-profile-card">
                <div class="avatar">{{ substr(session('user_email', 'A'), 0, 1) }}</div>
                <div style="overflow: hidden;">
                    <div class="small fw-bold text-truncate text-white">{{ session('user_name', 'Admin') }}</div>
                    <small class="text-muted text-truncate d-block" style="font-size: 0.7rem;">{{ session('user_email', 'admin@naap.edu') }}</small>
                    <small class="text-info text-truncate d-block" style="font-size: 0.65rem;">{{ strtoupper(session('user_role', 'USER')) }}</small>
                </div>
            </div>
            <a href="{{ route('logout') }}" class="logout-link text-decoration-none" style="color: #fb7185; font-size: 0.85rem; font-weight: 600; padding: 8px 16px; display: block;">
                <i class="bi bi-box-arrow-left me-2"></i> Logout System
            </a>
        </div>
    </aside>

    <div class="main-container">
        <header class="container-fluid px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button class="btn d-lg-none" id="hamburgerMenu" style="background: var(--panel); border: 1px solid var(--panel-border); color: var(--accent-cyan);">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h4 class="mb-0 fw-bold">@yield('title')</h4>
            </div>

            <div class="dropdown">
                <div class="notif-btn position-relative" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge">3</span>
                </div>
                <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow mt-2" aria-labelledby="notifDropdown" style="width: 280px;">
                    <li class="dropdown-header text-info fw-bold">Notifications</li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item text-white small" href="#">New document assigned</a></li>
                    <li><a class="dropdown-item text-white small" href="#">System update complete</a></li>
                    <li><a class="dropdown-item text-white small" href="#">User login detected</a></li>
                </ul>
            </div>
        </header>

        <main class="container-fluid px-4 py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(34, 211, 238, 0.1); color: var(--accent-cyan); border-left: 4px solid var(--accent-cyan);">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(251, 113, 133, 0.1); color: #fb7185; border-left: 4px solid #fb7185;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    
    <script>
        // Apply saved theme on page load
        function applyTheme(theme) {
            const root = document.documentElement;
            if (theme === 'dark') {
                root.style.setProperty('--bg', '#0b1228');
                root.style.setProperty('--sidebar-bg', '#161e31');
                root.style.setProperty('--accent-cyan', '#22d3ee');
                root.style.setProperty('--accent-purple', '#a855f7');
                root.style.setProperty('--text-dim', '#94a3b8');
                root.style.setProperty('--panel', 'rgba(30, 41, 59, 0.45)');
                root.style.setProperty('--panel-border', 'rgba(255, 255, 255, 0.08)');
                document.body.style.backgroundColor = '#0b1228';
                document.body.style.color = '#f8fafc';
            } else {
                root.style.setProperty('--bg', '#f8fafc');
                root.style.setProperty('--sidebar-bg', '#e2e8f0');
                root.style.setProperty('--accent-cyan', '#0891b2');
                root.style.setProperty('--accent-purple', '#7c3aed');
                root.style.setProperty('--text-dim', '#475569');
                root.style.setProperty('--panel', 'rgba(226, 232, 240, 0.8)');
                root.style.setProperty('--panel-border', 'rgba(15, 23, 42, 0.1)');
                document.body.style.backgroundColor = '#f8fafc';
                document.body.style.color = '#1e293b';
            }
        }

        // Load saved theme on page load
        const savedTheme = localStorage.getItem('theme') || 'dark';
        applyTheme(savedTheme);

        const body = document.body;
        const hamburgerMenu = document.getElementById('hamburgerMenu');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');

        // Sidebar Toggle Logic
        hamburgerMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            body.classList.add('sidebar-open');
        });

        closeSidebar.addEventListener('click', () => body.classList.remove('sidebar-open'));
        overlay.addEventListener('click', () => body.classList.remove('sidebar-open'));

        // Auto-dismiss Alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Show notification toast
        window.showNotification = function(message, type = 'success') {
            const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'i';
            const bgColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6';
            const html = `
                <div style="position: fixed; top: 20px; right: 20px; background: ${bgColor}; color: white; padding: 16px 24px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); font-weight: 600; z-index: 9999; animation: slideIn 0.3s ease-out;">
                    <span style="font-size: 1.2rem; margin-right: 12px;">${icon}</span>${message}
                </div>
                <style>
                    @keyframes slideIn {
                        from { transform: translateX(400px); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                </style>
            `;
            const container = document.createElement('div');
            container.innerHTML = html;
            document.body.appendChild(container.firstElementChild);
            setTimeout(() => {
                container.firstElementChild?.remove();
            }, 3000);
        };
    </script>
</body>
</html>