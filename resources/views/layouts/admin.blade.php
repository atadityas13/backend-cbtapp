<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Panel CBT MTsN 11 Majalengka</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-base: #070b13;
            --bg-surface: #0f1624;
            --bg-surface-hover: #172237;
            --border-color: #1e293b;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            
            --primary: #10b981;
            --primary-hover: #34d399;
            --primary-glow: rgba(16, 185, 129, 0.15);
            
            --danger: #ef4444;
            --danger-hover: #f87171;
            --danger-glow: rgba(239, 68, 68, 0.15);
            
            --warning: #f59e0b;
            --warning-hover: #fbbf24;
            
            --info: #3b82f6;
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Design */
        .sidebar {
            width: 280px;
            background-color: var(--bg-surface);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar-brand {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-brand i {
            font-size: 28px;
            color: var(--primary);
            text-shadow: 0 0 10px var(--primary);
        }

        .sidebar-brand img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            filter: drop-shadow(0 0 6px var(--primary));
        }

        .sidebar-brand-text {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #ffffff 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-brand-text span {
            color: var(--primary);
            -webkit-text-fill-color: initial;
        }

        .sidebar-menu {
            list-style: none;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .sidebar-item a:hover {
            color: var(--text-primary);
            background-color: var(--bg-surface-hover);
        }

        .sidebar-item.active a {
            color: var(--primary);
            background-color: var(--primary-glow);
            font-weight: 600;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .sidebar-item a i {
            font-size: 18px;
        }

        .sidebar-user {
            padding: 20px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--primary-glow);
            border: 2px solid var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--primary);
        }

        .sidebar-user-info {
            flex-grow: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: capitalize;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .sidebar-user-role::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--primary);
        }

        .sidebar-user-role.admin::before {
            background-color: #3b82f6;
        }

        /* Main Content wrapper */
        .main-wrapper {
            margin-left: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
        }

        /* Top Header */
        .main-header {
            height: 70px;
            background-color: rgba(15, 22, 36, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 24px;
            cursor: pointer;
        }

        .header-title {
            font-size: 18px;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-logout:hover {
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.4);
            background-color: var(--danger-glow);
        }

        /* Content Container */
        .content {
            padding: 32px;
            flex-grow: 1;
        }

        /* General UI Elements */
        .card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.3);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: transparent;
        }

        .card.card-primary::before {
            background: var(--primary);
        }

        .card.card-danger::before {
            background: var(--danger);
        }

        .card.card-warning::before {
            background: var(--warning);
        }

        .card-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: var(--primary);
        }

        /* Forms styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            background-color: var(--bg-base);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-family: var(--font-main);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: var(--font-main);
            font-size: 14px;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #000;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: var(--bg-surface-hover);
        }

        .btn-danger {
            background-color: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 12px;
            border-radius: var(--radius-sm);
        }

        /* Table */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .table th {
            background-color: rgba(15, 22, 36, 0.6);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            vertical-align: middle;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover td {
            background-color: rgba(23, 34, 55, 0.3);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: var(--primary);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .badge-warning {
            background-color: rgba(245, 158, 11, 0.15);
            color: var(--warning);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            animation: slideDown 0.3s ease-out;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: var(--primary);
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }

        /* Pagination */
        .pagination-container {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Modal custom simple (via absolute layouts if needed or JS toggles) */
        
        /* Utility classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-0 { margin-bottom: 0; }
        
        /* Animations */
        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Breakpoints */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .menu-toggle {
                display: block;
            }
            .main-header {
                padding: 0 16px;
                height: 60px;
            }
            .header-title {
                font-size: 15px;
                max-width: 55%;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .btn-logout span {
                display: none; /* Hide logout text on mobile devices */
            }
            .btn-logout {
                padding: 8px 10px;
            }
            .content {
                padding: 16px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand" style="cursor: pointer;" onclick="window.location.href='{{ route('admin.dashboard') }}'">
            <img src="{{ asset('assets/cbt_logo.png') }}" alt="CBT Logo">
            <div class="sidebar-brand-text">CBT PANEL</div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-item {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Ringkasan Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item {{ Route::is('admin.students.*') ? 'active' : '' }}">
                <a href="{{ route('admin.students.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Siswa Terdaftar</span>
                </a>
            </li>
            <li class="sidebar-item {{ Route::is('admin.violations.*') ? 'active' : '' }}">
                <a href="{{ route('admin.violations.index') }}">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Pelanggaran & Ban</span>
                </a>
            </li>
            <li class="sidebar-item {{ Route::is('admin.help.*') ? 'active' : '' }}">
                <a href="{{ route('admin.help.index') }}">
                    <i class="bi bi-chat-left-text-fill"></i>
                    <span>Bantuan Siswa</span>
                </a>
            </li>
            <li class="sidebar-item {{ Route::is('admin.notifications.*') ? 'active' : '' }}">
                <a href="{{ route('admin.notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>Kirim Notifikasi Push</span>
                </a>
            </li>
            @if(Auth::user()->role === 'admin')
                <li class="sidebar-item {{ Route::is('admin.media.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.media.index') }}">
                        <i class="bi bi-file-earmark-music-fill"></i>
                        <span>Manajemen File Media</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.versions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.versions.index') }}">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                        <span>Manajemen Versi</span>
                    </a>
                </li>
                <li class="sidebar-item {{ Route::is('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}">
                        <i class="bi bi-gear-fill"></i>
                        <span>Pengaturan Sistem</span>
                    </a>
                </li>
            @endif
        </ul>
        
        <!-- Authenticated User Profile Summary at bottom -->
        <a href="{{ route('admin.settings.index') }}" class="sidebar-user" style="text-decoration: none; color: inherit; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='var(--bg-surface-hover)'" onmouseout="this.style.backgroundColor='transparent'">
            <div class="sidebar-user-avatar">
                {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</div>
                <div class="sidebar-user-role {{ Auth::user()->role === 'admin' ? 'admin' : '' }}">
                    {{ Auth::user()->role === 'admin' ? 'Super Admin' : 'Proktor' }}
                </div>
            </div>
            <div class="sidebar-user-action" style="color: var(--text-secondary); margin-left: auto; transition: color 0.2s;">
                <i class="bi bi-gear-fill"></i>
            </div>
        </a>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <header class="main-header">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="header-title">
                @yield('header_title', 'Ringkasan Sistem')
            </div>
            <div class="header-actions">
                <!-- User name & quick logout -->
                <form action="{{ route('logout') }}" method="POST" id="logoutForm">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="content">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <div>
                        <ul style="list-style: none; padding-left: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Toggle Sidebar Mobile JS -->
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', (e) => {
                sidebar.classList.toggle('active');
                e.stopPropagation();
            });
            
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
