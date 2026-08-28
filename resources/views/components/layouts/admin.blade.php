<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — DOMINIUM TECH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --blue: #3b82f6;
            --blue-light: #60a5fa;
            --cyan: #0ea5e9;
            --bg-dark: #020617;
            --bg-blue-dark: #0c1e3a;
            --bg-blue: #0f2847;
            --bg-card: #081a31;
            --white: #ffffff;
            --text-muted: rgba(255,255,255,0.48);
            --text-secondary: rgba(255,255,255,0.65);
            --border: rgba(255,255,255,0.09);
            --accent-surface: rgba(59,130,246,0.12);
            --accent-light: #60a5fa;
            --radius: 16px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            color: var(--white);
            background: linear-gradient(135deg, #020617 0%, #0c1e3a 40%, #0f2847 70%, #0a1628 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        .background {
            position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0;
        }
        .curved-pattern {
            position: absolute; inset: 0; opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E");
            background-size: 200px 100px;
        }
        .grid-pattern {
            position: absolute; inset: 0; opacity: 0.025;
            background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
            background-size: 80px 80px;
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); }
        .orb-1 { width: 500px; height: 500px; top: -180px; right: -100px; background: radial-gradient(circle, rgba(59,130,246,0.22) 0%, transparent 70%); animation: orbFloat1 9s ease-in-out infinite; }
        .orb-2 { width: 450px; height: 450px; bottom: -200px; left: 15%; background: radial-gradient(circle, rgba(99,102,241,0.16) 0%, transparent 70%); animation: orbFloat2 11s ease-in-out infinite; }
        @keyframes orbFloat1 { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,25px) scale(1.05); } }
        @keyframes orbFloat2 { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(25px,-25px) scale(1.08); } }

        .admin-layout { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px;
            padding: 28px 20px;
            background: rgba(2,6,23,0.95);
            border-right: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            display: flex; flex-direction: column; z-index: 100;
        }
        .sidebar-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 36px; }
        .sidebar-brand img { height: 42px; width: auto; object-fit: contain; }
        .sidebar-brand span { font-size: 14px; font-weight: 800; letter-spacing: 0.5px; color: rgba(255,255,255,0.8); text-transform: uppercase; }

        .sidebar-section { margin-bottom: 8px; }
        .sidebar-label { font-size: 9px; font-weight: 700; color: rgba(255,255,255,0.28); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 10px; padding: 0 12px; }

        .sidebar-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 14px; border-radius: 12px;
            color: rgba(255,255,255,0.5); font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all 0.2s ease;
        }
        .sidebar-nav a i { width: 18px; text-align: center; font-size: 14px; }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.85); }
        .sidebar-nav a.active {
            background: linear-gradient(90deg, rgba(59,130,246,0.14), rgba(59,130,246,0.04));
            color: white; border: 1px solid rgba(59,130,246,0.12);
        }
        .sidebar-nav a.active i { color: var(--blue-light); }

        .sidebar-footer { margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 12px;
            background: rgba(255,255,255,0.04);
        }
        .sidebar-user-avatar {
            width: 34px; height: 34px; border-radius: 10px;
            background: rgba(59,130,246,0.15); display: flex; align-items: center; justify-content: center;
            font-size: 13px; color: var(--blue-light); font-weight: 800;
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.85); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: 10px; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.05em; }
        .sidebar-logout {
            display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 8px;
            color: rgba(255,255,255,0.35); text-decoration: none;
            transition: all 0.2s ease; border: none; background: none; cursor: pointer;
        }
        .sidebar-logout:hover { background: rgba(239,68,68,0.12); color: #f87171; }

        .admin-main {
            flex: 1; margin-left: 260px; padding: 32px 40px; min-height: 100vh;
        }

        .admin-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 32px;
        }
        .admin-title { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: white; }
        .admin-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

        .admin-card {
            background: linear-gradient(145deg, rgba(15,40,71,0.7), rgba(5,18,36,0.8));
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px; padding: 28px; margin-bottom: 24px;
            backdrop-filter: blur(10px);
        }

        .admin-table {
            width: 100%; border-collapse: collapse;
        }
        .admin-table th {
            text-align: left; padding: 12px 16px;
            font-size: 10px; font-weight: 700; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.08em;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .admin-table td {
            padding: 14px 16px; font-size: 13px; color: rgba(255,255,255,0.75);
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .admin-table tr:hover td { background: rgba(255,255,255,0.02); }

        .admin-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .badge-pending { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); }
        .badge-in_progress { background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.2); }
        .badge-completed { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .badge-cancelled { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .badge-active { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .badge-inactive { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .badge-admin { background: rgba(139,92,246,0.12); color: #a78bfa; border: 1px solid rgba(139,92,246,0.2); }
        .badge-user { background: rgba(255,255,255,0.06); color: var(--text-secondary); border: 1px solid rgba(255,255,255,0.1); }

        .stat-card {
            background: linear-gradient(145deg, rgba(15,40,71,0.7), rgba(5,18,36,0.8));
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px; padding: 24px; text-align: center;
        }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 14px; }
        .stat-value { font-size: 32px; font-weight: 800; color: white; line-height: 1; }
        .stat-label { font-size: 11px; color: var(--text-muted); margin-top: 6px; text-transform: uppercase; letter-spacing: 0.05em; }

        .admin-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer; transition: all 0.2s ease;
        }
        .btn-primary { background: var(--blue); color: white; }
        .btn-primary:hover { background: var(--blue-light); transform: translateY(-1px); box-shadow: 0 8px 25px rgba(59,130,246,0.25); }
        .btn-secondary { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.6); border: 1px solid rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: white; }
        .btn-danger { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.15); }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-sm { padding: 6px 14px; font-size: 11px; }

        .admin-input {
            width: 100%; height: 42px; padding: 0 14px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.09);
            border-radius: 10px; color: white; font-size: 13px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .admin-input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
        .admin-input::placeholder { color: var(--text-muted); }
        select.admin-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.48)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px; }
        select.admin-input option { background: #0f172a; color: white; }
        textarea.admin-input { height: auto; min-height: 90px; padding: 12px 14px; resize: vertical; }

        .admin-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.55); text-transform: uppercase; letter-spacing: 0.06em; }

        .admin-alert {
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px; font-size: 13px;
        }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }

        .pagination { display: flex; align-items: center; gap: 6px; justify-content: center; margin-top: 24px; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px;
            font-size: 12px; font-weight: 600; text-decoration: none;
            transition: all 0.2s ease;
        }
        .pagination a { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5); border: 1px solid rgba(255,255,255,0.08); }
        .pagination a:hover { background: rgba(59,130,246,0.1); color: var(--blue-light); border-color: rgba(59,130,246,0.2); }
        .pagination .active { background: var(--blue); color: white; border: 1px solid var(--blue); }
        .pagination .disabled { opacity: 0.3; pointer-events: none; }

        @media (max-width: 768px) {
            .admin-sidebar { width: 0; overflow: hidden; }
            .admin-main { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="background" aria-hidden="true">
        <div class="curved-pattern"></div>
        <div class="grid-pattern"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM">
                <span>DOMINIUM</span>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">Navigation</div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
                <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-open"></i> Projets
                </a>
                <a href="{{ route('admin.clients.index') }}" class="{{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> Clients
                </a>
                <a href="{{ route('admin.questions.index') }}" class="{{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-question"></i> Questions
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield"></i> Utilisateurs
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-role">Administrateur</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-logout" title="Déconnexion">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="admin-main">
            @if(session('success'))
                <div class="admin-alert alert-success">
                    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="admin-alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
