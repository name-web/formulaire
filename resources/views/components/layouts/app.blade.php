<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('Questionnaire') }} — DOMINIUM TECH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --yellow: #ffb703;
            --yellow-light: #ffc72c;
            --navy: #071426;
            --navy-2: #0b1b31;
            --blue: #122c4d;
            --blue-light: #193b65;
            --bg-dark: #071426;
            --bg-card: #0b1b31;
            --white: #ffffff;
            --text-muted: #91a1b5;
            --text-secondary: #e6edf5;
            --border: rgba(255,255,255,0.12);
            --accent-surface: rgba(255,183,3,0.12);
            --accent-light: #ffb703;
            --radius: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--white);
            background: linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ============================================
           BACKGROUND EFFECTS
        ============================================ */
        .background {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        .curved-pattern {
            position: absolute;
            inset: 0;
            opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E");
            background-size: 200px 100px;
        }
        .grid-pattern {
            position: absolute;
            inset: 0;
            opacity: 0.025;
            background-image:
                linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px);
            background-size: 80px 80px;
        }
        .orb { position: absolute; border-radius: 50%; filter: blur(90px); }
        .orb-1 {
            width: 500px; height: 500px; top: -180px; right: -100px;
            background: radial-gradient(circle, rgba(255,183,3,0.15) 0%, transparent 70%);
            animation: orbFloat1 9s ease-in-out infinite;
        }
        .orb-2 {
            width: 450px; height: 450px; bottom: -200px; left: 15%;
            background: radial-gradient(circle, rgba(255,183,3,0.08) 0%, transparent 70%);
            animation: orbFloat2 11s ease-in-out infinite;
        }
        .orb-3 {
            width: 300px; height: 300px; top: 35%; left: 50%;
            background: radial-gradient(circle, rgba(255,183,3,0.06) 0%, transparent 70%);
        }
        @keyframes orbFloat1 { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,25px) scale(1.05); } }
        @keyframes orbFloat2 { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(25px,-25px) scale(1.08); } }

        .hero-snow { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .hero-snow span { position: absolute; top: -10px; width: 3px; height: 3px; border-radius: 50%; background: rgba(255,255,255,0.4); animation: snowFall linear infinite; }
        .hero-snow span:nth-child(1) { left: 5%; animation-duration: 12s; animation-delay: 0s; width: 2px; height: 2px; opacity: 0.3; }
        .hero-snow span:nth-child(2) { left: 12%; animation-duration: 14s; animation-delay: 1s; opacity: 0.25; }
        .hero-snow span:nth-child(3) { left: 20%; animation-duration: 10s; animation-delay: 3s; width: 2px; height: 2px; opacity: 0.35; }
        .hero-snow span:nth-child(4) { left: 28%; animation-duration: 16s; animation-delay: 0.5s; opacity: 0.2; }
        .hero-snow span:nth-child(5) { left: 35%; animation-duration: 11s; animation-delay: 2s; opacity: 0.3; }
        .hero-snow span:nth-child(6) { left: 42%; animation-duration: 13s; animation-delay: 4s; width: 2px; height: 2px; opacity: 0.25; }
        .hero-snow span:nth-child(7) { left: 50%; animation-duration: 15s; animation-delay: 1.5s; opacity: 0.3; }
        .hero-snow span:nth-child(8) { left: 58%; animation-duration: 10s; animation-delay: 3.5s; opacity: 0.2; }
        .hero-snow span:nth-child(9) { left: 65%; animation-duration: 14s; animation-delay: 0.8s; width: 2px; height: 2px; opacity: 0.35; }
        .hero-snow span:nth-child(10) { left: 72%; animation-duration: 12s; animation-delay: 2.5s; opacity: 0.25; }
        .hero-snow span:nth-child(11) { left: 78%; animation-duration: 16s; animation-delay: 4.5s; opacity: 0.2; }
        .hero-snow span:nth-child(12) { left: 85%; animation-duration: 11s; animation-delay: 1.2s; width: 2px; height: 2px; opacity: 0.3; }
        .hero-snow span:nth-child(13) { left: 90%; animation-duration: 13s; animation-delay: 3.2s; opacity: 0.25; }
        .hero-snow span:nth-child(14) { left: 15%; animation-duration: 15s; animation-delay: 5s; opacity: 0.2; }
        .hero-snow span:nth-child(15) { left: 55%; animation-duration: 12s; animation-delay: 0.3s; opacity: 0.3; }
        @keyframes snowFall { 0% { transform: translateY(-10px) translateX(0) rotate(0deg); opacity: 0; } 10% { opacity: 1; } 90% { opacity: 1; } 100% { transform: translateY(100vh) translateX(60px) rotate(360deg); opacity: 0; } }

        .hero-shape { position: absolute; border-radius: 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); }
        .hero-shape-1 { width: 80px; height: 80px; top: 15%; right: 20%; animation: shapeRotate 20s linear infinite, shapeFade 6s ease-in-out infinite; }
        .hero-shape-2 { width: 60px; height: 60px; bottom: 25%; right: 30%; animation: shapeRotate 25s linear infinite reverse, shapeFade 8s ease-in-out infinite 2s; }
        .hero-shape-3 { width: 40px; height: 40px; top: 60%; right: 10%; animation: shapeRotate 15s linear infinite, shapeFade 7s ease-in-out infinite 1s; }
        @keyframes shapeRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shapeFade { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.8; } }

        .hero-ring { position: absolute; border-radius: 50%; border: 1px solid rgba(255,255,255,0.06); }
        .hero-ring-1 { width: 200px; height: 200px; top: 20%; right: 15%; animation: ringPulse2 6s ease-in-out infinite; }
        .hero-ring-2 { width: 120px; height: 120px; bottom: 30%; left: 55%; animation: ringPulse2 8s ease-in-out infinite 2s; }
        @keyframes ringPulse2 { 0%, 100% { transform: scale(1); opacity: 0.3; } 50% { transform: scale(1.1); opacity: 0.6; } }

        .hero-line { position: absolute; height: 1px;            background: linear-gradient(90deg, transparent, rgba(255,183,3,0.15), transparent);
        }
        .hero-line-1 { width: 300px; top: 30%; right: 5%; animation: lineSlide 8s ease-in-out infinite; }
        .hero-line-2 { width: 200px; bottom: 35%; left: 20%; animation: lineSlide 10s ease-in-out infinite 3s; transform: rotate(-15deg); }
        @keyframes lineSlide { 0%, 100% { transform: translateX(0); opacity: 0.3; } 50% { transform: translateX(40px); opacity: 0.7; } }

        /* ============================================
           SIDEBAR
        ============================================ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 300px;
            padding: 34px 28px;
            background: rgba(7,20,38,0.92);
            border-right: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            margin-bottom: 24px;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sidebar-logo img {
            height: 90px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(255,183,3,0.25));
        }
        .sidebar-logo span {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
        }

        .sidebar-label {
            margin-bottom: 18px;
            color: rgba(255,255,255,0.32);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.8px;
        }

        .step-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            overflow-y: auto;
        }
        .step-list::-webkit-scrollbar { width: 3px; }
        .step-list::-webkit-scrollbar-track { background: transparent; }
        .step-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .step-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 54px;
            padding: 9px 10px;
            border-radius: 13px;
            color: rgba(255,255,255,0.35);
            transition: background 0.25s ease, color 0.25s ease;
            cursor: default;
        }
        .step-dot {
            width: 33px;
            height: 33px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.35);
            font-size: 10px;
            font-weight: 800;
            transition: all 0.25s ease;
        }
        .step-label {
            font-size: 13px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .step-item:not(:last-child)::after {
            content: "";
            position: absolute;
            left: 26px;
            top: 43px;
            width: 1px;
            height: 17px;
            background: rgba(255,255,255,0.08);
        }

        .step-item.done {
            color: rgba(255,255,255,0.65);
        }
        .step-item.done .step-dot {
            background: rgba(255,183,3,0.12);
            border-color: rgba(255,183,3,0.18);
            color: var(--yellow);
        }

        .step-item.active {
            background: linear-gradient(90deg, rgba(255,183,3,0.14), rgba(255,183,3,0.04));
            color: white;
            border: 1px solid rgba(255,183,3,0.12);
        }
        .step-item.active .step-dot {
            background: var(--yellow);
            border-color: var(--yellow);
            color: #071426;
            box-shadow: 0 0 20px rgba(255,183,3,0.25);
            animation: dotPulse 2s ease-in-out infinite;
        }
        @keyframes dotPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(255,183,3,0.25); }
            50% { box-shadow: 0 0 30px rgba(255,183,3,0.4); }
        }

        .step-arrow {
            margin-left: auto;
            font-size: 9px;
            color: var(--yellow);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .step-item.active .step-arrow { opacity: 1; }

        .progress-section {
            margin-top: auto;
        }
        .progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .progress-label {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
        }
        .progress-value {
            font-size: 11px;
            color: var(--yellow);
            font-weight: 700;
        }
        .progress-bar-track {
            height: 5px;
            width: 100%;
            overflow: hidden;
            border-radius: 20px;
            background: rgba(255,255,255,0.08);
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #e6a400, #ffb703, #ffc72c);
            box-shadow: 0 0 15px rgba(255,183,3,0.4);
            transition: width 0.6s cubic-bezier(0.16,1,0.3,1);
            position: relative;
            overflow: hidden;
        }
        .progress-bar-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 2s ease-in-out infinite;
        }
        @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        .progress-step-text {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
            margin-top: 8px;
        }
        .progress-step-text strong {
            color: rgba(255,255,255,0.65);
        }

        /* ============================================
           MAIN
        ============================================ */
        .main-content {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            margin-left: 300px;
            padding: 40px 55px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content-card {
            position: relative;
            width: 100%;
            max-width: 950px;
            min-height: 650px;
            padding: 55px 60px;
            border-radius: 30px;
            background: linear-gradient(145deg, rgba(18,44,77,0.88), rgba(7,20,38,0.94));
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 35px 100px rgba(0,0,0,0.35), 0 0 80px rgba(255,183,3,0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            animation: cardFloat 6s ease-in-out infinite;
        }
        @keyframes cardFloat {
            0%, 100% { box-shadow: 0 35px 100px rgba(0,0,0,0.35), 0 0 80px rgba(255,183,3,0.06); }
            50% { box-shadow: 0 35px 100px rgba(0,0,0,0.35), 0 0 120px rgba(255,183,3,0.1); }
        }

        /* ============================================
           PROJECT BADGE (sidebar)
        ============================================ */
        .project-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .project-badge-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 10px;
            background: rgba(255,183,3,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--yellow);
        }
        .project-badge-label {
            font-size: 10px;
            font-weight: 700;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 2px;
        }
        .project-badge-value {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
        }

        /* ============================================
           SELECT DROPDOWN ARROW
        ============================================ */
        .input-icon-wrap select.glass-input {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.48)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        /* ============================================
           CARD TOP
        ============================================ */
        .top, .card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .eyebrow, .question-badge {
            color: var(--yellow);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            animation: eyebrowGlow 3s ease-in-out infinite;
        }
        @keyframes eyebrowGlow {
            0%, 100% { text-shadow: 0 0 0 transparent; }
            50% {            text-shadow: 0 0 15px rgba(255,183,3,0.3); }
        }
        .counter, .counter-circle {
            min-width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            padding: 0 12px;
            border-radius: 29px;
            background: rgba(255,183,3,0.1);
            border: 1px solid rgba(255,183,3,0.18);
            color: var(--yellow);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            animation: counterPulse 3s ease-in-out infinite;
            position: relative;
        }
        @keyframes counterPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,183,3,0); }
            50% { box-shadow: 0 0 20px 4px rgba(255,183,3,0.15); }
        }

        /* Card top progress bar */
        .card-progress-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            overflow: hidden;
            border-radius: 30px 30px 0 0;
            background: rgba(255,255,255,0.05);
        }
        .card-progress-top-fill {
            height: 100%;
            border-radius: 30px 30px 0 0;
            background: linear-gradient(90deg, #e6a400, #ffb703, #ffc72c);
            transition: width 0.6s cubic-bezier(0.16,1,0.3,1);
        }

        /* ============================================
           QUESTION
        ============================================ */
        .question-title {
            max-width: 750px;
            margin: 32px 0 16px;
            font-size: clamp(40px, 5vw, 64px);
            line-height: 1;
            letter-spacing: -2.5px;
            color: white;
            font-weight: 700;
        }
        .question-title .req { color: #f87171; margin-left: 2px; }
        .question-desc {
            font-size: 15px;
            color: var(--text-muted);
            line-height: 1.7;
            margin: 0 0 24px;
        }

        /* ============================================
           OPTION CARDS
        ============================================ */
        .option-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            max-width: 780px;
        }
        .option-grid.single { grid-template-columns: 1fr; }

        .opt-card {
            position: relative;
            padding: 29px;
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 18px;
            background: rgba(255,255,255,0.035);
            color: rgba(255,255,255,0.8);
            font-size: 17px;
            font-weight: 700;
            text-align: left;
            cursor: pointer;
            transition: transform 0.25s ease, border 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
            user-select: none;
            overflow: hidden;
        }
        .opt-card::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,183,3,0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.5s ease, height 0.5s ease;
        }
        .opt-card:active::before {
            width: 300px;
            height: 300px;
        }
        .opt-card:hover {
            transform: translateY(-4px);
            background: rgba(255,183,3,0.06);
            border-color: rgba(255,183,3,0.35);
        }
        .opt-card.selected {
            transform: translateY(-4px);
            background: linear-gradient(135deg, rgba(255,183,3,0.14), rgba(255,183,3,0.04));
            border-color: rgba(255,183,3,0.7);
            color: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.18), 0 0 25px rgba(255,183,3,0.08);
        }
        .opt-check {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: var(--yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0);
            transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
            color: white;
            font-size: 10px;
        }
        .opt-card.selected .opt-check { opacity: 1; transform: scale(1); }

        .opt-icon-wrap {
            display: inline-flex;
            margin-right: 10px;
            color: var(--yellow);
            font-size: 17px;
        }

        .opt-label { font-size: 17px; font-weight: 700; }
        .opt-desc { display: none; }

        /* Yes/No */
        .yesno-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 28px 16px;
        }
        .yesno-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: transform 0.2s;
        }
        .opt-card:hover .yesno-icon,
        .opt-card.selected .yesno-icon { transform: scale(1.1); }

        /* ============================================
           CARD BOTTOM
        ============================================ */
        .card-bottom, .bottom {
            margin-top: auto;
            padding-top: 24px;
            display: flex;
            align-items: center;
            gap: 25px;
        }
        .card-progress, .progress {
            flex: 1;
            height: 6px;
            overflow: hidden;
            border-radius: 20px;
            background: rgba(255,255,255,0.08);
        }
        .card-progress-fill, .card-progress span, .progress span {
            display: block;
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, #e6a400, #ffb703, #ffc72c);
            transition: width 0.6s cubic-bezier(0.16,1,0.3,1);
        }
        .btn-continue, .continue {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 25px;
            border: none;
            border-radius: 5px;
            background: #ffb703;
            color: #071426;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }
        .btn-continue::after, .continue::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        .btn-continue:hover::after, .continue:hover::after {
            transform: translateX(100%);
        }
        .btn-continue:hover, .continue:hover {
            background: #ffc72c;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255,183,3,0.25);
        }
        .btn-continue:active, .continue:active {
            transform: translateY(0) scale(0.97);
            box-shadow: 0 4px 15px rgba(255,183,3,0.2);
        }
        .btn-continue:hover i, .continue:hover i {
            transform: translateX(4px);
            transition: transform 0.25s ease;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 25px;
            border-radius: 13px;
            font-size: 14px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.25s ease;
            text-decoration: none;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-color: rgba(255,255,255,0.15);
            transform: translateX(-2px);
        }
        .btn-back:active {
            transform: translateX(0) scale(0.97);
        }

        /* ============================================
           FILE DROP ZONE
        ============================================ */
        .dropzone {
            border: 2px dashed rgba(255,255,255,0.12);
            border-radius: 18px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            background: rgba(255,255,255,0.03);
        }
        .dropzone:hover {
            border-color: rgba(255,183,3,0.4);
            background: rgba(255,183,3,0.04);
        }
        .dropzone.drag-over {
            border-color: var(--yellow);
            border-style: solid;
            background: rgba(255,183,3,0.08);
        }
        .dropzone-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(255,183,3,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 22px;
            color: var(--yellow);
            transition: transform 0.2s;
        }
        .dropzone:hover .dropzone-icon { transform: translateY(-3px) scale(1.05); }
        .dropzone.drag-over .dropzone-icon { animation: iconBounce 0.5s ease; }
        @keyframes iconBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .dropzone-title { font-size: 14px; font-weight: 600; color: rgba(255,255,255,0.85); margin-bottom: 4px; }
        .dropzone-sub { font-size: 12px; color: var(--text-muted); }
        .dropzone-formats { display: flex; justify-content: center; gap: 6px; margin-top: 12px; }
        .format-tag {
            font-size: 9px; font-weight: 700; color: var(--text-muted);
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            padding: 3px 10px; border-radius: 6px;
        }

        /* ============================================
           INPUTS
        ============================================ */
        .glass-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 13px;
            color: white;
            padding: 0 16px;
            height: 48px;
            font-size: 14px;
            transition: all 0.25s;
            width: 100%;
        }
        .glass-input:hover {
            border-color: rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.07);
        }
        .glass-input:focus {
            outline: none;
            border-color: var(--yellow);
            box-shadow: 0 0 0 3px rgba(255,183,3,0.15), 0 0 20px rgba(255,183,3,0.08);
            animation: inputGlow 2s ease-in-out infinite;
        }
        @keyframes inputGlow {
            0%, 100% { box-shadow: 0 0 0 3px rgba(255,183,3,0.15), 0 0 20px rgba(255,183,3,0.08); }
            50% { box-shadow: 0 0 0 3px rgba(255,183,3,0.2), 0 0 30px rgba(255,183,3,0.12); }
        }
        .glass-input::placeholder { color: var(--text-muted); }
        .glass-input option { background: #0f172a; color: white; }

        .input-icon-wrap { position: relative; }
        .input-icon-wrap .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }
        .input-icon-wrap .glass-input { padding-left: 40px; }
        .input-icon-wrap:focus-within .input-icon {            color: var(--yellow); }

        .textarea-wrap { position: relative; }
        .textarea-wrap textarea { min-height: 120px; padding-bottom: 28px; resize: vertical; }
        .char-counter {
            position: absolute; bottom: 10px; right: 14px;
            font-size: 10px; color: var(--text-muted); pointer-events: none;
        }
        .textarea-wrap:focus-within .char-counter {            color: var(--yellow); }

        .form-label {
            font-size: 10px; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 6px; display: block;
        }

        /* ============================================
           ERROR
        ============================================ */
        .error-box {
            padding: 14px 16px; border-radius: 13px;
            background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.15);
            margin-bottom: 24px; word-break: break-word;
        }
        .error-header { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
        .error-header i { color: #f87171; font-size: 14px; }
        .error-header span { font-size: 10px; font-weight: 700; color: #f87171; text-transform: uppercase; letter-spacing: 0.06em; }
        .error-msg { font-size: 12px; color: #fca5a5; padding-left: 20px; display: flex; align-items: center; gap: 6px; }
        .error-msg::before { content: ''; width: 3px; height: 3px; border-radius: 50%; background: #fca5a5; flex-shrink: 0; }

        /* ============================================
           WIZARD
        ============================================ */
        .wizard-step { display: none; opacity: 0; }
        .wizard-step.active { display: block; animation: wizardIn 0.5s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes wizardIn {
            from { opacity: 0; transform: translateY(24px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ============================================
           ANIMATIONS
        ============================================ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.97); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes pulseRing {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }
        .anim-up { animation: fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) forwards; }
        .anim-scale { animation: scaleIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards; }
        .anim-slide-in { animation: slideInRight 0.4s cubic-bezier(0.16,1,0.3,1) forwards; }
        .d1 { animation-delay: 50ms; opacity: 0; }
        .d2 { animation-delay: 100ms; opacity: 0; }
        .d3 { animation-delay: 150ms; opacity: 0; }
        .d4 { animation-delay: 200ms; opacity: 0; }
        .d5 { animation-delay: 250ms; opacity: 0; }

        /* ============================================
           SCROLLBAR
        ============================================ */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        /* ============================================
           MOBILE
        ============================================ */
        @media (max-width: 1024px) {
            .sidebar { width: 240px; padding: 24px 20px; }
            .sidebar-logo img { height: 60px; }
            .step-item { min-height: 44px; padding: 7px 8px; }
            .step-dot { width: 30px; height: 30px; font-size: 9px; }
            .step-label { font-size: 12px; }
            .main-content { margin-left: 240px; padding: 30px 35px; }
            .content-card { padding: 40px 45px; }
            .question-title { font-size: clamp(32px, 4.5vw, 52px); }
        }
        @media (max-width: 750px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 16px;
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }
            .sidebar-header { margin-bottom: 12px; }
            .sidebar-label { display: none; }
            .step-list {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                gap: 4px;
            }
            .step-item {
                padding: 0;
                min-height: auto;
                background: transparent !important;
                border: none !important;
            }
            .step-item:not(:last-child)::after { display: none; }
            .step-label { display: none; }
            .step-dot { width: 32px; height: 32px; font-size: 10px; }
            .progress-section { margin-top: 16px; }
            .step-arrow { display: none; }
            .project-badge { margin-top: 12px; padding: 10px 12px; }
            .project-badge-icon { width: 30px; height: 30px; font-size: 12px; }

            .main-content {
                margin-left: 0;
                min-height: auto;
                padding: 16px;
                align-items: flex-start;
            }
            .content-card {
                min-height: auto;
                padding: 24px 18px;
                border-radius: 20px;
                max-width: 100%;
                animation: none;
            }
            .btn-continue, .btn-back { border-radius: 5px; }
            .top, .card-top { flex-wrap: wrap; gap: 10px; }
            .question-title {
                margin: 24px 0 12px;
                font-size: 28px;
                letter-spacing: -1px;
                line-height: 1.1;
            }
            .question-desc { margin: 0 0 20px; font-size: 14px; }
            .option-grid { grid-template-columns: 1fr !important; gap: 10px; }
            .opt-card { padding: 18px; font-size: 15px; }
            .card-bottom {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .btn-continue, .btn-back { width: 100%; justify-content: center; min-height: 48px; }
            .counter, .counter-circle { min-width: 42px; height: 42px; font-size: 11px; padding: 0 8px; border-radius: 21px; background: rgba(255,183,3,0.1); border-color: rgba(255,183,3,0.18); color: var(--yellow); }
            .dropzone { padding: 24px 16px; }
            .glass-input { height: 46px; font-size: 14px; }
            .textarea-wrap textarea { min-height: 100px; }
        }
        @media (pointer: coarse) {
            .opt-card { min-height: 48px; }
            .btn-continue, .btn-back { min-height: 48px; }
            .glass-input { height: 48px; }
            .skip-link { min-height: 44px; display: inline-flex; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="background" aria-hidden="true">
        <div class="curved-pattern"></div>
        <div class="grid-pattern"></div>
        <div class="hero-snow"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
        <div class="hero-ring hero-ring-1"></div>
        <div class="hero-ring hero-ring-2"></div>
        <div class="hero-line hero-line-1"></div>
        <div class="hero-line hero-line-2"></div>
    </div>

    {{ $slot }}
</body>
</html>