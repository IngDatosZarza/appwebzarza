<?php /* ================================================================
   PROPUESTA 2 – La Zarza Contigo | Gamified Dark Premium Homepage
   Archivo de VISTA STANDALONE – no afecta el sistema real
   Acceso: /propuesta/2
   Inspirado en: Apple Fitness · Nike Membership · Duolingo · Starbucks
   ================================================================ */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>La Zarza Contigo – Propuesta 2</title>
    <link rel="icon" type="image/png" href="/logozarza.png">

    <!-- Google Fonts: Space Grotesk + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['"Space Grotesk"', 'system-ui', 'sans-serif'],
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        neon: {
                            50:  '#FFF0F7', 100: '#FCDAEC', 200: '#F9B5D9',
                            300: '#F069AE', 400: '#E8307A', 500: '#CC1A65',
                            600: '#A8124F', 700: '#840B3B', 800: '#5E0828',
                            900: '#3A0419', 950: '#1E020D',
                        },
                        dark: {
                            50: '#2A0D1C', 100: '#220B17', 200: '#1A0912',
                            300: '#14070E', 400: '#0E050A', 500: '#090306',
                        },
                    },
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: #0E0610;
            color: #FFFFFF;
            overflow-x: hidden;
        }
        .font-display { font-family: 'Space Grotesk', system-ui, sans-serif; }

        /* ─── Background grid pattern ──────────────────── */
        .bg-grid {
            background-image:
                linear-gradient(rgba(232,48,122,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232,48,122,.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        /* ─── Neon effects ──────────────────────────────── */
        .text-neon {
            color: #E8307A;
            text-shadow: 0 0 24px rgba(232,48,122,.8), 0 0 48px rgba(232,48,122,.3);
        }
        .text-gradient-neon {
            background: linear-gradient(135deg, #FF6BB0 0%, #E8307A 50%, #CC1A65 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glow-border {
            border: 1px solid rgba(232,48,122,.35);
            box-shadow: 0 0 20px rgba(232,48,122,.12), inset 0 0 20px rgba(232,48,122,.03);
        }
        .glow-border-strong {
            border: 1px solid rgba(232,48,122,.6);
            box-shadow: 0 0 30px rgba(232,48,122,.25), 0 0 60px rgba(232,48,122,.1), inset 0 0 20px rgba(232,48,122,.05);
        }
        .neon-glow-btn {
            box-shadow: 0 0 20px rgba(232,48,122,.5), 0 8px 32px rgba(232,48,122,.35);
        }

        /* ─── Glass cards (dark) ─────────────────────────  */
        .glass-dark {
            background: rgba(255,255,255,.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,.08);
        }
        .glass-neon {
            background: rgba(232,48,122,.07);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(232,48,122,.2);
        }

        /* ─── Navbar ─────────────────────────────────────── */
        .nav-base {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            transition: all .4s ease;
            padding: 20px 0;
        }
        .nav-scrolled {
            background: rgba(14,6,16,.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(232,48,122,.12);
            padding: 14px 0;
        }

        /* ─── Buttons ─────────────────────────────────────  */
        .btn-neon {
            display: inline-flex; align-items: center; gap: 9px;
            background: linear-gradient(135deg, #CC1A65, #E8307A, #FF6BB0);
            color: white;
            padding: 15px 36px; border-radius: 50px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: .92rem; letter-spacing: .04em;
            box-shadow: 0 0 24px rgba(232,48,122,.55), 0 8px 32px rgba(232,48,122,.35);
            transition: all .3s ease;
            position: relative; overflow: hidden;
            text-decoration: none; border: none; cursor: pointer;
        }
        .btn-neon:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 0 40px rgba(232,48,122,.75), 0 16px 48px rgba(232,48,122,.5);
        }
        .btn-neon::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
            transition: left .5s ease;
        }
        .btn-neon:hover::before { left: 100%; }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 9px;
            background: rgba(255,255,255,.05);
            color: rgba(255,255,255,.85);
            padding: 14px 36px; border-radius: 50px;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 600; font-size: .92rem; letter-spacing: .04em;
            border: 1px solid rgba(255,255,255,.15);
            transition: all .3s ease;
            text-decoration: none;
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,.08);
            border-color: rgba(232,48,122,.4);
            transform: translateY(-2px);
        }

        /* ─── Animations ──────────────────────────────────  */
        @keyframes floatA {
            0%,100% { transform: translateY(0) translateX(0); }
            50%      { transform: translateY(-12px) translateX(5px); }
        }
        @keyframes floatB {
            0%,100% { transform: translateY(0) translateX(0); }
            50%      { transform: translateY(-9px) translateX(-6px); }
        }
        @keyframes floatC {
            0%,100% { transform: translateY(0) translateX(0); }
            50%      { transform: translateY(-14px) translateX(3px); }
        }
        @keyframes pulseNeon {
            0%,100% { box-shadow: 0 0 15px rgba(232,48,122,.35), 0 0 30px rgba(232,48,122,.1); }
            50%      { box-shadow: 0 0 35px rgba(232,48,122,.7), 0 0 70px rgba(232,48,122,.3), 0 0 100px rgba(232,48,122,.1); }
        }
        @keyframes pulseGlow {
            0%,100% { opacity:.3; transform:scale(1); }
            50%      { opacity:.7; transform:scale(1.08); }
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(40px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity:0; transform:scale(.88); }
            to   { opacity:1; transform:scale(1); }
        }

        /* Ring fill animations */
        @keyframes ringFill1 {
            from { stroke-dashoffset: 1005; }
            to   { stroke-dashoffset: 201; }
        }
        @keyframes ringFill2 {
            from { stroke-dashoffset: 785; }
            to   { stroke-dashoffset: 275; }
        }
        @keyframes ringFill3 {
            from { stroke-dashoffset: 565; }
            to   { stroke-dashoffset: 141; }
        }

        /* Bar fill */
        @keyframes barFill {
            from { width: 0%; }
            to   { width: var(--w); }
        }

        /* Badge shine */
        @keyframes shine {
            0%   { transform: rotate(35deg) translateX(-200%); }
            100% { transform: rotate(35deg) translateX(300%); }
        }
        @keyframes countUp {
            from { opacity:0; }
            to   { opacity:1; }
        }

        /* Ticker scroll */
        @keyframes ticker {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .animate-float-a   { animation: floatA 5.5s ease-in-out infinite; }
        .animate-float-b   { animation: floatB 7s ease-in-out infinite 1s; }
        .animate-float-c   { animation: floatC 6s ease-in-out infinite .5s; }
        .animate-pulse-neon { animation: pulseNeon 3s ease-in-out infinite; }
        .animate-fade-up   { animation: fadeUp .7s ease-out both; }
        .animate-scale-in  { animation: scaleIn .6s ease-out both; }

        .d1 { animation-delay:.1s; }
        .d2 { animation-delay:.25s; }
        .d3 { animation-delay:.4s; }
        .d4 { animation-delay:.55s; }
        .d5 { animation-delay:.7s; }
        .d6 { animation-delay:.85s; }

        /* Ring SVG */
        .ring-1 {
            stroke-dasharray: 1005;
            stroke-dashoffset: 1005;
            transform-origin: center;
            transform: rotate(-90deg);
            animation: ringFill1 2.2s .8s cubic-bezier(.4,0,.2,1) forwards;
        }
        .ring-2 {
            stroke-dasharray: 785;
            stroke-dashoffset: 785;
            transform-origin: center;
            transform: rotate(-90deg);
            animation: ringFill2 2.4s 1s cubic-bezier(.4,0,.2,1) forwards;
        }
        .ring-3 {
            stroke-dasharray: 565;
            stroke-dashoffset: 565;
            transform-origin: center;
            transform: rotate(-90deg);
            animation: ringFill3 2s 1.2s cubic-bezier(.4,0,.2,1) forwards;
        }

        /* Progress bars */
        .progress-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #CC1A65, #E8307A, #FF6BB0);
            box-shadow: 0 0 12px rgba(232,48,122,.6);
            width: 0;
            animation: barFill 1.8s ease-out forwards;
        }

        /* Achievement badge */
        .badge-unlocked {
            position: relative; overflow: hidden;
            cursor: pointer;
            transition: all .3s ease;
        }
        .badge-unlocked:hover { transform: translateY(-6px) scale(1.05); }
        .badge-unlocked::after {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,.15) 50%, transparent 70%);
            transform: rotate(35deg) translateX(-200%);
        }
        .badge-unlocked:hover::after {
            animation: shine .5s ease-out forwards;
        }
        .badge-locked {
            filter: grayscale(1) brightness(.4);
            cursor: not-allowed;
        }

        /* Locked reward card */
        .locked-card {
            position: relative; overflow: hidden;
            transition: all .3s ease;
        }
        .locked-card:hover { transform: translateY(-4px); }
        .locked-overlay {
            position: absolute; inset: 0;
            background: rgba(14,6,16,.55);
            backdrop-filter: blur(2px);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 8px;
        }

        /* Level path */
        .level-connector {
            flex: 1; height: 2px;
            background: linear-gradient(90deg, rgba(232,48,122,.4), rgba(232,48,122,.1));
            position: relative;
        }
        .level-connector::after {
            content: '';
            position: absolute; top: 0; left: 0;
            height: 100%;
            background: linear-gradient(90deg, #E8307A, #FF6BB0);
            box-shadow: 0 0 8px rgba(232,48,122,.8);
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0; transform: translateY(28px);
            transition: opacity .65s ease, transform .65s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* Section label */
        .section-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(232,48,122,.1);
            color: #FF6BB0;
            padding: 6px 16px; border-radius: 50px;
            font-size: .75rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
            border: 1px solid rgba(232,48,122,.25);
        }

        /* Stat card */
        .stat-card {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px;
            padding: 24px;
            transition: all .3s ease;
        }
        .stat-card:hover {
            background: rgba(232,48,122,.07);
            border-color: rgba(232,48,122,.25);
            transform: translateY(-4px);
        }

        /* Ticker */
        .ticker-wrap {
            overflow: hidden;
            white-space: nowrap;
        }
        .ticker-inner {
            display: inline-flex;
            animation: ticker 28s linear infinite;
        }

        /* Mobile sticky bottom */
        .mobile-cta-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            padding: 16px 20px;
            background: rgba(14,6,16,.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(232,48,122,.15);
            z-index: 90;
        }

        /* Scrollbar dark */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0E0610; }
        ::-webkit-scrollbar-thumb { background: rgba(232,48,122,.4); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(232,48,122,.7); }

        a { text-decoration: none; }
    </style>
</head>

<body
    x-data="{
        scrolled: false,
        mobileMenu: false,
        points: 0,
        handleScroll() { this.scrolled = window.scrollY > 60 },
        countUp(target) {
            let start = 0;
            const step = Math.ceil(target / 60);
            const timer = setInterval(() => {
                start += step;
                if (start >= target) { start = target; clearInterval(timer); }
                this.points = start;
            }, 20);
        }
    }"
    @scroll.window="handleScroll()"
    x-init="setTimeout(() => countUp(342), 1400)">

<!-- ══════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════ -->
<nav class="nav-base" :class="scrolled ? 'nav-scrolled' : ''">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">

        <!-- Logo -->
        <a href="#">
            <img src="/lazarzacontigowhite.png" alt="La Zarza Contigo" class="h-9 w-auto object-contain">
        </a>

        <!-- Desktop links -->
        <div class="hidden lg:flex items-center gap-8 text-sm font-medium" style="color:rgba(255,255,255,.7);">
            <a href="#niveles"     class="hover:text-white transition-colors duration-200 hover:text-neon-400">Niveles</a>
            <a href="#logros"      class="hover:text-white transition-colors duration-200 hover:text-neon-400">Logros</a>
            <a href="#metas"       class="hover:text-white transition-colors duration-200 hover:text-neon-400">Metas</a>
            <a href="#recompensas" class="hover:text-white transition-colors duration-200 hover:text-neon-400">Recompensas</a>
        </div>

        <!-- CTA -->
        <div class="hidden lg:flex items-center gap-4">
            <a href="/login"
               class="text-sm font-semibold transition-colors duration-200"
               style="color:rgba(255,255,255,.6);"
               onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,.6)'">
                Iniciar sesión
            </a>
            <a href="/register" class="btn-neon text-sm">
                Comenzar ahora
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Mobile toggle -->
        <button class="lg:hidden p-2" style="color:rgba(255,255,255,.8);" @click="mobileMenu = !mobileMenu">
            <i class="fa-solid text-xl" :class="mobileMenu ? 'fa-xmark' : 'fa-bars'"></i>
        </button>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileMenu"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden mt-3 mx-4 pb-5 pt-4 glass-dark rounded-2xl px-5">
        <div class="flex flex-col gap-4 text-sm font-medium" style="color:rgba(255,255,255,.75);">
            <a href="#niveles"     @click="mobileMenu=false" class="hover:text-white py-1">Niveles</a>
            <a href="#logros"      @click="mobileMenu=false" class="hover:text-white py-1">Logros</a>
            <a href="#metas"       @click="mobileMenu=false" class="hover:text-white py-1">Metas</a>
            <a href="#recompensas" @click="mobileMenu=false" class="hover:text-white py-1">Recompensas</a>
            <hr style="border-color:rgba(255,255,255,.08);">
            <a href="/login"    class="hover:text-white py-1">Iniciar sesión</a>
            <a href="/register" class="btn-neon text-sm w-full text-center justify-center">Comenzar ahora</a>
        </div>
    </div>
</nav>


<!-- ══════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════ -->
<section class="bg-grid min-h-screen flex items-center pt-24 pb-10 relative overflow-hidden" id="inicio">

    <!-- ══════════════════════════════════════════════════════════
         VIDEO DE FONDO DEL HERO – subir: public/proposals/p2-hero-bg.mp4
         Formato : mp4 · H.264 · sin audio · 1920×1080
         Aparece automáticamente (opacity 15%) cuando el archivo existe.
         ══════════════════════════════════════════════════════════ -->
    <video class="absolute inset-0 w-full h-full object-cover pointer-events-none"
           style="opacity:.15; display:none; z-index:0;"
           autoplay muted loop playsinline
           oncanplay="this.style.display='block';">
        <source src="/proposals/p2-hero-bg.mp4" type="video/mp4">
    </video>

    <!-- Background radial glows -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-1/4 w-[600px] h-[600px] rounded-full"
             style="background: radial-gradient(circle, rgba(232,48,122,.14) 0%, transparent 65%); animation: pulseGlow 6s ease-in-out infinite;"></div>
        <div class="absolute bottom-0 left-1/4 w-[400px] h-[400px] rounded-full"
             style="background: radial-gradient(circle, rgba(140,10,70,.18) 0%, transparent 60%); animation: pulseGlow 8s ease-in-out infinite 2s;"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 w-full relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-8">

            <!-- ── LEFT: Text Content ── -->
            <div class="lg:w-5/12 flex flex-col items-start text-left">

                <!-- Game badge -->
                <div class="animate-fade-up d1 mb-8">
                    <span class="section-tag">
                        <span class="w-2 h-2 rounded-full bg-neon-400" style="animation: pulseNeon 2s infinite;"></span>
                        Programa en vivo · 2026
                    </span>
                </div>

                <!-- Headline -->
                <h1 class="font-display text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight mb-6 animate-fade-up d2">
                    Cada compra te
                    <span class="block text-gradient-neon">acerca a tu</span>
                    <span class="block">próxima recompensa.</span>
                </h1>

                <!-- Subtext -->
                <p class="text-lg leading-relaxed mb-10 max-w-md animate-fade-up d3"
                   style="color:rgba(255,255,255,.6);">
                    Acumula puntos, desbloquea beneficios y disfruta experiencias
                    exclusivas que hacen que cada visita valga más.
                </p>

                <!-- CTA buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mb-12 animate-fade-up d4">
                    <a href="/register" class="btn-neon text-base">
                        <i class="fa-solid fa-bolt text-yellow-300 text-sm"></i>
                        Comenzar ahora
                    </a>
                    <a href="#recompensas" class="btn-ghost text-base">
                        Explorar recompensas
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                </div>

                <!-- Mini stats row -->
                <div class="flex items-center gap-6 animate-fade-up d5">
                    <div>
                        <div class="font-display text-2xl font-bold text-white">+12,400</div>
                        <div class="text-xs uppercase tracking-widest mt-0.5" style="color:rgba(232,48,122,.8);">Jugadores activos</div>
                    </div>
                    <div class="w-px h-10" style="background:rgba(255,255,255,.1);"></div>
                    <div>
                        <div class="font-display text-2xl font-bold text-white">1.2M</div>
                        <div class="text-xs uppercase tracking-widest mt-0.5" style="color:rgba(232,48,122,.8);">Puntos canjeados</div>
                    </div>
                    <div class="w-px h-10" style="background:rgba(255,255,255,.1);"></div>
                    <div>
                        <div class="font-display text-2xl font-bold text-white">98%</div>
                        <div class="text-xs uppercase tracking-widest mt-0.5" style="color:rgba(232,48,122,.8);">Satisfacción</div>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT: Ring Visual ── -->
            <div class="lg:w-7/12 flex justify-center items-center relative animate-scale-in d3">

                <!-- Outer glow bloom -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-[400px] h-[400px] rounded-full"
                         style="background: radial-gradient(circle, rgba(232,48,122,.22) 0%, transparent 65%); animation: pulseGlow 5s ease-in-out infinite;"></div>
                </div>

                <!-- Main ring SVG -->
                <div class="relative w-[360px] h-[360px] flex-shrink-0">
                    <svg width="360" height="360" viewBox="0 0 360 360" class="w-full h-full">
                        <defs>
                            <linearGradient id="g1" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FF6BB0"/>
                                <stop offset="100%" stop-color="#CC1A65"/>
                            </linearGradient>
                            <linearGradient id="g2" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#E8307A"/>
                                <stop offset="100%" stop-color="#840B3B"/>
                            </linearGradient>
                            <linearGradient id="g3" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FF8CC8"/>
                                <stop offset="100%" stop-color="#E8307A"/>
                            </linearGradient>
                            <filter id="glow">
                                <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                        </defs>
                        <!-- Track circles -->
                        <circle cx="180" cy="180" r="160" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="14"/>
                        <circle cx="180" cy="180" r="125" fill="none" stroke="rgba(255,255,255,.05)" stroke-width="12"/>
                        <circle cx="180" cy="180" r="90"  fill="none" stroke="rgba(255,255,255,.05)" stroke-width="10"/>
                        <!-- Filled rings -->
                        <circle cx="180" cy="180" r="160" fill="none" stroke="url(#g1)" stroke-width="14"
                                stroke-linecap="round" filter="url(#glow)" class="ring-1"/>
                        <circle cx="180" cy="180" r="125" fill="none" stroke="url(#g2)" stroke-width="12"
                                stroke-linecap="round" filter="url(#glow)" class="ring-2"/>
                        <circle cx="180" cy="180" r="90"  fill="none" stroke="url(#g3)" stroke-width="10"
                                stroke-linecap="round" filter="url(#glow)" class="ring-3"/>
                        <!-- Ring labels at the end (dots) -->
                        <!-- Outer ring label -->
                        <text x="204" y="26" fill="rgba(255,255,255,.5)" font-size="10" font-family="Inter" text-anchor="middle">Meta</text>
                        <text x="204" y="38" fill="#FF6BB0" font-size="11" font-family="Space Grotesk" font-weight="700" text-anchor="middle">80%</text>
                        <!-- Middle ring label -->
                        <text x="292" y="120" fill="rgba(255,255,255,.5)" font-size="10" font-family="Inter" text-anchor="middle">Nivel</text>
                        <text x="292" y="132" fill="#E8307A" font-size="11" font-family="Space Grotesk" font-weight="700" text-anchor="middle">65%</text>
                        <!-- Inner ring label -->
                        <text x="270" y="62" fill="rgba(255,255,255,.5)" font-size="10" font-family="Inter" text-anchor="middle">Racha</text>
                        <text x="270" y="74" fill="#FF8CC8" font-size="11" font-family="Space Grotesk" font-weight="700" text-anchor="middle">75%</text>
                    </svg>

                    <!-- Center content -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div class="text-center">
                            <div class="font-display text-5xl font-bold text-white leading-none"
                                 x-text="points.toLocaleString('es-MX')">0</div>
                            <div class="text-xs font-bold uppercase tracking-[.15em] mt-2"
                                 style="color:rgba(232,48,122,.9);">Puntos</div>
                            <div class="mt-3 flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-neon-400" style="animation:pulseNeon 2s infinite;"></span>
                                <span class="text-xs font-medium" style="color:rgba(255,255,255,.55);">Nivel Bronze</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating achievement card: Streak -->
                <div class="glass-neon rounded-2xl p-4 absolute -top-4 -left-4 animate-float-a glow-border" style="min-width:170px;">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                             style="background:rgba(255,100,0,.15); border:1px solid rgba(255,100,0,.3);">
                            🔥
                        </div>
                        <div>
                            <div class="font-display text-xl font-bold text-white">7 días</div>
                            <div class="text-xs font-semibold mt-0.5" style="color:rgba(255,150,0,.9);">Racha activa</div>
                        </div>
                    </div>
                    <div class="mt-3 h-1.5 rounded-full" style="background:rgba(255,255,255,.08);">
                        <div class="h-full rounded-full" style="width:70%; background:linear-gradient(90deg,#FF6400,#FFB300); box-shadow:0 0 8px rgba(255,100,0,.6);"></div>
                    </div>
                </div>

                <!-- Floating card: Earned today -->
                <div class="glass-dark rounded-2xl p-4 absolute -bottom-6 -left-8 animate-float-b glow-border" style="min-width:160px;">
                    <div class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:rgba(255,255,255,.45);">Hoy ganaste</div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⭐</span>
                        <span class="font-display text-2xl font-bold" style="color:#FFD700;">+50 pts</span>
                    </div>
                    <div class="text-xs mt-1.5" style="color:rgba(255,255,255,.4);">Compra verificada</div>
                </div>

                <!-- Floating card: Next reward -->
                <div class="absolute -right-6 top-1/2 -translate-y-1/2 animate-float-c" style="max-width:175px;">
                    <div class="glass-neon rounded-2xl p-4 glow-border-strong">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-lock-open text-neon-400 text-sm"></i>
                            <span class="text-xs font-bold uppercase tracking-wide text-neon-300">Próxima recompensa</span>
                        </div>
                        <div class="text-base font-display font-bold text-white mb-2">Pastel gratis</div>
                        <div class="h-1.5 rounded-full mb-1.5" style="background:rgba(255,255,255,.08);">
                            <div class="h-full rounded-full" style="width:68%; background:linear-gradient(90deg,#CC1A65,#FF6BB0); box-shadow:0 0 10px rgba(232,48,122,.7);"></div>
                        </div>
                        <div class="text-xs" style="color:rgba(255,255,255,.45);">158 pts más</div>
                    </div>
                </div>

                <!-- Achievement pop (top right) -->
                <div class="glass-dark rounded-2xl px-4 py-3 absolute top-4 right-0 animate-float-a" style="animation-delay:2s;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-lg"
                             style="background:linear-gradient(135deg,#CC1A65,#E8307A);">
                            🏅
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white">¡Logro desbloqueado!</div>
                            <div class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.5);">Racha de 7 días</div>
                        </div>
                    </div>
                </div>

            </div>
        </div><!-- /hero cols -->
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     STATS TICKER
══════════════════════════════════════════════════════════ -->
<div style="background:rgba(232,48,122,.06); border-top:1px solid rgba(232,48,122,.12); border-bottom:1px solid rgba(232,48,122,.12); padding:16px 0; overflow:hidden;">
    <div class="ticker-wrap">
        <div class="ticker-inner">
            <!-- Duplicated for seamless loop -->
            <?php
            $items = [
                ['🎂', '+342 puntos canjeados hoy'],
                ['🔥', '312 racha activa más larga'],
                ['👑', 'Nuevo miembro Oro: @MarisolG'],
                ['⭐', '8,420 puntos acumulados esta semana'],
                ['🎁', '24 recompensas canjeadas hoy'],
                ['🏆', '¡Logro desbloqueado: 100 compras!'],
                ['💎', 'Zona VIP disponible en Sucursal Sur'],
                ['🍓', 'Nuevo sabor: Fresas con Crema Premium'],
            ];
            // 2x for seamless ticker
            for ($i = 0; $i < 2; $i++):
                foreach ($items as $item):
            ?>
            <span class="inline-flex items-center gap-2 mx-10 text-sm font-semibold whitespace-nowrap"
                  style="color:rgba(255,255,255,.65);">
                <span><?= $item[0] ?></span>
                <span><?= $item[1] ?></span>
                <span style="color:rgba(232,48,122,.4); margin-left:8px;">·</span>
            </span>
            <?php endforeach; endfor; ?>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════
     LEVEL SYSTEM
══════════════════════════════════════════════════════════ -->
<section id="niveles" class="py-24 lg:py-32 relative" style="background:#0E0610;">
    <div class="max-w-6xl mx-auto px-6">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-tag mb-5 inline-flex">
                <i class="fa-solid fa-layer-group text-xs"></i>
                Sistema de niveles
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-white mt-5 mb-4">
                Tu camino hacia el
                <span class="text-gradient-neon"> nivel más alto</span>
            </h2>
            <p class="text-base max-w-lg mx-auto leading-relaxed" style="color:rgba(255,255,255,.5);">
                Cada compra te acerca a nuevos poderes. Sube de nivel y desbloquea beneficios que nadie más tiene.
            </p>
        </div>

        <!-- Level path (horizontal) -->
        <div class="flex items-center mb-16 reveal">

            <!-- Level 1: Bronze (active) -->
            <div class="flex flex-col items-center gap-3 flex-shrink-0">
                <div class="relative">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl animate-pulse-neon"
                         style="background:linear-gradient(135deg,#5A320A,#CD7F32); border:2px solid #CD7F32;">
                        🥉
                    </div>
                    <div class="absolute -top-2 -right-2 bg-neon-400 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                        Tú
                    </div>
                </div>
                <div class="text-center">
                    <div class="font-display font-bold text-white text-sm">Bronze</div>
                    <div class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.4);">0 – 499 pts</div>
                </div>
            </div>

            <!-- Connector 1 (active, 68% done) -->
            <div class="flex-1 mx-3 relative" style="height:6px; background:rgba(255,255,255,.07); border-radius:3px;">
                <div style="position:absolute; top:0; left:0; width:68%; height:100%; border-radius:3px; background:linear-gradient(90deg,#CD7F32,#E8307A); box-shadow:0 0 10px rgba(232,48,122,.6);"></div>
                <div class="absolute -top-6 left-[68%] -translate-x-1/2 text-[10px] font-bold" style="color:#E8307A;">68%</div>
            </div>

            <!-- Level 2: Plata -->
            <div class="flex flex-col items-center gap-3 flex-shrink-0">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl"
                     style="background:rgba(255,255,255,.06); border:2px solid rgba(180,180,210,.3); filter:brightness(.8);">
                    🥈
                </div>
                <div class="text-center">
                    <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.5);">Plata</div>
                    <div class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.25);">500 – 1,499 pts</div>
                </div>
            </div>

            <!-- Connector 2 (locked) -->
            <div class="flex-1 mx-3" style="height:6px; background:rgba(255,255,255,.06); border-radius:3px;"></div>

            <!-- Level 3: Oro -->
            <div class="flex flex-col items-center gap-3 flex-shrink-0">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl"
                     style="background:rgba(255,255,255,.06); border:2px solid rgba(255,180,0,.2); filter:brightness(.6);">
                    👑
                </div>
                <div class="text-center">
                    <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.35);">Oro</div>
                    <div class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.2);">1,500+ pts</div>
                </div>
            </div>

            <!-- Connector 3 (locked) -->
            <div class="flex-1 mx-3" style="height:6px; background:rgba(255,255,255,.06); border-radius:3px;"></div>

            <!-- Level 4: Diamante -->
            <div class="flex flex-col items-center gap-3 flex-shrink-0">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl"
                     style="background:rgba(255,255,255,.04); border:2px solid rgba(120,180,255,.15); filter:brightness(.5);">
                    💎
                </div>
                <div class="text-center">
                    <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.25);">Diamante</div>
                    <div class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.15);">3,000+ pts</div>
                </div>
            </div>

        </div>

        <!-- Level detail cards -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">

            <!-- Bronze (active) -->
            <div class="rounded-2xl p-6 glow-border-strong reveal d1"
                 style="background:linear-gradient(160deg,rgba(90,50,10,.3),rgba(205,127,50,.12));">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl">🥉</span>
                    <span class="text-[10px] font-bold bg-neon-400 text-white px-2.5 py-1 rounded-full">ACTUAL</span>
                </div>
                <h4 class="font-display text-lg font-bold text-white mb-1">Bronze</h4>
                <p class="text-xs mb-4" style="color:rgba(255,255,255,.5);">0 – 499 puntos</p>
                <ul class="space-y-2 text-xs" style="color:rgba(255,255,255,.65);">
                    <li class="flex gap-2"><span class="text-neon-400">✓</span> Acumulación de puntos</li>
                    <li class="flex gap-2"><span class="text-neon-400">✓</span> Catálogo de premios básico</li>
                    <li class="flex gap-2"><span class="text-neon-400">✓</span> Alertas de promociones</li>
                </ul>
            </div>

            <!-- Plata -->
            <div class="rounded-2xl p-6 reveal d2"
                 style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl" style="filter:grayscale(.4);">🥈</span>
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full" style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.4);">158 PTS MÁS</span>
                </div>
                <h4 class="font-display text-lg font-bold mb-1" style="color:rgba(255,255,255,.6);">Plata</h4>
                <p class="text-xs mb-4" style="color:rgba(255,255,255,.3);">500 – 1,499 puntos</p>
                <ul class="space-y-2 text-xs" style="color:rgba(255,255,255,.4);">
                    <li class="flex gap-2"><span style="color:rgba(180,180,210,.6);">✓</span> Todo lo de Bronze</li>
                    <li class="flex gap-2"><span style="color:rgba(180,180,210,.6);">✓</span> Puntos x1.5</li>
                    <li class="flex gap-2 opacity-50"><i class="fa-solid fa-lock text-[10px]"></i> Premios exclusivos</li>
                </ul>
            </div>

            <!-- Oro -->
            <div class="rounded-2xl p-6 reveal d3"
                 style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.05);">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl" style="filter:grayscale(.6);">👑</span>
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full" style="background:rgba(255,255,255,.04); color:rgba(255,255,255,.25);">1,158 PTS MÁS</span>
                </div>
                <h4 class="font-display text-lg font-bold mb-1" style="color:rgba(255,255,255,.35);">Oro</h4>
                <p class="text-xs mb-4" style="color:rgba(255,255,255,.2);">1,500+ puntos</p>
                <ul class="space-y-2 text-xs" style="color:rgba(255,255,255,.25);">
                    <li class="flex gap-2 opacity-50"><i class="fa-solid fa-lock text-[10px]"></i> Todo lo de Plata</li>
                    <li class="flex gap-2 opacity-50"><i class="fa-solid fa-lock text-[10px]"></i> Puntos x2</li>
                    <li class="flex gap-2 opacity-50"><i class="fa-solid fa-lock text-[10px]"></i> Pastel anual gratis</li>
                </ul>
            </div>

            <!-- Diamante -->
            <div class="rounded-2xl p-6 reveal d4"
                 style="background:rgba(255,255,255,.02); border:1px solid rgba(120,180,255,.06);">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl" style="filter:grayscale(.8) brightness(.5);">💎</span>
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full" style="background:rgba(255,255,255,.03); color:rgba(255,255,255,.15);">MISTERIOSO</span>
                </div>
                <h4 class="font-display text-lg font-bold mb-1" style="color:rgba(255,255,255,.2);">Diamante</h4>
                <p class="text-xs mb-4" style="color:rgba(255,255,255,.12);">3,000+ puntos</p>
                <ul class="space-y-2 text-xs" style="color:rgba(255,255,255,.15);">
                    <li class="flex gap-2"><i class="fa-solid fa-lock text-[10px]"></i> <span class="blur-[2px]">Beneficio oculto</span></li>
                    <li class="flex gap-2"><i class="fa-solid fa-lock text-[10px]"></i> <span class="blur-[2px]">Acceso secreto</span></li>
                    <li class="flex gap-2"><i class="fa-solid fa-lock text-[10px]"></i> <span class="blur-[2px]">Recompensa VIP</span></li>
                </ul>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     ACHIEVEMENTS / LOGROS
══════════════════════════════════════════════════════════ -->
<section id="logros" class="py-24 lg:py-32"
         style="background:linear-gradient(180deg, #0E0610 0%, #130816 50%, #0E0610 100%);">
    <div class="max-w-6xl mx-auto px-6">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-tag mb-5 inline-flex">
                <i class="fa-solid fa-trophy text-xs"></i>
                Tus logros
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-white mt-5 mb-4">
                Colecciona logros,
                <span class="text-gradient-neon"> gana más puntos</span>
            </h2>
            <p class="text-base max-w-lg mx-auto" style="color:rgba(255,255,255,.5);">
                Completa misiones especiales y desbloquea insignias exclusivas que multiplican tus recompensas.
            </p>
        </div>

        <!-- Badges grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">

            <!-- UNLOCKED badges -->
            <div class="badge-unlocked glass-neon rounded-2xl p-5 glow-border text-center reveal d1">
                <div class="text-5xl mb-3">🎂</div>
                <div class="font-display font-bold text-white text-sm mb-1">Primera Compra</div>
                <div class="text-[10px] font-semibold text-neon-300">+25 pts extra</div>
                <div class="mt-3 text-[10px] px-3 py-1 rounded-full inline-block" style="background:rgba(232,48,122,.15); color:rgba(232,48,122,.9);">
                    ✓ Desbloqueado
                </div>
            </div>

            <div class="badge-unlocked glass-neon rounded-2xl p-5 glow-border text-center reveal d2">
                <div class="text-5xl mb-3">🔥</div>
                <div class="font-display font-bold text-white text-sm mb-1">Racha de 7 días</div>
                <div class="text-[10px] font-semibold text-neon-300">+50 pts extra</div>
                <div class="mt-3 text-[10px] px-3 py-1 rounded-full inline-block" style="background:rgba(232,48,122,.15); color:rgba(232,48,122,.9);">
                    ✓ Desbloqueado
                </div>
            </div>

            <div class="badge-unlocked glass-neon rounded-2xl p-5 glow-border text-center reveal d3">
                <div class="text-5xl mb-3">🍓</div>
                <div class="font-display font-bold text-white text-sm mb-1">Fan de Fresas</div>
                <div class="text-[10px] font-semibold text-neon-300">+30 pts extra</div>
                <div class="mt-3 text-[10px] px-3 py-1 rounded-full inline-block" style="background:rgba(232,48,122,.15); color:rgba(232,48,122,.9);">
                    ✓ Desbloqueado
                </div>
            </div>

            <div class="badge-unlocked glass-neon rounded-2xl p-5 glow-border text-center reveal d4">
                <div class="text-5xl mb-3">⭐</div>
                <div class="font-display font-bold text-white text-sm mb-1">Fidelidad Bronze</div>
                <div class="text-[10px] font-semibold text-neon-300">+40 pts extra</div>
                <div class="mt-3 text-[10px] px-3 py-1 rounded-full inline-block" style="background:rgba(232,48,122,.15); color:rgba(232,48,122,.9);">
                    ✓ Desbloqueado
                </div>
            </div>

            <!-- LOCKED badges -->
            <div class="badge-locked glass-dark rounded-2xl p-5 text-center reveal d1 relative">
                <div class="text-5xl mb-3">🏆</div>
                <div class="font-display font-bold text-sm mb-1" style="color:rgba(255,255,255,.3);">100 Compras</div>
                <div class="text-[10px] font-semibold" style="color:rgba(255,255,255,.2);">??? pts extra</div>
                <div class="absolute inset-0 flex items-center justify-center rounded-2xl">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(14,6,16,.9); border:1px solid rgba(255,255,255,.1);">
                        <i class="fa-solid fa-lock text-sm" style="color:rgba(255,255,255,.4);"></i>
                    </div>
                </div>
            </div>

            <div class="badge-locked glass-dark rounded-2xl p-5 text-center reveal d2 relative">
                <div class="text-5xl mb-3">👑</div>
                <div class="font-display font-bold text-sm mb-1" style="color:rgba(255,255,255,.3);">Nivel Oro</div>
                <div class="text-[10px] font-semibold" style="color:rgba(255,255,255,.2);">??? pts extra</div>
                <div class="absolute inset-0 flex items-center justify-center rounded-2xl">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(14,6,16,.9); border:1px solid rgba(255,255,255,.1);">
                        <i class="fa-solid fa-lock text-sm" style="color:rgba(255,255,255,.4);"></i>
                    </div>
                </div>
            </div>

            <div class="badge-locked glass-dark rounded-2xl p-5 text-center reveal d3 relative">
                <div class="text-5xl mb-3">💎</div>
                <div class="font-display font-bold text-sm mb-1" style="color:rgba(255,255,255,.3);">Miembro VIP</div>
                <div class="text-[10px] font-semibold" style="color:rgba(255,255,255,.2);">??? pts extra</div>
                <div class="absolute inset-0 flex items-center justify-center rounded-2xl">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(14,6,16,.9); border:1px solid rgba(255,255,255,.1);">
                        <i class="fa-solid fa-lock text-sm" style="color:rgba(255,255,255,.4);"></i>
                    </div>
                </div>
            </div>

            <div class="badge-locked glass-dark rounded-2xl p-5 text-center reveal d4 relative">
                <div class="text-5xl mb-3">🌟</div>
                <div class="font-display font-bold text-sm mb-1" style="color:rgba(255,255,255,.3);">Leyenda Zarza</div>
                <div class="text-[10px] font-semibold" style="color:rgba(255,255,255,.2);">??? pts extra</div>
                <div class="absolute inset-0 flex items-center justify-center rounded-2xl">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(14,6,16,.9); border:1px solid rgba(255,255,255,.1);">
                        <i class="fa-solid fa-lock text-sm" style="color:rgba(255,255,255,.4);"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     MONTHLY GOALS / METAS
══════════════════════════════════════════════════════════ -->
<section id="metas" class="py-24 lg:py-32" style="background:#0E0610;">
    <div class="max-w-6xl mx-auto px-6">

        <div class="flex flex-col lg:flex-row gap-16 items-start">

            <!-- Left: heading + description -->
            <div class="lg:w-2/5 reveal">
                <span class="section-tag mb-6 inline-flex">
                    <i class="fa-solid fa-chart-line text-xs"></i>
                    Metas de mayo
                </span>
                <h2 class="font-display text-4xl lg:text-5xl font-bold text-white mt-5 mb-5 leading-tight">
                    ¿Cuánto
                    <span class="text-gradient-neon">te falta</span>
                    para el siguiente nivel?
                </h2>
                <p class="mb-8 leading-relaxed" style="color:rgba(255,255,255,.5);">
                    Sigue tu progreso en tiempo real. Cada meta completada te acerca más rápido a tu próxima recompensa.
                </p>
                <div class="glass-neon rounded-2xl p-5 glow-border">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fa-solid fa-bolt text-neon-400"></i>
                        <span class="font-display font-bold text-white text-sm">Tip de esta semana</span>
                    </div>
                    <p class="text-xs leading-relaxed" style="color:rgba(255,255,255,.55);">
                        Compra en horario de 2–6pm de lunes a viernes y gana
                        <strong class="text-neon-300">puntos dobles</strong> automáticamente.
                    </p>
                </div>
            </div>

            <!-- Right: progress bars -->
            <div class="lg:w-3/5 space-y-7 reveal d2"
                 x-data="{
                     bars: [
                         { label:'Compras del mes',  sub:'3 de 5 completadas',  w:'60%',  val:3,  total:5,  color:'from-neon-600 to-neon-400',  emoji:'🛍️'},
                         { label:'Puntos acumulados', sub:'342 de 500 pts',     w:'68%',  val:342, total:500, color:'from-neon-600 to-neon-300', emoji:'⭐'},
                         { label:'Visitas a sucursal',sub:'2 de 4 completadas', w:'50%',  val:2,  total:4,  color:'from-neon-700 to-neon-500',  emoji:'📍'},
                         { label:'Productos probados',sub:'3 de 6 sabores',     w:'50%',  val:3,  total:6,  color:'from-neon-800 to-neon-500',  emoji:'🎂'},
                     ]
                 }">
                <template x-for="bar in bars" :key="bar.label">
                    <div class="glass-dark rounded-2xl p-5 glow-border">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xl" x-text="bar.emoji"></span>
                                <div>
                                    <div class="font-display font-bold text-white text-sm" x-text="bar.label"></div>
                                    <div class="text-xs mt-0.5" style="color:rgba(255,255,255,.4);" x-text="bar.sub"></div>
                                </div>
                            </div>
                            <div class="font-display text-xl font-bold text-white" x-text="bar.w"></div>
                        </div>
                        <!-- Progress bar -->
                        <div class="h-3 rounded-full overflow-hidden" style="background:rgba(255,255,255,.06);">
                            <div class="progress-bar-fill" :style="'--w:'+bar.w+'; background:linear-gradient(90deg,#CC1A65,#E8307A,#FF6BB0); box-shadow:0 0 12px rgba(232,48,122,.6);'"></div>
                        </div>
                        <!-- Mini markers -->
                        <div class="flex justify-between mt-2">
                            <span class="text-[10px]" style="color:rgba(255,255,255,.25);">0</span>
                            <span class="text-[10px]" style="color:rgba(232,48,122,.7);">
                                <i class="fa-solid fa-flag text-[8px]"></i>
                                Meta
                            </span>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     LOCKED REWARDS TEASER
══════════════════════════════════════════════════════════ -->
<section id="recompensas" class="py-24 lg:py-32"
         style="background:linear-gradient(180deg,#0E0610 0%,#130816 100%);">
    <div class="max-w-6xl mx-auto px-6">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-tag mb-5 inline-flex">
                <i class="fa-solid fa-gift text-xs"></i>
                Recompensas esperando
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-white mt-5 mb-4">
                Recompensas que
                <span class="text-gradient-neon"> te esperan</span>
            </h2>
            <p class="text-base max-w-lg mx-auto" style="color:rgba(255,255,255,.5);">
                Estas recompensas están a tu alcance. Acumula los puntos que faltan y desbloquéalas antes de que otros lo hagan.
            </p>
        </div>

        <!-- Reward cards (2 unlocked, 4 locked) -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- UNLOCKED -->
            <div class="locked-card glass-neon rounded-2xl p-6 glow-border-strong reveal d1">
                <!-- ══ IMAGEN: public/proposals/p2-reward-1.jpg  (400×180px) ══ -->
                <div class="-mx-6 -mt-6 h-32 mb-5 overflow-hidden rounded-t-2xl" id="p2R1Wrap">
                    <img src="/proposals/p2-reward-1.jpg" alt="Descuento La Zarza"
                         class="w-full h-full object-cover"
                         style="opacity:.85;"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:linear-gradient(135deg,rgba(204,26,101,.3),rgba(232,48,122,.2));">
                        💸
                    </div>
                    <span class="text-[10px] font-bold bg-green-500 bg-opacity-20 text-green-400 px-3 py-1 rounded-full border border-green-500 border-opacity-30">
                        ✓ DISPONIBLE
                    </span>
                </div>
                <h4 class="font-display font-bold text-white text-lg mb-2">5% de descuento</h4>
                <p class="text-sm mb-5" style="color:rgba(255,255,255,.5);">Aplica en tu siguiente compra en cualquier sucursal.</p>
                <div class="flex items-center justify-between">
                    <span class="font-display text-2xl font-bold text-neon-400">200 pts</span>
                    <a href="/register" class="btn-neon text-xs px-5 py-2.5" style="border-radius:12px;">
                        Canjear
                    </a>
                </div>
            </div>

            <!-- UNLOCKED 2 -->
            <div class="locked-card glass-neon rounded-2xl p-6 glow-border-strong reveal d2">
                <!-- ══ IMAGEN: public/proposals/p2-reward-2.jpg  (400×180px) ══ -->
                <div class="-mx-6 -mt-6 h-32 mb-5 overflow-hidden rounded-t-2xl" id="p2R2Wrap">
                    <img src="/proposals/p2-reward-2.jpg" alt="Envío gratis La Zarza"
                         class="w-full h-full object-cover"
                         style="opacity:.85;"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:linear-gradient(135deg,rgba(204,26,101,.3),rgba(232,48,122,.2));">
                        🚚
                    </div>
                    <span class="text-[10px] font-bold bg-green-500 bg-opacity-20 text-green-400 px-3 py-1 rounded-full border border-green-500 border-opacity-30">
                        ✓ DISPONIBLE
                    </span>
                </div>
                <h4 class="font-display font-bold text-white text-lg mb-2">Envío gratis</h4>
                <p class="text-sm mb-5" style="color:rgba(255,255,255,.5);">Entrega a domicilio sin costo en cualquier pedido.</p>
                <div class="flex items-center justify-between">
                    <span class="font-display text-2xl font-bold text-neon-400">150 pts</span>
                    <a href="/register" class="btn-neon text-xs px-5 py-2.5" style="border-radius:12px;">
                        Canjear
                    </a>
                </div>
            </div>

            <!-- LOCKED: Pastel personalizado -->
            <div class="locked-card glass-dark rounded-2xl p-6 reveal d3" style="border:1px solid rgba(255,255,255,.07);">
                <!-- ══ IMAGEN: public/proposals/p2-reward-3.jpg  (400×180px, se muestra en escala grises) ══ -->
                <div class="-mx-6 -mt-6 h-32 mb-5 overflow-hidden rounded-t-2xl" id="p2R3Wrap">
                    <img src="/proposals/p2-reward-3.jpg" alt="Pastel personalizado La Zarza"
                         class="w-full h-full object-cover"
                         style="filter:grayscale(1) brightness(.5);"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:rgba(255,255,255,.05); filter:grayscale(.5);">
                        🎂
                    </div>
                    <span class="text-[10px] font-bold px-3 py-1 rounded-full" style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.3); border:1px solid rgba(255,255,255,.08);">
                        🔒 BLOQUEADO
                    </span>
                </div>
                <h4 class="font-display font-bold text-lg mb-2" style="color:rgba(255,255,255,.5);">Pastel personalizado</h4>
                <p class="text-sm mb-4" style="color:rgba(255,255,255,.3);">Tu pastel de autor diseñado a tu gusto.</p>
                <div class="mb-4">
                    <div class="flex justify-between text-xs mb-2">
                        <span style="color:rgba(255,255,255,.35);">Tu progreso</span>
                        <span style="color:rgba(232,48,122,.7);">342 / 500 pts</span>
                    </div>
                    <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,.06);">
                        <div style="width:68%; height:100%; background:linear-gradient(90deg,#8B1050,#E8307A); border-radius:999px; box-shadow:0 0 10px rgba(232,48,122,.5);"></div>
                    </div>
                    <div class="text-[10px] mt-1.5" style="color:rgba(232,48,122,.6);">¡Solo 158 pts más!</div>
                </div>
                <a href="/register" class="btn-ghost text-xs w-full justify-center" style="padding:10px 16px; border-radius:12px;">
                    Desbloquear pronto
                </a>
            </div>

            <!-- LOCKED: Clase de Repostería -->
            <div class="locked-card glass-dark rounded-2xl p-6 reveal d1" style="border:1px solid rgba(255,255,255,.06);">
                <!-- ══ IMAGEN: public/proposals/p2-reward-4.jpg  (400×180px, se muestra en escala grises) ══ -->
                <div class="-mx-6 -mt-6 h-32 mb-5 overflow-hidden rounded-t-2xl" id="p2R4Wrap">
                    <img src="/proposals/p2-reward-4.jpg" alt="Clase de repostería La Zarza"
                         class="w-full h-full object-cover"
                         style="filter:grayscale(1) brightness(.4);"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:rgba(255,255,255,.04); filter:grayscale(.7);">
                        👩‍🍳
                    </div>
                    <span class="text-[10px] font-bold px-3 py-1 rounded-full" style="background:rgba(255,255,255,.05); color:rgba(255,255,255,.25); border:1px solid rgba(255,255,255,.06);">
                        🔒 BLOQUEADO
                    </span>
                </div>
                <h4 class="font-display font-bold text-lg mb-2" style="color:rgba(255,255,255,.4);">Clase de repostería</h4>
                <p class="text-sm mb-4" style="color:rgba(255,255,255,.25);">Aprende con nuestros maestros pasteleros.</p>
                <div class="mb-4">
                    <div class="flex justify-between text-xs mb-2">
                        <span style="color:rgba(255,255,255,.25);">Tu progreso</span>
                        <span style="color:rgba(232,48,122,.5);">342 / 1,200 pts</span>
                    </div>
                    <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,.05);">
                        <div style="width:28%; height:100%; background:linear-gradient(90deg,#5A0829,#CC1A65); border-radius:999px;"></div>
                    </div>
                </div>
                <button class="btn-ghost text-xs w-full justify-center opacity-50 cursor-not-allowed" style="padding:10px 16px; border-radius:12px;">
                    Requiere 858 pts más
                </button>
            </div>

            <!-- LOCKED: Mesa VIP -->
            <div class="locked-card glass-dark rounded-2xl p-6 reveal d2" style="border:1px solid rgba(255,255,255,.06);">
                <!-- ══ IMAGEN: public/proposals/p2-reward-5.jpg  (400×180px, se muestra en escala grises) ══ -->
                <div class="-mx-6 -mt-6 h-32 mb-5 overflow-hidden rounded-t-2xl" id="p2R5Wrap">
                    <img src="/proposals/p2-reward-5.jpg" alt="Mesa VIP La Zarza"
                         class="w-full h-full object-cover"
                         style="filter:grayscale(1) brightness(.4);"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:rgba(255,255,255,.04); filter:grayscale(.7);">
                        🥂
                    </div>
                    <span class="text-[10px] font-bold px-3 py-1 rounded-full" style="background:rgba(255,255,255,.05); color:rgba(255,255,255,.25); border:1px solid rgba(255,255,255,.06);">
                        🔒 BLOQUEADO
                    </span>
                </div>
                <h4 class="font-display font-bold text-lg mb-2" style="color:rgba(255,255,255,.4);">Mesa VIP reservada</h4>
                <p class="text-sm mb-4" style="color:rgba(255,255,255,.25);">Noche exclusiva con experiencia gourmet.</p>
                <div class="mb-4">
                    <div class="flex justify-between text-xs mb-2">
                        <span style="color:rgba(255,255,255,.25);">Tu progreso</span>
                        <span style="color:rgba(232,48,122,.5);">342 / 600 pts</span>
                    </div>
                    <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,.05);">
                        <div style="width:57%; height:100%; background:linear-gradient(90deg,#6B0D3E,#E8307A); border-radius:999px; box-shadow:0 0 8px rgba(232,48,122,.4);"></div>
                    </div>
                    <div class="text-[10px] mt-1.5" style="color:rgba(232,48,122,.5);">258 pts más</div>
                </div>
                <button class="btn-ghost text-xs w-full justify-center opacity-50 cursor-not-allowed" style="padding:10px 16px; border-radius:12px;">
                    Requiere 258 pts más
                </button>
            </div>

            <!-- LOCKED: Caja Premium -->
            <div class="locked-card glass-dark rounded-2xl p-6 reveal d3" style="border:1px solid rgba(255,255,255,.06);">
                <!-- ══ IMAGEN: public/proposals/p2-reward-6.jpg  (400×180px, se muestra en escala grises) ══ -->
                <div class="-mx-6 -mt-6 h-32 mb-5 overflow-hidden rounded-t-2xl" id="p2R6Wrap">
                    <img src="/proposals/p2-reward-6.jpg" alt="Caja Premium La Zarza"
                         class="w-full h-full object-cover"
                         style="filter:grayscale(1) brightness(.4);"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:rgba(255,255,255,.04); filter:grayscale(.7);">
                        🎁
                    </div>
                    <span class="text-[10px] font-bold text-yellow-400 px-3 py-1 rounded-full"
                          style="background:rgba(255,180,0,.1); border:1px solid rgba(255,180,0,.2);">
                        ⏰ TIEMPO LIMITADO
                    </span>
                </div>
                <h4 class="font-display font-bold text-lg mb-2" style="color:rgba(255,255,255,.4);">Caja Premium Zarza</h4>
                <p class="text-sm mb-4" style="color:rgba(255,255,255,.25);">Selección curada de nuestros mejores productos.</p>
                <div class="mb-4">
                    <div class="flex justify-between text-xs mb-2">
                        <span style="color:rgba(255,255,255,.25);">Tu progreso</span>
                        <span style="color:rgba(232,48,122,.5);">342 / 400 pts</span>
                    </div>
                    <div class="h-2 rounded-full overflow-hidden" style="background:rgba(255,255,255,.05);">
                        <div style="width:85%; height:100%; background:linear-gradient(90deg,#840B3B,#FF6BB0); border-radius:999px; box-shadow:0 0 12px rgba(232,48,122,.6);"></div>
                    </div>
                    <div class="text-[10px] mt-1.5" style="color:rgba(255,200,0,.8);">¡Solo 58 pts más! Expira el 31 mayo</div>
                </div>
                <a href="/register" class="btn-ghost text-xs w-full justify-center" style="padding:10px 16px; border-radius:12px; border-color:rgba(255,180,0,.3); color:rgba(255,200,0,.8);">
                    ¡Casi lo tienes! Únete
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     COMMUNITY LEADERBOARD
══════════════════════════════════════════════════════════ -->
<section class="py-24 lg:py-28" style="background:#0E0610;">
    <div class="max-w-6xl mx-auto px-6">

        <div class="flex flex-col lg:flex-row gap-12 items-start">

            <!-- Leaderboard -->
            <div class="lg:w-1/2 reveal">
                <span class="section-tag mb-6 inline-flex">
                    <i class="fa-solid fa-ranking-star text-xs"></i>
                    Top este mes
                </span>
                <h2 class="font-display text-3xl lg:text-4xl font-bold text-white mt-5 mb-8">
                    Los más<br>
                    <span class="text-gradient-neon">comprometidos</span>
                </h2>

                <div class="space-y-3">
                    <!-- Rank 1 -->
                    <div class="glass-neon rounded-2xl p-4 glow-border flex items-center gap-4">
                        <div class="font-display text-2xl font-bold" style="color:rgba(255,215,0,.9); width:28px; text-align:center;">1</div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                             style="background:linear-gradient(135deg,#CC1A65,#E8307A);">MG</div>
                        <div class="flex-1">
                            <div class="font-display font-bold text-white text-sm">Marisol González</div>
                            <div class="text-xs mt-0.5" style="color:rgba(255,255,255,.4);">Nivel Oro · Sucursal Norte</div>
                        </div>
                        <div class="text-right">
                            <div class="font-display font-bold text-neon-400 text-base">1,842 pts</div>
                            <div class="text-[10px]" style="color:rgba(255,255,255,.3);">este mes</div>
                        </div>
                    </div>
                    <!-- Rank 2 -->
                    <div class="glass-dark rounded-2xl p-4 flex items-center gap-4" style="border:1px solid rgba(255,255,255,.07);">
                        <div class="font-display text-2xl font-bold" style="color:rgba(192,192,192,.8); width:28px; text-align:center;">2</div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                             style="background:rgba(255,255,255,.1);">CR</div>
                        <div class="flex-1">
                            <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.75);">Carlos Ramírez</div>
                            <div class="text-xs mt-0.5" style="color:rgba(255,255,255,.3);">Nivel Plata · Sucursal Sur</div>
                        </div>
                        <div class="text-right">
                            <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.6);">1,234 pts</div>
                            <div class="text-[10px]" style="color:rgba(255,255,255,.25);">este mes</div>
                        </div>
                    </div>
                    <!-- Rank 3 -->
                    <div class="glass-dark rounded-2xl p-4 flex items-center gap-4" style="border:1px solid rgba(255,255,255,.06);">
                        <div class="font-display text-2xl font-bold" style="color:rgba(205,127,50,.8); width:28px; text-align:center;">3</div>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                             style="background:rgba(255,255,255,.08);">AL</div>
                        <div class="flex-1">
                            <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.65);">Ana Luisa Torres</div>
                            <div class="text-xs mt-0.5" style="color:rgba(255,255,255,.3);">Nivel Bronze · Sucursal Centro</div>
                        </div>
                        <div class="text-right">
                            <div class="font-display font-bold text-sm" style="color:rgba(255,255,255,.5);">987 pts</div>
                            <div class="text-[10px]" style="color:rgba(255,255,255,.2);">este mes</div>
                        </div>
                    </div>
                    <!-- "You" row -->
                    <div class="rounded-2xl p-4 flex items-center gap-4"
                         style="background:rgba(232,48,122,.08); border:1px solid rgba(232,48,122,.25);">
                        <div class="font-display text-2xl font-bold text-neon-400" style="width:28px; text-align:center;">?</div>
                        <div class="w-10 h-10 rounded-full border-2 border-neon-400 border-dashed flex items-center justify-center text-neon-400">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-display font-bold text-white text-sm">Tú (próximamente)</div>
                            <div class="text-xs mt-0.5 text-neon-300">¡Únete y empieza a competir!</div>
                        </div>
                        <a href="/register"
                           class="text-xs font-bold text-neon-400 hover:text-white transition-colors duration-200">
                            Unirse →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: CTA card -->
            <div class="lg:w-1/2 reveal d2">
                <div class="glass-neon rounded-3xl p-10 glow-border-strong text-center">
                    <div class="text-6xl mb-6">🏆</div>
                    <h3 class="font-display text-3xl font-bold text-white mb-4 leading-tight">
                        El mejor jugador del mes gana una
                        <span class="text-gradient-neon">experiencia VIP</span>
                    </h3>
                    <p class="mb-8 leading-relaxed" style="color:rgba(255,255,255,.55);">
                        Cena privada, pastel personalizado y tarjeta de regalo de $500 para el miembro más activo de cada mes.
                    </p>
                    <div class="flex items-center justify-center gap-4 mb-8">
                        <div class="text-center">
                            <div class="font-display text-3xl font-bold text-neon-400">28</div>
                            <div class="text-xs" style="color:rgba(255,255,255,.4);">días</div>
                        </div>
                        <div style="color:rgba(255,255,255,.2);">:</div>
                        <div class="text-center">
                            <div class="font-display text-3xl font-bold text-neon-400">14</div>
                            <div class="text-xs" style="color:rgba(255,255,255,.4);">horas</div>
                        </div>
                        <div style="color:rgba(255,255,255,.2);">:</div>
                        <div class="text-center">
                            <div class="font-display text-3xl font-bold text-neon-400">32</div>
                            <div class="text-xs" style="color:rgba(255,255,255,.4);">min</div>
                        </div>
                    </div>
                    <a href="/register" class="btn-neon text-base w-full justify-center">
                        <i class="fa-solid fa-trophy text-yellow-300 text-sm"></i>
                        Quiero competir
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ -->
<footer style="background:#080410; border-top:1px solid rgba(232,48,122,.1); padding-top:64px; padding-bottom:40px;">
    <div class="max-w-6xl mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-10 pb-14" style="border-bottom:1px solid rgba(255,255,255,.06);">

            <!-- Brand -->
            <div class="md:col-span-2">
                <img src="/lazarzacontigowhite.png" alt="La Zarza Contigo" class="h-10 w-auto object-contain mb-5">
                <p class="text-sm leading-relaxed max-w-xs mb-6" style="color:rgba(255,255,255,.4);">
                    El programa de lealtad más adictivo del mundo de la repostería. Cada punto cuenta, cada recompensa importa.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200"
                       style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.5);"
                       onmouseover="this.style.background='rgba(232,48,122,.15)'; this.style.color='#E8307A'"
                       onmouseout="this.style.background='rgba(255,255,255,.06)'; this.style.color='rgba(255,255,255,.5)'">
                        <i class="fa-brands fa-instagram text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200"
                       style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.5);"
                       onmouseover="this.style.background='rgba(232,48,122,.15)'; this.style.color='#E8307A'"
                       onmouseout="this.style.background='rgba(255,255,255,.06)'; this.style.color='rgba(255,255,255,.5)'">
                        <i class="fa-brands fa-facebook text-sm"></i>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-200"
                       style="background:rgba(255,255,255,.06); color:rgba(255,255,255,.5);"
                       onmouseover="this.style.background='rgba(232,48,122,.15)'; this.style.color='#E8307A'"
                       onmouseout="this.style.background='rgba(255,255,255,.06)'; this.style.color='rgba(255,255,255,.5)'">
                        <i class="fa-brands fa-tiktok text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Links -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-5" style="color:rgba(232,48,122,.7);">Plataforma</h4>
                <ul class="space-y-3 text-sm" style="color:rgba(255,255,255,.4);">
                    <li><a href="#niveles"     class="hover:text-white transition-colors duration-200">Niveles</a></li>
                    <li><a href="#logros"      class="hover:text-white transition-colors duration-200">Logros</a></li>
                    <li><a href="#metas"       class="hover:text-white transition-colors duration-200">Metas del mes</a></li>
                    <li><a href="#recompensas" class="hover:text-white transition-colors duration-200">Recompensas</a></li>
                    <li><a href="/sucursales"  class="hover:text-white transition-colors duration-200">Sucursales</a></li>
                </ul>
            </div>

            <!-- Account -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-widest mb-5" style="color:rgba(232,48,122,.7);">Mi cuenta</h4>
                <ul class="space-y-3 text-sm" style="color:rgba(255,255,255,.4);">
                    <li><a href="/register" class="hover:text-white transition-colors duration-200">Registrarme</a></li>
                    <li><a href="/login"    class="hover:text-white transition-colors duration-200">Iniciar sesión</a></li>
                    <li><a href="/perfil"   class="hover:text-white transition-colors duration-200">Mi perfil</a></li>
                    <li><a href="/cupones"  class="hover:text-white transition-colors duration-200">Mis promociones</a></li>
                    <li><a href="/compras"  class="hover:text-white transition-colors duration-200">Mis compras</a></li>
                </ul>
            </div>
        </div>

        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs" style="color:rgba(255,255,255,.2);">
                © 2026 La Zarza Contigo. Todos los derechos reservados.
            </p>
            <div class="flex gap-6">
                <a href="#" class="text-xs hover:text-white transition-colors duration-200" style="color:rgba(255,255,255,.25);">Privacidad</a>
                <a href="#" class="text-xs hover:text-white transition-colors duration-200" style="color:rgba(255,255,255,.25);">Términos</a>
            </div>
        </div>
    </div>
</footer>


<!-- ══════════════════════════════════════════════════════════
     MOBILE STICKY CTA
══════════════════════════════════════════════════════════ -->
<div class="mobile-cta-bar lg:hidden">
    <a href="/register" class="btn-neon w-full justify-center text-base">
        <i class="fa-solid fa-bolt text-yellow-300 text-sm"></i>
        Comenzar a ganar puntos
    </a>
</div>
<!-- Bottom padding so mobile content clears the sticky bar -->
<div class="lg:hidden h-20"></div>


<!-- ══════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════ -->
<script>
    // IntersectionObserver – scroll reveal
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const siblings = el.parentElement.querySelectorAll('.reveal');
                    const idx = Array.from(siblings).indexOf(el);
                    setTimeout(() => el.classList.add('visible'), idx * 110);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Trigger bar animations when in view
        const barObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.progress-bar-fill').forEach(bar => {
                        bar.style.animationPlayState = 'running';
                    });
                    barObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        document.querySelectorAll('[x-data]').forEach(el => barObserver.observe(el));
    });

    // Countdown timer (cosmetic demo)
    (function() {
        const endDate = new Date();
        endDate.setDate(endDate.getDate() + 6); // 6 days from now
        function update() {
            const now = new Date();
            const diff = endDate - now;
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            document.querySelectorAll('[data-countdown="d"]').forEach(el => el.textContent = String(d).padStart(2,'0'));
            document.querySelectorAll('[data-countdown="h"]').forEach(el => el.textContent = String(h).padStart(2,'0'));
            document.querySelectorAll('[data-countdown="m"]').forEach(el => el.textContent = String(m).padStart(2,'0'));
        }
        update(); setInterval(update, 60000);
    })();
</script>

</body>
</html>
