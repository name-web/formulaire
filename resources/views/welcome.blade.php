<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DOMINIUM TECH — Agence digitale</title>
    @fonts
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .hero-orb { position: absolute; border-radius: 50%; filter: blur(80px); }
        .hero-orb-1 {
            width: 500px; height: 500px; top: -10%; right: -5%;
            background: radial-gradient(circle, rgba(59,130,246,0.25) 0%, transparent 70%);
            animation: orbFloat1 8s ease-in-out infinite;
        }
        .hero-orb-2 {
            width: 400px; height: 400px; bottom: -10%; left: 10%;
            background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, transparent 70%);
            animation: orbFloat2 10s ease-in-out infinite;
        }
        .hero-orb-3 {
            width: 300px; height: 300px; top: 40%; left: 50%;
            background: radial-gradient(circle, rgba(14,165,233,0.15) 0%, transparent 70%);
            animation: orbFloat3 12s ease-in-out infinite;
        }

        .hero-shape { position: absolute; border-radius: 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); }
        .hero-shape-1 { width: 80px; height: 80px; top: 15%; right: 20%; animation: shapeRotate 20s linear infinite, shapeFade 6s ease-in-out infinite; }
        .hero-shape-2 { width: 60px; height: 60px; bottom: 25%; right: 30%; animation: shapeRotate 25s linear infinite reverse, shapeFade 8s ease-in-out infinite 2s; }
        .hero-shape-3 { width: 40px; height: 40px; top: 60%; right: 10%; animation: shapeRotate 15s linear infinite, shapeFade 7s ease-in-out infinite 1s; }

        .hero-ring { position: absolute; border-radius: 50%; border: 1px solid rgba(255,255,255,0.06); }
        .hero-ring-1 { width: 200px; height: 200px; top: 20%; right: 15%; animation: ringPulse 6s ease-in-out infinite; }
        .hero-ring-2 { width: 120px; height: 120px; bottom: 30%; left: 55%; animation: ringPulse 8s ease-in-out infinite 2s; }

        .hero-line { position: absolute; height: 1px; background: linear-gradient(90deg, transparent, rgba(59,130,246,0.15), transparent); }
        .hero-line-1 { width: 300px; top: 30%; right: 5%; animation: lineSlide 8s ease-in-out infinite; }
        .hero-line-2 { width: 200px; bottom: 35%; left: 20%; animation: lineSlide 10s ease-in-out infinite 3s; transform: rotate(-15deg); }

        @keyframes orbFloat1 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(-30px, 20px) scale(1.05); } }
        @keyframes orbFloat2 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(20px, -30px) scale(1.08); } }
        @keyframes orbFloat3 { 0%, 100% { transform: translate(0, 0) scale(1); } 50% { transform: translate(-20px, -15px) scale(1.03); } }
        @keyframes shapeRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes shapeFade { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.8; } }
        @keyframes ringPulse { 0%, 100% { transform: scale(1); opacity: 0.3; } 50% { transform: scale(1.1); opacity: 0.6; } }
        @keyframes lineSlide { 0%, 100% { transform: translateX(0); opacity: 0.3; } 50% { transform: translateX(40px); opacity: 0.7; } }

        .hero-snow { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .hero-snow span {
            position: absolute; top: -10px;
            width: 3px; height: 3px; border-radius: 50%;
            background: rgba(255,255,255,0.4);
            animation: snowFall linear infinite;
        }
        .hero-snow span:nth-child(1)  { left:  5%; animation-duration: 12s; animation-delay: 0s;   width: 2px; height: 2px; opacity: 0.3; }
        .hero-snow span:nth-child(2)  { left: 12%; animation-duration: 14s; animation-delay: 1s;   opacity: 0.25; }
        .hero-snow span:nth-child(3)  { left: 20%; animation-duration: 10s; animation-delay: 3s;   width: 2px; height: 2px; opacity: 0.35; }
        .hero-snow span:nth-child(4)  { left: 28%; animation-duration: 16s; animation-delay: 0.5s; opacity: 0.2; }
        .hero-snow span:nth-child(5)  { left: 35%; animation-duration: 11s; animation-delay: 2s;   opacity: 0.3; }
        .hero-snow span:nth-child(6)  { left: 42%; animation-duration: 13s; animation-delay: 4s;   width: 2px; height: 2px; opacity: 0.25; }
        .hero-snow span:nth-child(7)  { left: 50%; animation-duration: 15s; animation-delay: 1.5s; opacity: 0.3; }
        .hero-snow span:nth-child(8)  { left: 58%; animation-duration: 10s; animation-delay: 3.5s; opacity: 0.2; }
        .hero-snow span:nth-child(9)  { left: 65%; animation-duration: 14s; animation-delay: 0.8s; width: 2px; height: 2px; opacity: 0.35; }
        .hero-snow span:nth-child(10) { left: 72%; animation-duration: 12s; animation-delay: 2.5s; opacity: 0.25; }
        .hero-snow span:nth-child(11) { left: 78%; animation-duration: 16s; animation-delay: 4.5s; opacity: 0.2; }
        .hero-snow span:nth-child(12) { left: 85%; animation-duration: 11s; animation-delay: 1.2s; width: 2px; height: 2px; opacity: 0.3; }
        .hero-snow span:nth-child(13) { left: 90%; animation-duration: 13s; animation-delay: 3.2s; opacity: 0.25; }
        .hero-snow span:nth-child(14) { left: 15%; animation-duration: 15s; animation-delay: 5s;   opacity: 0.2; }
        .hero-snow span:nth-child(15) { left: 55%; animation-duration: 12s; animation-delay: 0.3s; opacity: 0.3; }
        @keyframes snowFall {
            0%   { transform: translateY(-10px) translateX(0) rotate(0deg); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(100vh) translateX(60px) rotate(360deg); opacity: 0; }
        }

        .hero-slideshow { position: relative; }
        .hero-slide {
            position: absolute; inset: 0;
            opacity: 0;
            animation: heroSlideFade 15s linear infinite;
        }
        @keyframes heroSlideFade {
            0%    { opacity: 0; }
            2%    { opacity: 1; }
            20%   { opacity: 1; }
            22%   { opacity: 0; }
            100%  { opacity: 0; }
        }

        /* --- Brand letter reveal --- */
        .hero-text-reveal span {
            display: inline-block;
            opacity: 0;
            transform: translateY(20px) rotateX(-40deg);
            animation: letterReveal 0.5s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }
        .hero-text-reveal span:nth-child(1)  { animation-delay: 0.1s; }
        .hero-text-reveal span:nth-child(2)  { animation-delay: 0.15s; }
        .hero-text-reveal span:nth-child(3)  { animation-delay: 0.2s; }
        .hero-text-reveal span:nth-child(4)  { animation-delay: 0.25s; }
        .hero-text-reveal span:nth-child(5)  { animation-delay: 0.3s; }
        .hero-text-reveal span:nth-child(6)  { animation-delay: 0.35s; }
        .hero-text-reveal span:nth-child(7)  { animation-delay: 0.4s; }
        .hero-text-reveal span:nth-child(8)  { animation-delay: 0.45s; }
        .hero-text-reveal span:nth-child(9)  { animation-delay: 0.9s; }
        .hero-text-reveal span:nth-child(10) { animation-delay: 0.95s; }
        .hero-text-reveal span:nth-child(11) { animation-delay: 1.0s; }
        .hero-text-reveal span:nth-child(12) { animation-delay: 1.05s; }

        @keyframes letterReveal {
            0%   { opacity: 0; transform: translateY(20px) rotateX(-40deg); }
            100% { opacity: 1; transform: translateY(0) rotateX(0deg); }
        }

        /* --- Brand pulse after reveal --- */
        .hero-brand-animate {
            animation: brandPulse 3s ease-in-out 2s infinite;
        }
        @keyframes brandPulse {
            0%, 100% { filter: drop-shadow(0 0 20px rgba(59,130,246,0)); }
            50%      { filter: drop-shadow(0 0 25px rgba(59,130,246,0.15)); }
        }

        /* --- Line reveal --- */
        .hero-line-reveal {
            display: inline-block;
            opacity: 0;
            transform: translateY(30px);
            animation: lineReveal 0.7s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }
        @keyframes lineReveal {
            0%   { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* --- Glow on blue text --- */
        .hero-glow-text {
            text-shadow: 0 0 0 transparent;
            animation: lineReveal 0.7s cubic-bezier(0.23, 1, 0.32, 1) forwards, glowPulse 4s ease-in-out 2s infinite;
        }
        @keyframes glowPulse {
            0%, 100% { text-shadow: 0 0 0 transparent; }
            50%      { text-shadow: 0 0 30px rgba(59,130,246,0.3), 0 0 60px rgba(59,130,246,0.1); }
        }

        /* --- Enhanced scroll reveal --- */
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
            0%, 100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
            50% { box-shadow: 0 0 20px 4px rgba(59,130,246,0.15); }
        }
        .glow-on-scroll.visible { animation: pulseGlow 2s ease-in-out 1; }

        .parallax-bg { will-change: transform; transition: transform 0.1s linear; }
    </style>
</head>
<body class="bg-[#020617] text-white font-sans antialiased">

    {{-- ============================================
         NAVIGATION
    ============================================ --}}
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="main-nav">
        <div class="max-w-7xl mx-auto px-6 lg:px-10 h-18 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM TECH" class="h-10 w-auto" id="nav-logo">
                <span class="text-lg font-bold tracking-tight text-white transition-colors duration-300" id="nav-brand">DOMINIUM TECH</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('questionnaire.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300"
                   id="nav-cta">
                    Démarrer un projet
                    <i class="fa-solid fa-arrow-right w-4 h-4"></i>
                </a>
            </div>
        </div>
    </nav>


    {{-- ============================================
         HERO
    ============================================ --}}
    <section class="relative min-h-[92vh] flex items-center overflow-hidden" style="background: linear-gradient(135deg, #020617 0%, #0c1e3a 40%, #0f2847 70%, #0a1628 100%);">
        {{-- Flowing curved lines pattern --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        {{-- Snow particles --}}
        <div class="hero-snow" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span><span></span>
        </div>

        {{-- Animated gradient orbs --}}
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>

        {{-- Floating geometric shapes --}}
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
        <div class="hero-ring hero-ring-1"></div>
        <div class="hero-ring hero-ring-2"></div>
        <div class="hero-line hero-line-1"></div>
        <div class="hero-line hero-line-2"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10 w-full py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                {{-- Left: Text --}}
                <div class="reveal-left">
                    <div class="flex items-center gap-1 mb-8 hero-brand-animate">
                        <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM TECH" class="h-36 w-auto drop-shadow-2xl">
                        <div>
                            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-none hero-text-reveal">
                                <span>D</span><span>O</span><span>M</span><span>I</span><span>N</span><span>I</span><span>U</span><span>M</span>
                            </h2>
                            <span class="text-[#3b82f6] font-bold text-lg tracking-[0.3em] uppercase hero-text-reveal" style="animation-delay: 0.8s;">
                                <span>T</span><span>E</span><span>C</span><span>H</span>
                            </span>
                        </div>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.05] mb-6">
                        <span class="hero-line-reveal" style="animation-delay: 0.3s;">Votre vision,</span>
                        <br>
                        <span class="text-[#3b82f6] hero-glow-text hero-line-reveal" style="animation-delay: 0.6s;">notre expertise</span>
                        <br>
                        <span class="hero-line-reveal" style="animation-delay: 0.9s;">digitale.</span>
                    </h1>

                    <p class="hero-line-reveal text-lg text-white/50 max-w-md leading-relaxed mb-10" style="animation-delay: 1.2s;">
                        Sites web, boutiques en ligne, applications mobiles et logiciels sur mesure.
                        Transformons vos idées en réalité.
                    </p>

                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <a href="{{ route('questionnaire.index') }}"
                           class="group inline-flex items-center gap-3 px-8 py-4 bg-blue-500 text-white font-bold rounded-2xl hover:bg-blue-400 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 text-base">
                            Décrivez votre projet
                            <i class="fa-solid fa-arrow-right w-5 h-5 transition-transform group-hover:translate-x-1"></i>
                        </a>
                        <a href="#services"
                           class="inline-flex items-center gap-2 px-6 py-4 text-white/60 font-medium rounded-2xl border border-white/10 hover:bg-white/5 hover:text-white transition-all duration-300 text-base">
                            Nos services
                        </a>
                    </div>
                </div>

                {{-- Right: Image slideshow --}}
                <div class="relative reveal-right">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl shadow-black/40 aspect-[4/3] hero-slideshow">
                        <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800&q=80&auto=format&fit=crop"
                             alt="Équipe DOMINIUM TECH" class="w-full h-full object-cover hero-slide" style="animation-delay: 0s;">
                        <img src="https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=800&q=80&auto=format&fit=crop"
                             alt="Équipe DOMINIUM TECH" class="w-full h-full object-cover hero-slide" style="animation-delay: 3s;">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80&auto=format&fit=crop"
                             alt="Équipe DOMINIUM TECH" class="w-full h-full object-cover hero-slide" style="animation-delay: 6s;">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&q=80&auto=format&fit=crop"
                             alt="Équipe DOMINIUM TECH" class="w-full h-full object-cover hero-slide" style="animation-delay: 9s;">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80&auto=format&fit=crop"
                             alt="Équipe DOMINIUM TECH" class="w-full h-full object-cover hero-slide" style="animation-delay: 12s;">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0a1628]/60 via-transparent to-transparent z-10"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-white/20">
            <span class="text-[10px] font-medium uppercase tracking-widest">Scroll</span>
            <div class="w-5 h-8 rounded-full border border-white/20 flex justify-center pt-1.5">
                <div class="w-1 h-2 rounded-full bg-white/40 animate-bounce"></div>
            </div>
        </div>
    </section>


    {{-- ============================================
         SERVICES
    ============================================ --}}
    <section id="services" class="py-24 sm:py-32 relative" style="background: linear-gradient(135deg, #020617 0%, #0c1e3a 40%, #0f2847 70%, #0a1628 100%);">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10">

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-14 reveal-up">
                <div>
                    <span class="text-xs font-semibold text-blue-400 uppercase tracking-widest mb-3 block">Nos services</span>
                    <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                        Un projet en tête ?
                    </h2>
                </div>
                <a href="{{ route('questionnaire.index') }}" class="mt-4 sm:mt-0 text-sm font-semibold text-blue-400 hover:text-blue-300 transition-colors inline-flex items-center gap-1.5">
                    Voir tous les services
                    <i class="fa-solid fa-arrow-right w-4 h-4"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Site vitrine --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-1">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-blue-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-globe text-blue-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Site vitrine</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        Présentez votre activité et vos services avec un site professionnel.
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400">
                        Démarrer
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Boutique en ligne --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-2">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-blue-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-bag-shopping text-blue-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Boutique en ligne</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        Vendez vos produits en ligne avec une boutique rapide et sécurisée.
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400">
                        Démarrer
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Application mobile --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-3">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-blue-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-mobile-screen text-blue-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Application mobile</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        Créez une application mobile native pour iOS et Android.
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400">
                        Démarrer
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Logiciel / ERP / CRM --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-4">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-blue-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-gear text-blue-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Logiciel / ERP / CRM</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        Développez un logiciel sur mesure pour vos processus métier.
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400">
                        Démarrer
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

                {{-- Autre --}}
                <a href="{{ route('questionnaire.index') }}" class="project-card group relative p-4 rounded-xl bg-white/5 border border-white/10 overflow-hidden reveal-up stagger-5">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-blue-500 scale-x-0 origin-left transition-transform duration-300 group-hover:scale-x-100"></div>
                    <div class="flex items-center gap-3 mb-2 project-card-icon">
                        <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-wand-magic-sparkles text-blue-400 text-base"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Autre projet</h3>
                    </div>
                    <p class="text-xs text-white/50 leading-relaxed mb-2.5">
                        Un projet unique ? Décrivez votre vision, nous trouverons la solution technique adaptée.
                    </p>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400">
                        Démarrer
                        <i class="fa-solid fa-arrow-right w-3 h-3 transition-transform group-hover:translate-x-1"></i>
                    </span>
                </a>

            </div>
        </div>
    </section>


    {{-- ============================================
         HOW IT WORKS
    ============================================ --}}
    <section class="py-24 sm:py-32 relative" style="background: linear-gradient(135deg, #0a1628 0%, #0c1e3a 50%, #020617 100%);">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-10">

            <div class="text-center mb-16 reveal-scale">
                <span class="text-xs font-semibold text-blue-400 uppercase tracking-widest mb-3 block">Processus</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                    Comment ça fonctionne ?
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">

                <div class="step-card relative p-8 rounded-2xl bg-white/5 border border-white/10 reveal-left stagger-1">
                    <div class="step-number w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 font-bold text-lg flex items-center justify-center mb-6 border border-blue-500/20">
                        01
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">Vous nous présentez votre idée</h3>
                    <p class="text-sm text-white/50 leading-relaxed">
                        Décrivez votre projet en quelques clics. Notre questionnaire interactif recueille toutes les informations essentielles.
                    </p>
                </div>

                <div class="step-card relative p-8 rounded-2xl bg-white/5 border border-white/10 reveal-up stagger-2">
                    <div class="step-number w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 font-bold text-lg flex items-center justify-center mb-6 border border-blue-500/20">
                        02
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">Nous comprenons votre besoin</h3>
                    <p class="text-sm text-white/50 leading-relaxed">
                        Notre équipe analyse votre demande et identifie les meilleures solutions techniques pour atteindre vos objectifs.
                    </p>
                </div>

                <div class="step-card relative p-8 rounded-2xl bg-white/5 border border-white/10 reveal-right stagger-3">
                    <div class="step-number w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 font-bold text-lg flex items-center justify-center mb-6 border border-blue-500/20">
                        03
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">Nous étudions votre projet</h3>
                    <p class="text-sm text-white/50 leading-relaxed">
                        Vous recevez une proposition détaillée sous 24 à 48 heures. Délai et budget sont transparents.
                    </p>
                </div>

            </div>
        </div>
    </section>


    {{-- ============================================
         FINAL CTA
    ============================================ --}}
    <section class="py-24 sm:py-32 relative" style="background: linear-gradient(135deg, #020617 0%, #0c1e3a 40%, #0f2847 70%, #0a1628 100%);">
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Cpath d='M0 50 Q25 30 50 50 T100 50' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 70 Q25 50 50 70 T100 70' fill='none' stroke='white' stroke-width='0.5'/%3E%3Cpath d='M0 30 Q25 10 50 30 T100 30' fill='none' stroke='white' stroke-width='0.5'/%3E%3C/svg%3E&quot;); background-size: 200px 100px;"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-6 lg:px-10 text-center reveal-scale glow-on-scroll">
            <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">
                Prêt à donner vie à votre projet ?
            </h2>
            <p class="text-lg text-white/50 mb-8 max-w-lg mx-auto">
                Décrivez votre projet en quelques minutes. C'est gratuit, rapide et sans engagement.
            </p>
            <a href="{{ route('questionnaire.index') }}"
               class="group inline-flex items-center gap-3 px-8 py-4 bg-blue-500 text-white font-bold rounded-2xl hover:bg-blue-400 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 text-base">
                Commencer maintenant
                <i class="fa-solid fa-arrow-right w-5 h-5 transition-transform group-hover:translate-x-1"></i>
            </a>
        </div>
    </section>


    {{-- ============================================
         FOOTER
    ============================================ --}}
    <footer class="py-8 border-t border-white/10" style="background: #020617;">
        <div class="max-w-7xl mx-auto px-6 lg:px-10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('IMAGES/Logo dominium.png') }}" alt="DOMINIUM TECH" class="h-6 w-auto opacity-60">
                <span class="text-sm font-bold text-white/60 tracking-tight">DOMINIUM TECH</span>
            </div>
            <p class="text-xs text-white/30">
                &copy; {{ date('Y') }} DOMINIUM TECH. Tous droits réservés.
            </p>
        </div>
    </footer>


    {{-- ============================================
         SCRIPTS
    ============================================ --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {

        const nav = document.getElementById('main-nav');
        const navCta = document.getElementById('nav-cta');
        const navBrand = document.getElementById('nav-brand');

        function updateNav() {
            if (window.scrollY > 60) {
                nav.classList.add('bg-white/95', 'backdrop-blur-xl', 'border-b', 'border-[var(--color-border-subtle)]', 'shadow-sm');
                nav.classList.remove('bg-transparent');
                navCta.classList.add('bg-blue-500', 'text-white', 'hover:bg-blue-400');
                navCta.classList.remove('bg-white/10', 'text-white', 'border', 'border-white/20');
                navBrand.classList.add('text-slate-900');
                navBrand.classList.remove('text-white');
            } else {
                nav.classList.remove('bg-white/95', 'backdrop-blur-xl', 'border-b', 'border-[var(--color-border-subtle)]', 'shadow-sm');
                nav.classList.add('bg-transparent');
                navCta.classList.remove('bg-blue-500', 'text-white', 'hover:bg-blue-400');
                navCta.classList.add('bg-white/10', 'text-white', 'border', 'border-white/20');
                navBrand.classList.remove('text-slate-900');
                navBrand.classList.add('text-white');
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
