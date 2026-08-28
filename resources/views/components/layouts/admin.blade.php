<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ config('admin_brand.app_name', 'DOMINIUM TECH') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --navy: #071426;
            --navy-2: #0b1b31;
            --blue: #122c4d;
            --blue-light: #193b65;
            --yellow: #ffb703;
            --yellow-light: #ffc72c;
            --white: #ffffff;
            --text: #e6edf5;
            --muted: #91a1b5;
            --line: rgba(255,255,255,.12);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--white);
            background: var(--navy);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Background (même que le hero HTML) ── */
        .background {
            position: fixed; inset: 0; overflow: hidden; pointer-events: none; z-index: 0;
            background:
                radial-gradient(circle at 82% 45%, rgba(35,76,122,.45), transparent 34%),
                radial-gradient(circle at 15% 20%, rgba(255,183,3,.05), transparent 28%),
                linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);
        }
        .background::before {
            content: ""; position: absolute; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 55px 55px;
        }

        /* ── Layout ── */
        .admin-layout { display: flex; min-height: 100vh; position: relative; z-index: 1; }

        /* ── Sidebar ── */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px;
            padding: 34px 24px;
            background: rgba(7,20,38,0.92);
            border-right: 1px solid var(--line);
            backdrop-filter: blur(20px);
            display: flex; flex-direction: column; z-index: 100;
        }

        .sidebar-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
        .sidebar-brand img { height: 45px; width: auto; object-fit: contain; filter: drop-shadow(0 0 12px rgba(255,183,3,0.25)); }
        .sidebar-brand span { font-size: 14px; font-weight: 800; letter-spacing: 0.5px; color: rgba(255,255,255,0.8); text-transform: uppercase; }

        .sidebar-section { margin-bottom: 8px; }
        .sidebar-label {
            font-size: 10px; font-weight: 700; color: var(--muted);
            letter-spacing: 1.8px; text-transform: uppercase;
            margin-bottom: 14px; padding: 0 12px;
        }

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
            background: linear-gradient(90deg, rgba(255,183,3,0.14), rgba(255,183,3,0.04));
            color: white; border: 1px solid rgba(255,183,3,0.12);
        }
        .sidebar-nav a.active i { color: var(--yellow); }

        .sidebar-footer { margin-top: auto; padding-top: 16px; border-top: 1px solid var(--line); }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 12px;
            background: rgba(255,255,255,0.04);
        }
        .sidebar-user-avatar {
            width: 34px; height: 34px; border-radius: 10px;
            background: rgba(255,183,3,0.15); display: flex; align-items: center; justify-content: center;
            font-size: 13px; color: var(--yellow); font-weight: 800;
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name { font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.85); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-role { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .sidebar-logout {
            display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 8px;
            color: var(--muted); text-decoration: none;
            transition: all 0.2s ease; border: none; background: none; cursor: pointer;
        }
        .sidebar-logout:hover { background: rgba(239,68,68,0.12); color: #f87171; }

        /* ── Main ── */
        .admin-main {
            flex: 1; margin-left: 260px; padding: 40px 55px; min-height: 100vh;
        }

        /* ── Header ── */
        .admin-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 32px;
        }
        .admin-title { font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: white; }
        .admin-subtitle { font-size: 13px; color: var(--muted); margin-top: 4px; }

        /* ── Cards ── */
        .admin-card {
            background: linear-gradient(145deg, rgba(18,44,77,0.88), rgba(7,20,38,0.94));
            border: 1px solid var(--line);
            border-radius: 30px; padding: 28px; margin-bottom: 24px;
            backdrop-filter: blur(20px);
        }

        /* ── Tables ── */
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th {
            text-align: left; padding: 12px 16px;
            font-size: 10px; font-weight: 700; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.08em;
            border-bottom: 1px solid var(--line);
        }
        .admin-table td {
            padding: 14px 16px; font-size: 13px; color: var(--text);
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .admin-table tr { cursor: pointer; transition: background 0.15s ease; }
        .admin-table tr:hover td { background: rgba(255,255,255,0.03); }

        /* ── Badges ── */
        .admin-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .badge-pending { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); }
        .badge-in_progress { background: rgba(255,183,3,0.12); color: var(--yellow); border: 1px solid rgba(255,183,3,0.2); }
        .badge-completed { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .badge-submitted { background: rgba(168,85,247,0.12); color: #a855f7; border: 1px solid rgba(168,85,247,0.2); }
        .badge-cancelled { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .badge-active { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }
        .badge-inactive { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.2); }
        .badge-admin { background: rgba(139,92,246,0.12); color: #a78bfa; border: 1px solid rgba(139,92,246,0.2); }
        .badge-user { background: rgba(255,255,255,0.06); color: var(--text); border: 1px solid var(--line); }

        /* ── Stat cards ── */
        .stat-card {
            background: linear-gradient(145deg, rgba(18,44,77,0.88), rgba(7,20,38,0.94));
            border: 1px solid var(--line);
            border-radius: 18px; padding: 24px; text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 14px; }
        .stat-value { font-size: 32px; font-weight: 800; color: white; line-height: 1; }
        .stat-label { font-size: 11px; color: var(--muted); margin-top: 6px; text-transform: uppercase; letter-spacing: 0.05em; }

        /* ── Buttons (même border-radius 5px que le HTML) ── */
        .admin-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 5px; font-size: 12px; font-weight: 700;
            text-decoration: none; border: none; cursor: pointer; transition: all 0.2s ease;
        }
        .btn-primary { background: var(--yellow); color: var(--navy); }
        .btn-primary:hover { background: var(--yellow-light); transform: translateY(-1px); box-shadow: 0 8px 25px rgba(255,183,3,0.25); }
        .btn-secondary { background: rgba(255,255,255,0.06); color: var(--text); border: 1px solid var(--line); }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); color: white; }
        .btn-danger { background: rgba(239,68,68,0.12); color: #f87171; border: 1px solid rgba(239,68,68,0.15); }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }
        .btn-sm { padding: 6px 14px; font-size: 11px; }

        /* ── Inputs ── */
        .admin-input {
            width: 100%; height: 48px; padding: 0 16px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--line);
            border-radius: 13px; color: white; font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .admin-input:focus { outline: none; border-color: var(--yellow); box-shadow: 0 0 0 3px rgba(255,183,3,0.12); }
        .admin-input::placeholder { color: var(--muted); }
        select.admin-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.48)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px; }
        select.admin-input option { background: #0f172a; color: white; }
        textarea.admin-input { height: auto; min-height: 90px; padding: 12px 16px; resize: vertical; border-radius: 13px; }

        .admin-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; }

        /* ── Alerts ── */
        .admin-alert {
            padding: 14px 18px; border-radius: 12px; margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px; font-size: 13px;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }

        /* ── Pagination ── */
        .pagination { display: flex; align-items: center; gap: 6px; justify-content: center; margin-top: 24px; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px;
            font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.2s ease;
        }
        .pagination a { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5); border: 1px solid var(--line); }
        .pagination a:hover { background: rgba(255,183,3,0.1); color: var(--yellow); border-color: rgba(255,183,3,0.2); }
        .pagination .active { background: var(--yellow); color: var(--navy); border: 1px solid var(--yellow); }
        .pagination .disabled { opacity: 0.3; pointer-events: none; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .admin-sidebar { width: 0; overflow: hidden; }
            .admin-main { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="background" aria-hidden="true"></div>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset(config('admin_brand.logo')) }}" alt="{{ config('admin_brand.brand_name') }}">
                <span>{{ config('admin_brand.brand_name') }}</span>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-label">Navigation</div>
            </div>

            <nav class="sidebar-nav">
                @foreach(config('admin_brand.nav_items') as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route_pattern']) ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-role">{{ config('user_roles.roles.' . auth()->user()->role . '.label', ucfirst(auth()->user()->role)) }}</div>
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
