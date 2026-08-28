<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — {{ config('admin_brand.app_name', 'DOMINIUM') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --black: #101010;
            --black-light: #191919;
            --yellow: #ffd000;
            --yellow-light: #fff7cc;
            --white: #ffffff;
            --background: #f5f5f3;
            --text: #151515;
            --muted: #858581;
            --line: #e3e3df;
            --text-muted: #858581;
            --text-secondary: #151515;
            --blue-light: #ffd000;
            --shadow-sm: 0 1px 2px rgba(0,0,0,.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,.06);
            --shadow-lg: 0 8px 24px rgba(0,0,0,.08);
            --radius: 7px;
            --transition: all .25s cubic-bezier(.4,0,.2,1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            background: var(--background);
            color: var(--text);
            font-family: "Inter", sans-serif;
            -webkit-font-smoothing: antialiased;
            line-height: 1.6;
            font-size: 14px;
        }

        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; }

        ::selection { background: var(--yellow); color: var(--black); }

        :focus-visible { outline: 2px solid var(--yellow); outline-offset: 2px; border-radius: 3px; }

        /* ── Animations ── */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(.96); } to { opacity: 1; transform: scale(1); } }
        @keyframes progressFill { from { width: 0; } }
        @keyframes countUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes ripple { 0% { transform: scale(0); opacity: .5; } 100% { transform: scale(4); opacity: 0; } }
        @keyframes bounceIn { 0% { transform: scale(.3); opacity: 0; } 50% { transform: scale(1.05); } 70% { transform: scale(.9); } 100% { transform: scale(1); opacity: 1; } }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed; inset: 0 auto 0 0; width: 218px;
            background: var(--black); color: white;
            display: flex; flex-direction: column; z-index: 100;
            animation: fadeIn .4s ease;
            transition: width .3s cubic-bezier(.4,0,.2,1);
        }

        .brand {
            height: 70px; padding: 0 22px;
            display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid #292929;
        }

        .brand-logo {
            height: 48px; width: auto; max-width: 100%; flex-shrink: 0;
            object-fit: contain;
            display: block;
        }

        .brand-name { font-size: 14px; font-weight: 800; letter-spacing: 2px; }

        .navigation { padding: 25px 12px; }

        .nav-heading {
            padding: 0 10px; margin-bottom: 12px;
            color: #646464; font-size: 12px; font-weight: 700;
            letter-spacing: 1.2px; text-transform: uppercase;
        }

        .nav-item {
            height: 44px; display: flex; align-items: center;
            padding: 0 12px; margin-bottom: 4px; border-radius: 6px;
            color: #929292; font-size: 14px; font-weight: 600;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .nav-item::before {
            content: ""; position: absolute; inset: 0;
            background: var(--yellow); opacity: 0;
            transition: opacity .2s ease; border-radius: 6px;
        }
        .nav-item:hover::before { opacity: .08; }
        .nav-item i { width: 18px; text-align: center; margin-right: 10px; font-size: 15px; transition: transform .2s ease; position: relative; z-index: 1; }
        .nav-item span { position: relative; z-index: 1; }
        .nav-item:hover { color: white; transform: translateX(3px); }
        .nav-item:hover i { transform: scale(1.15); }
        .nav-item.active { background: var(--yellow); color: var(--black); font-weight: 800; }
        .nav-item.active::before { display: none; }
        .nav-item.active i { color: var(--black); }
        .nav-item:active { transform: scale(.97); }

        .sidebar-footer {
            margin-top: auto; padding: 16px;
            border-top: 1px solid #292929;
        }

        .user { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 6px; transition: background .2s ease; cursor: pointer; }
        .user:hover { background: #1b1b1b; }

        .avatar {
            width: 32px; height: 32px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; background: var(--yellow);
            color: black; font-size: 12px; font-weight: 800;
            transition: transform .2s ease;
        }
        .user:hover .avatar { transform: scale(1.1); }

        .user-name { font-size: 13px; font-weight: 700; }
        .user-role { margin-top: 2px; color: #686868; font-size: 11px; }

        /* ── Main ── */
        .main { margin-left: 218px; padding: 0 36px 50px; }

        /* ── Topbar ── */
        .topbar {
            height: 70px; display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; background: var(--background); z-index: 50;
            backdrop-filter: blur(8px);
        }
        .breadcrumb { color: #999; font-size: 13px; font-weight: 500; }
        .breadcrumb strong { color: var(--text); }

        .top-right { display: flex; align-items: center; gap: 10px; }
        .top-btn {
            height: 34px; padding: 0 14px;
            border: 1px solid var(--line); background: white;
            border-radius: 5px; color: #666;
            font-size: 13px; font-weight: 600; cursor: pointer;
            transition: var(--transition);
            position: relative; overflow: hidden;
        }
        .top-btn:hover { background: var(--background); transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .top-btn:active { transform: translateY(0); }
        .top-btn.primary { border-color: var(--yellow); background: var(--yellow); color: var(--black); font-weight: 700; }
        .top-btn.primary:hover { background: #e6bb00; box-shadow: 0 4px 12px rgba(255,208,0,.3); }

        /* ── Page Intro ── */
        .intro {
            padding: 30px 0 26px;
            display: flex; justify-content: space-between; align-items: flex-end;
            animation: slideUp .5s ease;
        }
        .intro-label {
            display: flex; align-items: center; gap: 7px;
            margin-bottom: 8px; color: var(--muted);
            font-size: 12px; font-weight: 700;
            letter-spacing: 1px; text-transform: uppercase;
        }
        .intro-label span {
            width: 6px; height: 6px; border-radius: 50%; background: var(--yellow);
            animation: pulse 2s ease infinite;
        }
        .intro h1 { font-size: 40px; line-height: 1; letter-spacing: -2px; font-weight: 800; }
        .intro p { margin-top: 8px; color: var(--muted); font-size: 15px; }

        /* ── Cards ── */
        .card {
            background: white; border: 1px solid var(--line);
            border-radius: var(--radius); overflow: hidden;
            transition: var(--transition);
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-header {
            height: 64px; padding: 0 22px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 1px solid var(--line);
        }
        .card-title { font-size: 18px; font-weight: 800; letter-spacing: -.3px; }
        .card-subtitle { margin-top: 3px; color: #999; font-size: 13px; }
        .view-all { font-size: 13px; font-weight: 700; color: #555; transition: var(--transition); padding: 5px 10px; border-radius: 4px; }
        .view-all:hover { color: black; background: var(--yellow-light); }

        /* ── Tables ── */
        .table-head, .table-row {
            display: grid; grid-template-columns: 1.6fr 1.25fr .7fr .75fr 52px; align-items: center;
        }
        .table-head {
            height: 40px; padding: 0 22px;
            background: #fafaf8; border-bottom: 1px solid var(--line);
            color: #999; font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .7px;
        }
        .table-row {
            min-height: 76px; padding: 0 22px;
            border-bottom: 1px solid var(--line);
            transition: var(--transition);
            animation: fadeIn .4s ease backwards;
        }
        .table-row:nth-child(2) { animation-delay: .05s; }
        .table-row:nth-child(3) { animation-delay: .1s; }
        .table-row:nth-child(4) { animation-delay: .15s; }
        .table-row:nth-child(5) { animation-delay: .2s; }
        .table-row:nth-child(6) { animation-delay: .25s; }
        .table-row:nth-child(7) { animation-delay: .3s; }
        .table-row:nth-child(8) { animation-delay: .35s; }
        .table-row:nth-child(9) { animation-delay: .4s; }
        .table-row:nth-child(10) { animation-delay: .45s; }
        .table-row:last-child { border-bottom: 0; }
        .table-row:hover { background: #fffdf3; transform: translateX(2px); }

        .client { display: flex; align-items: center; gap: 12px; }
        .client-avatar {
            width: 34px; height: 34px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: var(--black); color: white;
            font-size: 11px; font-weight: 800; border-radius: 5px;
            transition: var(--transition);
        }
        .table-row:hover .client-avatar { transform: scale(1.08); background: var(--yellow); color: var(--black); }
        .client-name { font-size: 14px; font-weight: 800; }
        .client-email { margin-top: 3px; color: #aaa; font-size: 12px; }

        .project { color: #333; font-size: 14px; font-weight: 600; }
        .date { color: #888; font-size: 13px; }

        .status {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 700;
        }
        .status::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--yellow); }
        .status.done::before { background: #111; }

        .open {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--line); border-radius: 5px;
            background: white; font-size: 14px;
            transition: var(--transition);
        }
        .open:hover { background: var(--yellow); border-color: var(--yellow); transform: scale(1.1) rotate(-5deg); }
        .open:active { transform: scale(.95); }

        /* ── Stat strip ── */
        .stats {
            display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr;
            background: white; border: 1px solid var(--line);
            border-radius: var(--radius); overflow: hidden;
            animation: slideUp .5s ease .1s backwards;
        }
        .stat {
            min-height: 120px; padding: 22px; border-right: 1px solid var(--line);
            transition: background .2s ease;
        }
        .stat:last-child { border-right: 0; }
        .stat.main-stat { background: var(--yellow); }
        .stat:hover:not(.main-stat) { background: #fafaf8; }
        .stat-label { color: #777; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; }
        .main-stat .stat-label { color: #4d4500; }
        .stat-number {
            margin-top: 20px; font-size: 42px; line-height: .9; letter-spacing: -2px; font-weight: 800;
            animation: countUp .6s ease .2s backwards;
        }
        .stat-info { margin-top: 8px; color: #999; font-size: 13px; }
        .main-stat .stat-info { color: #514900; }

        /* ── Content grid ── */
        .content {
            display: grid; grid-template-columns: minmax(0, 1fr) 300px;
            gap: 18px; margin-top: 22px;
            animation: slideUp .5s ease .2s backwards;
        }

        /* ── Right column ── */
        .side-section { padding: 22px; border-bottom: 1px solid var(--line); }
        .side-section:last-child { border-bottom: 0; }
        .side-heading { margin-bottom: 18px; font-size: 16px; font-weight: 800; }

        .completion-number { display: flex; justify-content: space-between; align-items: flex-end; }
        .completion-number strong { font-size: 30px; letter-spacing: -1.5px; font-weight: 800; }
        .completion-number span { color: #999; font-size: 13px; }

        .progress { height: 6px; margin-top: 14px; overflow: hidden; background: #eeeeeb; border-radius: 10px; }
        .progress span { display: block; height: 100%; background: var(--yellow); border-radius: 10px; animation: progressFill 1s ease .4s backwards; }

        .activity {
            display: flex; gap: 10px; margin-bottom: 14px;
            padding: 10px; border-radius: 5px;
            transition: var(--transition);
        }
        .activity:last-child { margin-bottom: 0; }
        .activity:hover { background: #fafaf8; }
        .activity-dot { width: 7px; height: 7px; flex-shrink: 0; margin-top: 4px; border-radius: 50%; background: var(--yellow); }
        .activity-title { font-size: 13px; line-height: 1.5; font-weight: 700; }
        .activity-time { margin-top: 3px; color: #aaa; font-size: 12px; }

        .quick-action {
            display: block; padding: 14px;
            background: var(--black); color: white;
            border-radius: 5px; font-size: 14px; font-weight: 700;
            transition: var(--transition); text-align: center;
            position: relative; overflow: hidden;
        }
        .quick-action::before {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,.1), transparent);
            transform: translateX(-100%); transition: transform .5s ease;
        }
        .quick-action:hover::before { transform: translateX(100%); }
        .quick-action:hover { background: var(--yellow); color: black; transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .quick-action:active { transform: translateY(0); }

        /* ── Alerts ── */
        .admin-alert {
            padding: 14px 18px; border-radius: 5px; margin-top: 22px;
            display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;
            animation: slideUp .3s ease;
        }
        .alert-success { background: var(--yellow); border: 1px solid var(--yellow); color: var(--black); }
        .alert-error { background: var(--black); border: 1px solid var(--black); color: var(--white); }

        /* ── Pagination ── */
        .pagination { display: flex; align-items: center; gap: 6px; justify-content: center; margin-top: 22px; }
        .pagination a, .pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 38px; height: 38px; padding: 0 14px; border-radius: 6px;
            font-size: 13px; font-weight: 600; text-decoration: none; transition: var(--transition);
        }
        .pagination a { background: white; color: var(--text); border: 1px solid var(--line); }
        .pagination a:hover { background: var(--yellow); border-color: var(--yellow); color: var(--black); transform: translateY(-1px); }
        .pagination .active { background: var(--yellow); color: var(--black); border: 1px solid var(--yellow); font-weight: 800; }
        .pagination .disabled { opacity: 0.3; pointer-events: none; }

        /* ── Badges ── */
        .admin-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 5px; font-size: 13px; font-weight: 700;
            transition: var(--transition);
        }
        .admin-badge:hover { transform: scale(1.05); }
        .badge-pending { background: var(--yellow); color: var(--black); }
        .badge-in_progress { background: var(--yellow); color: var(--black); }
        .badge-completed { background: var(--black); color: var(--white); }
        .badge-submitted { background: var(--black); color: var(--white); }
        .badge-cancelled { background: var(--line); color: var(--muted); }
        .badge-active { background: var(--black); color: var(--white); }
        .badge-inactive { background: var(--line); color: var(--muted); }
        .badge-admin { background: var(--yellow); color: var(--black); }
        .badge-user { background: var(--line); color: var(--text); }

        /* ── Admin inputs/labels ── */
        .admin-input {
            width: 100%; height: 42px; padding: 0 16px;
            background: white; border: 1px solid var(--line);
            border-radius: 5px; color: var(--text); font-size: 14px; font-family: inherit;
            transition: var(--transition);
        }
        .admin-input:focus { outline: none; border-color: var(--yellow); box-shadow: 0 0 0 3px rgba(255,208,0,.15); }
        .admin-input::placeholder { color: var(--muted); }
        select.admin-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23858581' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
        select.admin-input option { background: white; color: var(--text); font-size: 14px; }
        textarea.admin-input { height: auto; min-height: 90px; padding: 12px 16px; resize: vertical; }

        .admin-label { display: block; margin-bottom: 7px; font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .7px; }

        /* ── Admin btn ── */
        .admin-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 18px; border-radius: 5px; font-size: 14px; font-weight: 700;
            text-decoration: none; border: 1px solid var(--line); background: white;
            color: #666; cursor: pointer; transition: var(--transition);
            position: relative; overflow: hidden;
        }
        .admin-btn i { font-size: 12px; transition: transform .2s ease; }
        .admin-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .admin-btn:active { transform: translateY(0); }
        .btn-primary { border-color: var(--yellow); background: var(--yellow); color: var(--black); }
        .btn-primary:hover { background: #e6bb00; box-shadow: 0 4px 12px rgba(255,208,0,.3); }
        .btn-primary:hover i { transform: rotate(90deg); }
        .btn-secondary { background: white; color: #666; border: 1px solid var(--line); }
        .btn-secondary:hover { background: var(--background); }
        .btn-danger { background: white; color: #666; border: 1px solid var(--line); }
        .btn-danger:hover { background: var(--black); color: white; border-color: var(--black); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* ── Admin stat cards ── */
        .stat-card {
            background: white; border: 1px solid var(--line);
            border-radius: var(--radius); padding: 22px; text-align: center;
            transition: var(--transition);
        }
        .stat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
        .stat-icon { width: 44px; height: 44px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; margin-bottom: 14px; background: var(--yellow); color: var(--black); transition: var(--transition); }
        .stat-card:hover .stat-icon { transform: scale(1.1) rotate(-5deg); }
        .stat-value { font-size: 36px; font-weight: 800; color: var(--text); line-height: .9; letter-spacing: -2px; }
        .stat-label { font-size: 12px; color: #777; margin-top: 8px; text-transform: uppercase; letter-spacing: .7px; font-weight: 700; }

        /* ── Admin card ── */
        .admin-card {
            background: white; border: 1px solid var(--line);
            border-radius: var(--radius); overflow: hidden; margin-bottom: 18px;
            transition: var(--transition);
        }
        .admin-card:hover { box-shadow: var(--shadow-md); }

        /* ── Admin header ── */
        .admin-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 0;
        }
        .admin-title { font-size: 40px; line-height: 1; letter-spacing: -2px; font-weight: 800; }
        .admin-subtitle { margin-top: 8px; color: var(--muted); font-size: 15px; }

        /* ── Admin table ── */
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th {
            text-align: left; padding: 12px 22px;
            font-size: 12px; font-weight: 700; color: #999;
            text-transform: uppercase; letter-spacing: .7px;
            background: #fafaf8; border-bottom: 1px solid var(--line);
        }
        .admin-table td {
            padding: 16px 22px; font-size: 14px; color: var(--text);
            border-bottom: 1px solid var(--line);
            transition: background .15s ease;
        }
        .admin-table tr:last-child td { border-bottom: 0; }
        .admin-table tr { transition: background .15s ease; }
        .admin-table tr:hover td { background: #fffdf3; }

        /* ── Empty state ── */
        .empty-state {
            text-align: center; padding: 52px 22px;
            animation: fadeIn .5s ease;
        }
        .empty-state i {
            font-size: 28px; color: var(--line); margin-bottom: 14px; display: block;
        }
        .empty-state p { color: var(--muted); font-size: 14px; }

        /* ── Tooltips ── */
        [data-tooltip] { position: relative; }
        [data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
            padding: 6px 10px; background: var(--black); color: white;
            font-size: 12px; font-weight: 600; white-space: nowrap;
            border-radius: 4px; margin-bottom: 8px;
            animation: fadeIn .15s ease;
        }

        /* ── Skeleton loading ── */
        .skeleton {
            background: linear-gradient(90deg, #eee 25%, #e0e0e0 50%, #eee 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 4px;
        }

        /* ── Responsive ── */
        @media (max-width: 1050px) {
            .main { padding-left: 25px; padding-right: 25px; }
        }
        @media (max-width: 800px) {
            .sidebar { width: 65px; }
            .brand { justify-content: center; padding: 0; }
            .brand-logo { height: 34px; max-width: 50px; }
            .brand-name, .nav-heading, .nav-item span, .user-info { display: none; }
            .navigation { padding-left: 8px; padding-right: 8px; }
            .nav-item { justify-content: center; padding: 0; }
            .nav-item i { margin-right: 0; }
            .main { margin-left: 65px; padding-left: 18px; padding-right: 18px; }
            .stats { grid-template-columns: 1fr 1fr; }
            .stat { border-bottom: 1px solid var(--line); }
            .stat:nth-child(2) { border-right: 0; }
            .stat:last-child { border-bottom: 0; }
            .top-right { display: none; }
            .content { grid-template-columns: 1fr; }
        }
        @media (max-width: 550px) {
            .intro h1 { font-size: 28px; }
            .stats { grid-template-columns: 1fr; }
            .stat { border-right: 0; border-bottom: 1px solid var(--line); }
            .stat:last-child { border-bottom: 0; }
            .table-head { display: none; }
            .table-row { grid-template-columns: 1fr 40px; padding: 15px 17px; }
            .table-row > .project, .table-row > .date, .table-row > .status { display: none; }
            .open { grid-column: 2; grid-row: 1; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <img src="{{ asset(config('admin_brand.logo', 'IMAGES/Logo dominium.png')) }}"
             alt="{{ config('admin_brand.brand_name', 'DOMINIUM') }}" class="brand-logo">
        <div class="brand-name">{{ config('admin_brand.brand_name', 'DOMINIUM') }}</div>
    </div>

    <nav class="navigation">
        <div class="nav-heading">Administration</div>
        @foreach(config('admin_brand.nav_items') as $item)
            <a href="{{ route($item['route']) }}" class="nav-item {{ request()->routeIs($item['route_pattern']) ? 'active' : '' }}">
                <i class="{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ config('user_roles.roles.' . auth()->user()->role . '.label', ucfirst(auth()->user()->role)) }}</div>
            </div>
        </div>
    </div>
</aside>

<main class="main">
    <header class="topbar">
        <div class="breadcrumb">
            {{ config('admin_brand.brand_name', 'DOMINIUM') }} / <strong>{{ $title ?? 'Dashboard' }}</strong>
        </div>
        <div class="top-right">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="top-btn">Déconnexion</button>
            </form>
        </div>
    </header>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.admin-table tr[onclick], .table-row[onclick]').forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('mouseenter', function() {
            this.style.background = '#fffdf3';
        });
        row.addEventListener('mouseleave', function() {
            this.style.background = '';
        });
    });

    document.querySelectorAll('.admin-alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'all .3s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-8px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });

    document.querySelectorAll('.stat-number').forEach(function(el) {
        var target = parseInt(el.textContent) || 0;
        if (target === 0) return;
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 30));
        var timer = setInterval(function() {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current;
        }, 30);
    });

    document.querySelectorAll('form[onsubmit], form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.style.opacity = '.6';
                btn.style.pointerEvents = 'none';
            }
        });
    });
});
</script>

</body>
</html>
