<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DOMINIUM TECH — Agence digitale</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800;1,9..40,400&display=swap" rel="stylesheet">
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

        body { font-family: 'DM Sans', sans-serif; }

        /* =====================================================
           HERO SECTION
        ===================================================== */

        .hero-section {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: 130px 7% 120px;
            background:
                radial-gradient(circle at 82% 45%, rgba(25, 59, 101, .35), transparent 38%),
                radial-gradient(circle at 15% 20%, rgba(255,183,3,.04), transparent 30%),
                linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);
        }

        /* =====================================================
           BACKGROUND GRID
        ===================================================== */

        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 55px 55px;
            pointer-events: none;
            mask-image: linear-gradient(to right, black 0%, black 55%, transparent 100%);
        }

        /* =====================================================
           DECORATION
        ===================================================== */

        .background-ring {
            position: absolute;
            width: 560px;
            height: 560px;
            right: -270px;
            top: -170px;
            border: 1px solid rgba(255,183,3,.10);
            border-radius: 50%;
        }

        .background-ring::before {
            content: "";
            position: absolute;
            inset: 45px;
            border: 1px solid rgba(255,183,3,.06);
            border-radius: 50%;
        }

        .background-line {
            position: absolute;
            width: 240px;
            height: 1px;
            left: -60px;
            bottom: 150px;
            background: linear-gradient(90deg, transparent, rgba(255,183,3,.5));
        }

        /* =====================================================
           CONTENT
        ===================================================== */

        .hero-content {
            position: relative;
            z-index: 5;
            width: 52%;
            max-width: 720px;
        }

        /* =====================================================
           EYEBROW
        ===================================================== */

        .hero-eyebrow {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            color: var(--yellow);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .eyebrow-line {
            width: 42px;
            height: 2px;
            background: var(--yellow);
        }

        /* =====================================================
           TITLE
        ===================================================== */

        .hero-title {
            max-width: 700px;
            font-family: 'Segoe UI', sans-serif;
            font-size: clamp(3.4rem, 5.5vw, 5.7rem);
            line-height: .98;
            font-weight: 800;
            letter-spacing: -4px;
            text-transform: uppercase;
        }

        .hero-title .yellow {
            color: var(--yellow);
        }

        .hero-title .outline {
            color: transparent;
            -webkit-text-stroke: 1px rgba(255,255,255,.65);
        }

        /* =====================================================
           DESCRIPTION
        ===================================================== */

        .hero-description {
            max-width: 590px;
            margin-top: 30px;
            color: var(--text);
            font-size: 16px;
            line-height: 1.8;
        }

        /* =====================================================
           BUTTONS
        ===================================================== */

        .btn-group {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 38px;
        }

        .btn-hero {
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 24px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            transition: transform .2s ease, background .2s ease, border .2s ease;
        }

        .btn-primary {
            min-width: 245px;
            justify-content: space-between;
            gap: 30px;
            background: var(--yellow);
            color: #071426;
            border: 2px solid var(--yellow);
        }

        .btn-primary span {
            font-size: 21px;
            line-height: 1;
        }

        .btn-primary:hover {
            background: var(--yellow-light);
            transform: translateY(-2px);
        }

        .btn-secondary {
            color: white;
            border: 1px solid rgba(255,255,255,.35);
            background: rgba(255,255,255,.02);
        }

        .btn-secondary:hover {
            border-color: white;
            background: rgba(255,255,255,.06);
        }

        /* =====================================================
           INFORMATION
        ===================================================== */

        .hero-info {
            display: flex;
            max-width: 620px;
            margin-top: 60px;
            border-top: 1px solid var(--line);
        }

        .info-item {
            flex: 1;
            padding: 19px 22px 0 0;
            border-right: 1px solid var(--line);
        }

        .info-item + .info-item {
            padding-left: 22px;
        }

        .info-item:last-child {
            border-right: none;
        }

        .info-number {
            color: var(--yellow);
            font-size: 20px;
            font-weight: 800;
        }

        .info-label {
            margin-top: 6px;
            color: var(--muted);
            font-size: 9px;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        /* =====================================================
           VISUAL
        ===================================================== */

        .hero-visual {
            position: absolute;
            z-index: 4;
            right: 5.5%;
            top: 50%;
            transform: translateY(-50%);
            width: 43%;
            max-width: 650px;
            height: 70vh;
            max-height: 650px;
        }

        /* =====================================================
           IMAGE FRAME
        ===================================================== */

        .image-container {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: var(--blue);
            border: 1px solid rgba(255,255,255,.16);
            box-shadow: 0 35px 80px rgba(0,0,0,.35);
        }

        .image-container img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center center;
            filter: saturate(.88) contrast(1.08);
            transition: transform .7s ease;
        }

        .hero-visual:hover .image-container img {
            transform: scale(1.035);
        }

        /* =====================================================
           IMAGE GRADIENT
        ===================================================== */

        .image-container::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(7,20,38,.02) 30%, rgba(7,20,38,.18) 60%, rgba(7,20,38,.86) 100%);
            pointer-events: none;
            z-index: 1;
        }

        /* =====================================================
           IMAGE SLIDESHOW
        ===================================================== */

        .hero-slideshow {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            z-index: 0;
            animation: heroSlideFade 18s linear infinite;
        }

        .hero-slide:first-child {
            opacity: 1;
            z-index: 1;
        }

        .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(.88) contrast(1.08);
            transition: transform .7s ease;
        }

        .hero-slide:nth-child(1) { animation-delay: 0s; }
        .hero-slide:nth-child(2) { animation-delay: 3.6s; }
        .hero-slide:nth-child(3) { animation-delay: 7.2s; }
        .hero-slide:nth-child(4) { animation-delay: 10.8s; }
        .hero-slide:nth-child(5) { animation-delay: 14.4s; }

        @keyframes heroSlideFade {
            0%    { opacity: 0; }
            2%    { opacity: 1; }
            18%   { opacity: 1; }
            20%   { opacity: 0; }
            100%  { opacity: 0; }
        }

        /* =====================================================
           IMAGE TEXT
        ===================================================== */

        .image-caption {
            position: absolute;
            z-index: 3;
            left: 28px;
            bottom: 27px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: white;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .caption-line {
            width: 28px;
            height: 2px;
            background: var(--yellow);
        }

        /* =====================================================
           AFRICAN GEOMETRIC PATTERN
        ===================================================== */

        .image-pattern {
            position: absolute;
            z-index: 2;
            top: 25px;
            right: 25px;
            width: 105px;
            height: 105px;
            opacity: .7;
        }

        .image-pattern::before,
        .image-pattern::after {
            content: "";
            position: absolute;
            inset: 0;
            border: 1px solid rgba(255,183,3,.7);
            transform: rotate(45deg);
        }

        .image-pattern::after {
            inset: 20px;
            transform: rotate(45deg);
        }

        /* =====================================================
           SMALL CORNER
        ===================================================== */

        .image-corner {
            position: absolute;
            z-index: 3;
            right: 25px;
            bottom: 25px;
            width: 45px;
            height: 45px;
            border-right: 2px solid var(--yellow);
            border-bottom: 2px solid var(--yellow);
        }

        /* =====================================================
           VERTICAL TEXT
        ===================================================== */

        .vertical-text {
            position: absolute;
            right: -43px;
            top: 50%;
            transform: rotate(90deg) translateX(50%);
            transform-origin: right top;
            color: rgba(255,255,255,.4);
            font-size: 8px;
            letter-spacing: 3px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* =====================================================
           WAVE
        ===================================================== */

        .wave-decoration {
            position: absolute;
            z-index: 2;
            left: 0;
            bottom: -2px;
            width: 100%;
            height: 145px;
            pointer-events: none;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {
            .hero-section {
                padding-left: 6%;
                padding-right: 6%;
            }
            .hero-content {
                width: 50%;
            }
            .hero-visual {
                width: 42%;
                right: 4%;
            }
            .hero-title {
                font-size: 4rem;
            }
        }

        @media (max-width: 850px) {
            .hero-section {
                min-height: auto;
                padding: 120px 7% 120px;
                flex-direction: column;
            }
            .hero-content {
                width: 100%;
                max-width: 700px;
                text-align: center;
            }
            .hero-eyebrow {
                justify-content: center;
            }
            .hero-description {
                margin-left: auto;
                margin-right: auto;
            }
            .btn-group {
                justify-content: center;
            }
            .hero-info {
                margin-left: auto;
                margin-right: auto;
            }
            .hero-visual {
                position: relative;
                top: auto;
                right: auto;
                transform: none;
                width: 100%;
                max-width: 620px;
                height: 480px;
                margin: 70px auto 0;
            }
            .vertical-text {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .hero-section {
                padding: 100px 20px 90px;
            }
            .hero-title {
                font-size: 2.85rem;
                letter-spacing: -1.5px;
            }
            .hero-description {
                font-size: 14px;
            }
            .btn-group {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-hero,
            .btn-primary {
                width: 100%;
                min-width: 0;
            }
            .hero-info {
                flex-direction: column;
            }
            .info-item,
            .info-item + .info-item {
                padding: 16px 0;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }
            .info-item:last-child {
                border-bottom: none;
            }
            .hero-visual {
                height: 360px;
                margin-top: 55px;
            }
            .image-pattern {
                width: 70px;
                height: 70px;
                top: 18px;
                right: 18px;
            }
            .image-corner {
                right: 18px;
                bottom: 18px;
            }
            .footer-logo { height: 80px !important; }
            .footer-brand { margin-left: -20px !important; }
        }
        .welcome-hamburger {
            display: none;
            align-items: center; justify-content: center;
            width: 44px; height: 44px; border: none; background: none;
            color: white; cursor: pointer;
            border-radius: 8px; transition: background .2s;
        }
        .welcome-hamburger:hover { background: rgba(255,255,255,.1); }
        .welcome-hamburger .w-bars {
            width: 20px; height: 14px; display: flex; flex-direction: column;
            justify-content: space-between; position: relative;
        }
        .welcome-hamburger .w-bars span {
            display: block; width: 100%; height: 2px; background: white; border-radius: 2px;
            transition: all .3s cubic-bezier(.4,0,.2,1); transform-origin: center;
        }
        .welcome-hamburger.active .w-bars span:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .welcome-hamburger.active .w-bars span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .welcome-hamburger.active .w-bars span:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }
        @media (max-width: 500px) {
            .welcome-hamburger { display: flex; }
            .nav-links { display: none; }
            .nav-links.open { display: flex; position: absolute; top: 110px; left: 0; right: 0; flex-direction: column; background: rgba(7,20,38,.97); backdrop-filter: blur(20px); padding: 16px 24px; gap: 8px; border-bottom: 1px solid var(--line); animation: fadeIn .3s ease; }
            .nav-links.open a { padding: 12px 0; width: 100%; }
        }

        /* =====================================================
           UNIFIED YELLOW — All sections
        ===================================================== */

        .text-yellow-400 { color: var(--yellow) !important; }
        .hover\:text-yellow-300:hover { color: var(--yellow-light) !important; }
        .bg-yellow-500 { background-color: var(--yellow) !important; }
        .hover\:bg-yellow-400:hover { background-color: var(--yellow-light) !important; }
        .bg-yellow-500\/10 { background-color: rgba(255,183,3,.10) !important; }
        .hover\:shadow-yellow-500\/30 { --tw-shadow-color: rgba(255,183,3,.30); }
        .border-yellow-500 { border-color: var(--yellow) !important; }

        /* =====================================================
           SHARED SCROLL REVEAL
        ===================================================== */

        .reveal-up { opacity: 0; transform: translateY(40px); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
        .reveal-up.visible { opacity: 1; transform: translateY(0); }

        .reveal-left { opacity: 0; transform: translateX(-50px); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
        .reveal-left.visible { opacity: 1; transform: translateX(0); }

        .reveal-right { opacity: 0; transform: translateX(50px); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
        .reveal-right.visible { opacity: 1; transform: translateX(0); }

        .reveal-scale { opacity: 0; transform: scale(0.9); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
        .reveal-scale.visible { opacity: 1; transform: scale(1); }

        .reveal-rotate { opacity: 0; transform: translateY(30px) rotate(-2deg); transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1); }
        .reveal-rotate.visible { opacity: 1; transform: translateY(0) rotate(0deg); }

        .stagger-1 { transition-delay: 80ms; }
        .stagger-2 { transition-delay: 160ms; }
        .stagger-3 { transition-delay: 240ms; }
        .stagger-4 { transition-delay: 320ms; }
        .stagger-5 { transition-delay: 400ms; }

        .project-card, .step-card { transition: transform 0.4s cubic-bezier(0.16,1,0.3,1), box-shadow 0.4s cubic-bezier(0.16,1,0.3,1); }
        .project-card:hover, .step-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }

        .counter-animate { transition: all 0.4s cubic-bezier(0.16,1,0.3,1); }
        .counter-animate:hover { transform: scale(1.05); }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,183,3,0); }
            50% { box-shadow: 0 0 20px 4px rgba(255,183,3,0.15); }
        }
        .glow-on-scroll.visible { animation: pulseGlow 2s ease-in-out 1; }

        .parallax-bg { will-change: transform; transition: transform 0.1s linear; }

        /* =====================================================
           SECTION HEADINGS — Segoe UI ExtraBold
        ===================================================== */

        #services h2,
        #services h3,
        .step-card h3,
        .glow-on-scroll h2 {
            font-family: 'Segoe UI', sans-serif;
            font-weight: 800;
        }


    </style>
</head>
<body class="bg-[#071426] text-white font-sans antialiased">

    {{-- ============================================
         NAVIGATION
    ============================================ --}}
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="main-nav">
    <div
        class="flex items-center justify-between"
        style="padding-left: 7%; padding-right: 4%; min-height: 110px;"
    >

        <a href="/" class="flex items-center">

            <img
    src="{{ asset('IMAGES/Logo dominium.png') }}"
    alt="DOMINIUM TECH"
    class="h-24 w-auto opacity-100"
    style="filter: brightness(0) saturate(100%) invert(67%) sepia(96%) saturate(1200%) hue-rotate(2deg) brightness(94%) contrast(93%);"
>

            <div class="flex flex-col justify-center -ml-5 mt-1">
                <span
                    class="text-2xl font-black tracking-tight text-white transition-colors duration-300 leading-none"
                    id="nav-brand"
                >
                    DOMINIUM
                </span>

                <span
                    class="text-[10px] font-bold tracking-[0.3em] uppercase leading-none mt-1"
                    style="color: var(--yellow);"
                >
                    TECH
                </span>
            </div>

        </a>

        <div class="flex items-center gap-4">

            <div class="nav-links" id="navLinks">
                <a
                    href="{{ route('questionnaire.index') }}"
                    class="inline-flex items-center gap-2.5 px-6 py-3 rounded-lg text-sm font-bold transition-all duration-300"
                    id="nav-cta"
                >
                    {{ __('Démarrer un projet') }}
                    <i class="fa-solid fa-arrow-right w-4 h-4"></i>
                </a>
            </div>

            <button class="welcome-hamburger" id="welcomeHamburger" aria-label="Menu">
                <div class="w-bars"><span></span><span></span><span></span></div>
            </button>

        </div>

    </div>
</nav>


    {{-- ============================================
         HERO
    ============================================ --}}
    <section class="hero-section">

        {{-- Decorations --}}
        <div class="background-ring"></div>
        <div class="background-line"></div>
        <div class="absolute inset-0 opacity-[0.025] pointer-events-none" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>

        {{-- ==========================================
             CONTENU
        =========================================== --}}

        <div class="hero-content">

            <div class="hero-eyebrow">
                <span class="eyebrow-line"></span>
                DOMINIUM — DIGITAL PROJECT STUDIO
            </div>

            <h1 class="hero-title">
                {{ __('Votre idée') }}
                <br>
                <span class="yellow">{{ __('mérite') }}</span>
                <br>
                {{ __('une vraie') }}
                <span class="outline">{{ __('structure.') }}</span>
            </h1>

            <p class="hero-description">
                {{ __('Décrivez votre projet, vos besoins et votre vision.') }}
                {{ __('Nous transformons vos idées en spécifications claires') }}
                {{ __('pour donner à votre équipe une direction précise.') }}
            </p>

            <div class="btn-group">
                <a href="{{ route('questionnaire.index') }}" class="btn-hero btn-primary">
                    {{ __('Créer mon cahier des charges') }}
                    <span>&rarr;</span>
                </a>
                <a href="#services" class="btn-hero btn-secondary">
                    {{ __('Voir un modèle') }}
                </a>
            </div>

            {{-- Informations --}}
            <div class="hero-info">
                <div class="info-item">
                    <div class="info-number">01</div>
                    <div class="info-label">{{ __('Décrivez votre idée') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-number">02</div>
                    <div class="info-label">{{ __('Structurez votre besoin') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-number">03</div>
                    <div class="info-label">{{ __('Préparez votre solution') }}</div>
                </div>
            </div>

        </div>

        {{-- ==========================================
             IMAGE
        =========================================== --}}

        <div class="hero-visual">
            <div class="image-container">
                <div class="hero-slideshow">
                    <div class="hero-slide">
                        <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=1200&q=85&auto=format&fit=crop" alt="Équipe travaillant sur un projet">
                    </div>
                    <div class="hero-slide">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1200&q=85&auto=format&fit=crop" alt="Collaboration digitale">
                    </div>
                    <div class="hero-slide">
                        <img src="https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=1200&q=85&auto=format&fit=crop" alt="Réunion de projet">
                    </div>
                    <div class="hero-slide">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&q=85&auto=format&fit=crop" alt="Travail en équipe">
                    </div>
                    <div class="hero-slide">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1200&q=85&auto=format&fit=crop" alt="Stratégie digitale">
                    </div>
                </div>
                <div class="image-pattern"></div>
                <div class="image-caption">
                    <span class="caption-line"></span>
                    Votre projet commence ici
                </div>
                <div class="image-corner"></div>
            </div>

            <div class="vertical-text">
                IDEA / STRUCTURE / DIGITAL
            </div>
        </div>

        {{-- ==========================================
             VAGUE
        =========================================== --}}

        <svg class="wave-decoration" viewBox="0 0 1440 180" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 125 C180 175, 350 170, 520 100 C680 35, 800 40, 950 105 C1100 170, 1260 175, 1440 70" stroke="#ffb703" stroke-width="4" fill="none"/>
        </svg>

    </section>


    {{-- ============================================
         SERVICES
    ============================================ --}}
    <section id="services" class="py-24 sm:py-32 relative" style="background: radial-gradient(circle at 82% 45%, rgba(25, 59, 101, .35), transparent 38%), radial-gradient(circle at 15% 20%, rgba(255,183,3,.04), transparent 30%), linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10">

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-14 reveal-up">
                <div>
                    <span class="text-xs font-semibold text-yellow-400 uppercase tracking-widest mb-3 block">{{ __('Nos services') }}</span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                        {{ __('Un projet en tête ?') }}
                    </h2>
                </div>
                <a href="{{ route('questionnaire.index') }}" class="mt-4 sm:mt-0 text-sm font-semibold text-yellow-400 hover:text-yellow-300 transition-colors inline-flex items-center gap-1.5">
                    {{ __('Voir tous les services') }}
                    <i class="fa-solid fa-arrow-right w-4 h-4"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Site vitrine --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-1">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-yellow-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-globe text-yellow-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">{{ __('Site vitrine') }}</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        {{ __('Présentez votre activité et vos services avec un site professionnel.') }}
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-400">
                        {{ __('Démarrer') }}
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Boutique en ligne --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-2">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-yellow-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-bag-shopping text-yellow-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">{{ __('Boutique en ligne') }}</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        {{ __('Vendez vos produits en ligne avec une boutique rapide et sécurisée.') }}
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-400">
                        {{ __('Démarrer') }}
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Application mobile --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-3">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-yellow-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-mobile-screen text-yellow-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">{{ __('Application mobile') }}</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        {{ __('Créez une application mobile native pour iOS et Android.') }}
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-400">
                        {{ __('Démarrer') }}
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Logiciel / ERP / CRM --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-4">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-yellow-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-gear text-yellow-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">{{ __('Logiciel / ERP / CRM') }}</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        {{ __('Développez un logiciel sur mesure pour vos processus métier.') }}
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-400">
                        {{ __('Démarrer') }}
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Autre --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-5">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-yellow-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-wand-magic-sparkles text-yellow-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">{{ __('Autre projet') }}</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        {{ __('Un projet unique ? Décrivez votre vision, nous trouverons la solution technique adaptée.') }}
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-yellow-400">
                        {{ __('Démarrer') }}
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

            </div>
        </div>
    </section>


    {{-- ============================================
         HOW IT WORKS
    ============================================ --}}
    <section class="py-24 sm:py-32 relative" style="background: radial-gradient(circle at 82% 45%, rgba(25, 59, 101, .35), transparent 38%), radial-gradient(circle at 15% 20%, rgba(255,183,3,.04), transparent 30%), linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10">

            <div class="text-center mb-16 reveal-scale">
                <span class="text-xs font-semibold text-yellow-400 uppercase tracking-widest mb-3 block">{{ __('Processus') }}</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                    {{ __('Comment ça fonctionne ?') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">

                <div class="step-card relative p-8 rounded-2xl bg-white/5 border border-white/10 reveal-left stagger-1">
                    <div class="step-number w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-400 font-bold text-lg flex items-center justify-center mb-6 border border-blue-500/20">
                        01
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">{{ __('Vous nous présentez votre idée') }}</h3>
                    <p class="text-sm text-white/50 leading-relaxed">
                        {{ __('Décrivez votre projet en quelques clics. Notre questionnaire interactif recueille toutes les informations essentielles.') }}
                    </p>
                </div>

                <div class="step-card relative p-8 rounded-2xl bg-white/5 border border-white/10 reveal-up stagger-2">
                    <div class="step-number w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-400 font-bold text-lg flex items-center justify-center mb-6 border border-blue-500/20">
                        02
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">{{ __('Nous comprenons votre besoin') }}</h3>
                    <p class="text-sm text-white/50 leading-relaxed">
                        {{ __('Notre équipe analyse votre demande et identifie les meilleures solutions techniques pour atteindre vos objectifs.') }}
                    </p>
                </div>

                <div class="step-card relative p-8 rounded-2xl bg-white/5 border border-white/10 reveal-right stagger-3">
                    <div class="step-number w-12 h-12 rounded-xl bg-yellow-500/10 text-yellow-400 font-bold text-lg flex items-center justify-center mb-6 border border-blue-500/20">
                        03
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">{{ __('Nous étudions votre projet') }}</h3>
                    <p class="text-sm text-white/50 leading-relaxed">
                        {{ __('Vous recevez une proposition détaillée sous 24 à 48 heures. Délai et budget sont transparents.') }}
                    </p>
                </div>

            </div>
        </div>
    </section>


    {{-- ============================================
         FINAL CTA
    ============================================ --}}
    <section class="py-24 sm:py-32 relative" style="background: radial-gradient(circle at 82% 45%, rgba(25, 59, 101, .35), transparent 38%), radial-gradient(circle at 15% 20%, rgba(255,183,3,.04), transparent 30%), linear-gradient(135deg, #071426 0%, #0b1b31 52%, #102b4c 100%);">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-6 lg:px-10 text-center reveal-scale glow-on-scroll">
            <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">
                {{ __('Prêt à donner vie à votre projet ?') }}
            </h2>
            <p class="text-lg text-white/50 mb-8 max-w-lg mx-auto">
                {{ __('Décrivez votre projet en quelques minutes. C\'est gratuit, rapide et sans engagement.') }}
            </p>
            <a href="{{ route('questionnaire.index') }}"
               class="group inline-flex items-center gap-3 px-8 py-4 bg-yellow-500 text-white font-bold rounded-2xl hover:bg-yellow-400 transition-all duration-300 hover:shadow-lg hover:shadow-yellow-500/30 hover:-translate-y-0.5 text-base">
                {{ __('Commencer maintenant') }}
                <i class="fa-solid fa-arrow-right w-5 h-5 transition-transform group-hover:translate-x-1"></i>
            </a>
        </div>
    </section>


    {{-- ============================================
         FOOTER
    ============================================ --}}
    <footer class="py-8 border-t border-white/10" style="background: #071426;">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 flex flex-col sm:flex-row items-center justify-between gap-1">

        <div class="flex items-center">

            <img
                src="{{ asset('IMAGES/Logo dominium.png') }}"
                alt="DOMINIUM TECH"
                class="h-40 w-auto opacity-100 footer-logo"
                style="filter: brightness(0) saturate(100%) invert(67%) sepia(96%) saturate(1200%) hue-rotate(2deg) brightness(94%) contrast(93%);"
            >

            <div class="flex flex-col justify-center -ml-10 mt-2 footer-brand">
                <span class="text-sm font-bold text-white/60 tracking-tight leading-none">
                    DOMINIUM
                </span>

                <span
                    class="text-[10px] font-bold tracking-[0.3em] uppercase leading-none mt-1"
                    style="color: var(--yellow);"
                >
                    TECH
                </span>
            </div>

        </div>

        <p class="text-xs text-white/30">
            &copy; {{ date('Y') }} DOMINIUM TECH. {{ __('Tous droits réservés.') }}
        </p>

    </div>
</footer>


    {{-- ============================================
         SCRIPTS
    ============================================ --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        // --- Welcome hamburger toggle ---
        const welcomeHamburger = document.getElementById('welcomeHamburger');
        const navLinks = document.getElementById('navLinks');
        if (welcomeHamburger && navLinks) {
            welcomeHamburger.addEventListener('click', () => {
                navLinks.classList.toggle('open');
                welcomeHamburger.classList.toggle('active');
            });
        }

        const nav = document.getElementById('main-nav');
        const navCta = document.getElementById('nav-cta');
        const navBrand = document.getElementById('nav-brand');
        const navTech = navBrand ? navBrand.nextElementSibling : null;

        function updateNav() {
            if (window.scrollY > 60) {
                nav.classList.add('bg-white/95', 'backdrop-blur-xl', 'border-b', 'border-[var(--color-border-subtle)]', 'shadow-sm');
                nav.classList.remove('bg-transparent');
                navCta.classList.add('bg-yellow-500', 'text-white', 'hover:bg-yellow-400');
                navCta.classList.remove('bg-white/10', 'text-white', 'border', 'border-white/20');
                navBrand.classList.add('text-slate-900');
                navBrand.classList.remove('text-white');
                if (navTech) { navTech.style.color = '#071426'; }
            } else {
                nav.classList.remove('bg-white/95', 'backdrop-blur-xl', 'border-b', 'border-[var(--color-border-subtle)]', 'shadow-sm');
                nav.classList.add('bg-transparent');
                navCta.classList.remove('bg-yellow-500', 'text-white', 'hover:bg-yellow-400');
                navCta.classList.add('bg-white/10', 'text-white', 'border', 'border-white/20');
                navBrand.classList.remove('text-slate-900');
                navBrand.classList.add('text-white');
                if (navTech) { navTech.style.color = ''; }
            }
        }
        updateNav();
        window.addEventListener('scroll', updateNav, { passive: true });

        // --- Scroll reveal ---
        const reveals = document.querySelectorAll('.reveal, .reveal-up, .reveal-left, .reveal-right, .reveal-scale, .reveal-rotate');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(el => observer.observe(el));

        // --- Parallax on scroll ---
        const parallaxElements = document.querySelectorAll('.parallax-bg');
        let ticking = false;
        function updateParallax() {
            const scrollY = window.scrollY;
            parallaxElements.forEach(el => {
                const speed = parseFloat(el.dataset.speed) || 0.3;
                el.style.transform = `translateY(${scrollY * speed}px)`;
            });
            ticking = false;
        }
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });

        // --- Magnetic hover on cards ---
        document.querySelectorAll('.project-card, .step-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px) scale(1.01)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });

        // --- Active nav link highlight on scroll ---
        const sections = document.querySelectorAll('section[id]');
        function updateActiveLink() {
            const scrollY = window.scrollY + 200;
            sections.forEach(section => {
                const top = section.offsetTop;
                const height = section.offsetHeight;
                const id = section.getAttribute('id');
                const link = document.querySelector(`a[href="#${id}"]`);
                if (link) {
                    if (scrollY >= top && scrollY < top + height) {
                        link.classList.add('text-white');
                        link.classList.remove('text-white/60');
                    } else {
                        link.classList.remove('text-white');
                        link.classList.add('text-white/60');
                    }
                }
            });
        }
        window.addEventListener('scroll', updateActiveLink, { passive: true });
    });
    </script>

</body>
</html>
