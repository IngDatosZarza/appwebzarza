<?php /* ================================================================
   PROPUESTA 1 – La Zarza Contigo | Homepage Premium
   Archivo de VISTA STANDALONE – no afecta el sistema real
   Acceso: /propuesta/1
   ================================================================ */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>La Zarza Contigo – Propuesta 1</title>
    <link rel="icon" type="image/png" href="/logozarza.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'display': ['"Playfair Display"', 'Georgia', 'serif'],
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        mag: {
                            50:  '#FEF0F7', 100: '#FCDAEC', 200: '#F9B5D9',
                            300: '#F585C0', 400: '#EE4EA0', 500: '#D4237A',
                            600: '#B01865', 700: '#8B1050', 800: '#6B0D3E',
                            900: '#4A0A2C', 950: '#2E0619',
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
            background-color: #FFFDF9;
            color: #1A0A12;
            overflow-x: hidden;
        }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }

        /* ─── Navbar ─────────────────────────────────────── */
        .nav-base {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            transition: all 0.4s cubic-bezier(.4,0,.2,1);
            padding: 20px 0;
        }
        .nav-scrolled {
            background: rgba(255, 253, 249, 0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 4px 40px rgba(139,16,80,.08);
            padding: 14px 0;
        }

        /* ─── Gradients ──────────────────────────────────── */
        .bg-hero {
            background:
                radial-gradient(ellipse 65% 80% at 80% 40%, rgba(249,165,200,.22) 0%, transparent 65%),
                radial-gradient(ellipse 50% 60% at 10% 90%, rgba(212,35,122,.10) 0%, transparent 55%),
                linear-gradient(160deg, #FFFDF9 0%, #FFF5FA 50%, #FFFDF9 100%);
        }
        .text-gradient {
            background: linear-gradient(135deg, #6B0D3E 0%, #D4237A 55%, #EE4EA0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-cta-gradient { background: linear-gradient(135deg, #8B1050 0%, #D4237A 100%); }
        .bg-footer { background: linear-gradient(135deg, #2E0619 0%, #4A0A2C 60%, #2E0619 100%); }

        /* ─── Buttons ────────────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #8B1050, #D4237A);
            color: white;
            padding: 15px 34px; border-radius: 50px;
            font-weight: 600; font-size: .9rem; letter-spacing: .03em;
            box-shadow: 0 8px 32px rgba(212,35,122,.35);
            transition: all .3s ease;
            position: relative; overflow: hidden;
            text-decoration: none;
            border: none; cursor: pointer;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 48px rgba(212,35,122,.50);
        }
        .btn-primary::after {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
            transition: left .45s ease;
        }
        .btn-primary:hover::after { left: 100%; }

        .btn-outline {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent;
            color: #8B1050;
            padding: 14px 34px; border-radius: 50px;
            font-weight: 600; font-size: .9rem; letter-spacing: .03em;
            border: 2px solid rgba(139,16,80,.28);
            transition: all .3s ease;
            text-decoration: none;
        }
        .btn-outline:hover {
            background: rgba(139,16,80,.05);
            border-color: #D4237A;
            transform: translateY(-3px);
        }

        /* ─── Glassmorphism ──────────────────────────────── */
        .glass {
            background: rgba(255,255,255,.60);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.80);
        }
        .glass-mag {
            background: rgba(255,240,247,.72);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(212,35,122,.18);
        }

        /* ─── Animations ─────────────────────────────────── */
        @keyframes float {
            0%,100% { transform: translateY(0) rotate(0deg); }
            40%      { transform: translateY(-14px) rotate(.5deg); }
            70%      { transform: translateY(-7px) rotate(-.3deg); }
        }
        @keyframes floatA {
            0%,100% { transform: translateY(0) translateX(0); }
            50%      { transform: translateY(-10px) translateX(4px); }
        }
        @keyframes floatB {
            0%,100% { transform: translateY(0) translateX(0); }
            50%      { transform: translateY(-7px) translateX(-5px); }
        }
        @keyframes floatC {
            0%,100% { transform: translateY(0) translateX(0); }
            50%      { transform: translateY(-12px) translateX(2px); }
        }
        @keyframes glowPulse {
            0%,100% { opacity:.35; transform:scale(1); }
            50%      { opacity:.65; transform:scale(1.06); }
        }
        @keyframes shimmerBtn {
            0%   { background-position:-200% center; }
            100% { background-position:200% center; }
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(36px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity:0; transform:scale(.92); }
            to   { opacity:1; transform:scale(1); }
        }
        @keyframes rotateRing {
            from { transform:rotate(-90deg); }
            to   { transform:rotate(270deg); }
        }
        @keyframes ringDash {
            0%   { stroke-dashoffset:283; }
            100% { stroke-dashoffset:65; }
        }

        .animate-float      { animation: float 7s ease-in-out infinite; }
        .animate-float-a    { animation: floatA 5s ease-in-out infinite; }
        .animate-float-b    { animation: floatB 6.5s ease-in-out infinite 1s; }
        .animate-float-c    { animation: floatC 5.5s ease-in-out infinite .5s; }
        .animate-glow       { animation: glowPulse 4s ease-in-out infinite; }
        .animate-fade-up    { animation: fadeUp .7s ease-out both; }
        .animate-scale-in   { animation: scaleIn .6s ease-out both; }
        .d1  { animation-delay:.1s; }
        .d2  { animation-delay:.2s; }
        .d3  { animation-delay:.35s; }
        .d4  { animation-delay:.5s; }
        .d5  { animation-delay:.65s; }
        .d6  { animation-delay:.8s; }

        /* Ring progress SVG */
        .ring-progress {
            stroke-dasharray: 283;
            stroke-dashoffset: 65;
            transform-origin: center;
            transform: rotate(-90deg);
            animation: ringDash 2s 1s ease-out forwards;
        }

        /* ─── Cards ──────────────────────────────────────── */
        .feature-card {
            background: white; border-radius: 28px;
            padding: 36px 32px;
            box-shadow: 0 4px 30px rgba(139,16,80,.07), 0 1px 0 rgba(255,255,255,.9) inset;
            transition: all .35s cubic-bezier(.4,0,.2,1);
            border: 1px solid rgba(249,165,200,.2);
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 24px 64px rgba(139,16,80,.14), 0 1px 0 rgba(255,255,255,.9) inset;
        }
        .feature-icon {
            width: 64px; height: 64px; border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 24px;
        }
        .reward-card {
            border-radius: 22px; overflow: hidden;
            transition: all .3s ease;
            cursor: pointer;
        }
        .reward-card:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 20px 50px rgba(139,16,80,.16); }
        .membership-card {
            border-radius: 28px; padding: 40px 32px;
            transition: all .35s ease;
        }
        .membership-card:hover { transform: translateY(-6px); }

        /* ─── Testimonials ───────────────────────────────── */
        .testi-card {
            background: white; border-radius: 24px; padding: 36px;
            box-shadow: 0 4px 28px rgba(139,16,80,.06);
            transition: all .3s ease;
            border: 1px solid rgba(249,165,200,.15);
        }
        .testi-card:hover { transform: translateY(-5px); box-shadow: 0 16px 50px rgba(139,16,80,.11); }

        /* ─── Section badge ──────────────────────────────── */
        .section-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(212,35,122,.07);
            color: #D4237A;
            padding: 7px 18px; border-radius: 50px;
            font-size: .78rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            border: 1px solid rgba(212,35,122,.18);
        }

        /* ─── Hero right visual ──────────────────────────── */
        .cake-glow {
            position: absolute; inset: -30px;
            background: radial-gradient(circle, rgba(212,35,122,.28) 0%, rgba(238,78,160,.12) 40%, transparent 70%);
            border-radius: 50%;
            animation: glowPulse 4s ease-in-out infinite;
        }
        .cake-plate {
            width: 260px; height: 32px;
            background: linear-gradient(180deg, #FCD2E8, #F9B5D9);
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(212,35,122,.25);
            margin: 0 auto;
        }
        .cake-tier { border-radius: 16px; margin: 0 auto; position: relative; }
        .cake-tier-1 {
            width: 220px; height: 52px;
            background: linear-gradient(180deg, #fff, #FFF0F8);
            border: 2px solid rgba(249,165,200,.5);
            margin-top: -8px;
        }
        .cake-tier-2 {
            width: 170px; height: 46px;
            background: linear-gradient(180deg, #FFF5FB, #FCDAEC);
            border: 2px solid rgba(249,165,200,.4);
            margin-top: -6px;
        }
        .cake-tier-3 {
            width: 120px; height: 40px;
            background: linear-gradient(180deg, #D4237A, #8B1050);
            border: 2px solid rgba(139,16,80,.3);
            margin-top: -5px;
            display: flex; align-items: center; justify-content: center;
        }
        .cake-topping {
            width: 28px; height: 28px;
            background: radial-gradient(circle, #FFD700, #FF8C00);
            border-radius: 50%;
            margin: -14px auto 0;
            box-shadow: 0 0 16px rgba(255,200,0,.7);
            animation: glowPulse 2s ease-in-out infinite;
        }
        .floating-card {
            position: absolute; border-radius: 18px; padding: 12px 16px;
            white-space: nowrap;
        }

        /* ─── Scroll reveal ──────────────────────────────── */
        .reveal {
            opacity: 0; transform: translateY(32px);
            transition: opacity .7s ease, transform .7s ease;
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-scale {
            opacity: 0; transform: scale(.94);
            transition: opacity .6s ease, transform .6s ease;
        }
        .reveal-scale.visible { opacity: 1; transform: scale(1); }

        /* ─── Misc ───────────────────────────────────────── */
        .divider-line {
            width: 56px; height: 3px; border-radius: 2px;
            background: linear-gradient(90deg, #D4237A, #EE4EA0);
        }
        .star-filled { color: #FFB800; }
        a { text-decoration: none; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-cols { flex-direction: column; }
            .cake-visual-wrap { margin-top: 56px; }
        }
    </style>
</head>

<body
    x-data="{
        scrolled: false,
        mobileMenu: false,
        handleScroll() { this.scrolled = window.scrollY > 60 }
    }"
    @scroll.window="handleScroll()">

<!-- ══════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════ -->
<nav class="nav-base" :class="scrolled ? 'nav-scrolled' : ''">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">

        <!-- Logo -->
        <a href="#" class="flex-shrink-0">
            <img src="/lazarzacontigoblack.png" alt="La Zarza Contigo" class="h-10 w-auto object-contain">
        </a>

        <!-- Desktop links -->
        <div class="hidden lg:flex items-center gap-8 text-sm font-medium text-mag-800">
            <a href="#como-funciona"
               class="hover:text-mag-500 transition-colors duration-200 relative group">
                ¿Cómo funciona?
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-mag-500 rounded-full transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="#recompensas"
               class="hover:text-mag-500 transition-colors duration-200 relative group">
                Recompensas
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-mag-500 rounded-full transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="#membresias"
               class="hover:text-mag-500 transition-colors duration-200 relative group">
                Membresías
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-mag-500 rounded-full transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="#testimonios"
               class="hover:text-mag-500 transition-colors duration-200 relative group">
                Testimonios
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-mag-500 rounded-full transition-all duration-300 group-hover:w-full"></span>
            </a>
        </div>

        <!-- CTA group -->
        <div class="hidden lg:flex items-center gap-4">
            <a href="/login"
               class="text-sm font-semibold text-mag-700 hover:text-mag-500 transition-colors duration-200">
                Iniciar sesión
            </a>
            <a href="/register" class="btn-primary text-sm">
                Únete gratis
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Mobile toggle -->
        <button class="lg:hidden p-2 text-mag-700" @click="mobileMenu = !mobileMenu">
            <i class="fa-solid text-xl" :class="mobileMenu ? 'fa-xmark' : 'fa-bars'"></i>
        </button>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileMenu"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-t border-mag-100 mt-3 mx-4 pb-5 pt-4">
        <div class="flex flex-col gap-4 text-sm font-medium text-mag-800 px-2">
            <a href="#como-funciona" @click="mobileMenu=false" class="hover:text-mag-500 transition-colors py-1">¿Cómo funciona?</a>
            <a href="#recompensas" @click="mobileMenu=false" class="hover:text-mag-500 transition-colors py-1">Recompensas</a>
            <a href="#membresias" @click="mobileMenu=false" class="hover:text-mag-500 transition-colors py-1">Membresías</a>
            <a href="#testimonios" @click="mobileMenu=false" class="hover:text-mag-500 transition-colors py-1">Testimonios</a>
            <hr class="border-mag-100 my-1">
            <a href="/login" class="hover:text-mag-500 transition-colors py-1">Iniciar sesión</a>
            <a href="/register" class="btn-primary text-sm w-full text-center justify-center">
                Únete gratis <i class="fa-solid fa-arrow-right text-xs ml-1"></i>
            </a>
        </div>
    </div>
</nav>


<!-- ══════════════════════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════════════════════ -->
<section class="bg-hero min-h-screen flex items-center pt-24 pb-16 relative overflow-hidden" id="inicio">

    <!-- Background decorative circles -->
    <div class="absolute top-20 right-0 w-96 h-96 rounded-full opacity-30 pointer-events-none"
         style="background: radial-gradient(circle, rgba(212,35,122,.18) 0%, transparent 70%);"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 rounded-full opacity-20 pointer-events-none"
         style="background: radial-gradient(circle, rgba(139,16,80,.2) 0%, transparent 70%);"></div>
    <div class="absolute top-1/3 left-1/4 w-4 h-4 rounded-full bg-mag-300 opacity-40 animate-float pointer-events-none"></div>
    <div class="absolute bottom-1/3 right-1/3 w-3 h-3 rounded-full bg-mag-400 opacity-30 animate-float-b pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 w-full">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16 hero-cols">

            <!-- ── LEFT COLUMN ── -->
            <div class="lg:w-1/2 flex flex-col items-start text-left">

                <!-- Premium badge -->
                <div class="animate-fade-up d1 mb-8">
                    <span class="section-badge">
                        <span class="w-2 h-2 bg-mag-500 rounded-full animate-glow"></span>
                        Programa de Lealtad Exclusivo
                    </span>
                </div>

                <!-- Headline -->
                <h1 class="font-display text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight mb-6 animate-fade-up d2">
                    Tus momentos
                    <span class="block text-gradient">favoritos ahora</span>
                    <span class="block text-mag-900">te recompensan.</span>
                </h1>

                <!-- Supporting text -->
                <p class="text-mag-800 text-lg lg:text-xl font-light leading-relaxed mb-10 max-w-lg animate-fade-up d3"
                   style="opacity:.75;">
                    Cada compra en La Zarza te llena de puntos. Canjéalos por pasteles, descuentos y
                    experiencias únicas que endulzan aún más tu vida.
                </p>

                <!-- CTA buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mb-12 animate-fade-up d4">
                    <a href="/register" class="btn-primary text-base">
                        <i class="fa-solid fa-star text-yellow-300 text-sm"></i>
                        Registrarme gratis
                    </a>
                    <a href="#como-funciona" class="btn-outline text-base">
                        Ver beneficios
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </a>
                </div>

                <!-- Stats row -->
                <div class="flex items-center gap-0 animate-fade-up d5">
                    <div class="text-center px-6">
                        <div class="font-display text-3xl font-bold text-mag-700">+12k</div>
                        <div class="text-xs font-medium text-mag-600 mt-1 opacity-70 uppercase tracking-wider">Miembros activos</div>
                    </div>
                    <div class="w-px h-10 bg-mag-200"></div>
                    <div class="text-center px-6">
                        <div class="font-display text-3xl font-bold text-mag-700">8</div>
                        <div class="text-xs font-medium text-mag-600 mt-1 opacity-70 uppercase tracking-wider">Sucursales</div>
                    </div>
                    <div class="w-px h-10 bg-mag-200"></div>
                    <div class="text-center px-6">
                        <div class="font-display text-3xl font-bold text-mag-700">+50</div>
                        <div class="text-xs font-medium text-mag-600 mt-1 opacity-70 uppercase tracking-wider">Recompensas</div>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT COLUMN: Cake Visual ── -->
            <div class="lg:w-1/2 flex justify-center items-center relative cake-visual-wrap animate-scale-in d3">

                <!-- Outer glow ring -->
                <div class="absolute w-[480px] h-[480px] rounded-full pointer-events-none"
                     style="background: radial-gradient(circle, rgba(212,35,122,.20) 0%, rgba(238,78,160,.08) 50%, transparent 70%); animation: glowPulse 5s ease-in-out infinite;">
                </div>

                <!-- Main visual container -->
                <div class="relative w-[400px] h-[480px] flex flex-col items-center justify-center animate-float">

                    <!-- Progress ring (top-right) -->
                    <div class="absolute -top-4 -right-8 z-20 animate-float-c">
                        <div class="glass rounded-3xl p-4 shadow-lg">
                            <div class="relative w-20 h-20">
                                <svg width="80" height="80" viewBox="0 0 96 96" class="w-full h-full">
                                    <circle cx="48" cy="48" r="40" fill="none"
                                            stroke="rgba(212,35,122,.12)" stroke-width="8"/>
                                    <circle cx="48" cy="48" r="40" fill="none"
                                            stroke="url(#ringGrad)" stroke-width="8"
                                            stroke-linecap="round"
                                            class="ring-progress"/>
                                    <defs>
                                        <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#8B1050"/>
                                            <stop offset="100%" stop-color="#EE4EA0"/>
                                        </linearGradient>
                                    </defs>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="font-display text-sm font-bold text-mag-700">75%</span>
                                    <span class="text-[9px] text-mag-500 font-medium">meta</span>
                                </div>
                            </div>
                            <p class="text-center text-[10px] font-semibold text-mag-600 mt-1 opacity-80">Próx. nivel</p>
                        </div>
                    </div>

                    <!-- Floating card: Points -->
                    <div class="glass-mag floating-card shadow-xl z-20 animate-float-a"
                         style="top: 8%; left: -28%;">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-cta-gradient flex items-center justify-center text-white">
                                <i class="fa-solid fa-coins text-sm"></i>
                            </div>
                            <div>
                                <div class="font-display text-xl font-bold text-mag-700">342</div>
                                <div class="text-[10px] font-semibold text-mag-500 uppercase tracking-wide">Puntos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating card: Reward unlocked -->
                    <div class="glass floating-card shadow-xl z-20 animate-float-b"
                         style="bottom: 18%; left: -30%;">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-mag-100 flex items-center justify-center text-xl">
                                🎁
                            </div>
                            <div>
                                <div class="text-xs font-bold text-mag-800">¡Recompensa desbloqueada!</div>
                                <div class="text-[10px] text-mag-500 font-medium mt-0.5">Postre gratis en tu próxima visita</div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating card: Level badge -->
                    <div class="floating-card shadow-xl z-20 animate-float-c"
                         style="bottom: 10%; right: -18%; background: linear-gradient(135deg,#8B1050,#D4237A); color:white;">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-crown text-yellow-300 text-base"></i>
                            <div>
                                <div class="text-[11px] font-bold">Nivel Oro</div>
                                <div class="text-[9px] opacity-80">Zarza Premium</div>
                            </div>
                        </div>
                    </div>

                    <!-- Cake illustration -->
                    <!-- ══════════════════════════════════════════════════════════
                         IMAGEN HERO – subir: public/proposals/p1-pastel-hero.jpg
                         Tamaño ideal : 480 × 560 px  |  jpg · webp · png
                         Cuando el archivo existe sustituye la ilustración CSS.
                         ══════════════════════════════════════════════════════════ -->
                    <img src="/proposals/p1-pastel-hero.jpg"
                         alt="Pastel premium La Zarza Contigo"
                         id="p1HeroImg"
                         class="w-64 h-80 object-cover rounded-3xl"
                         style="display:none; box-shadow:0 24px 60px rgba(212,35,122,.45), 0 0 0 6px rgba(255,255,255,.65);"
                         onload="this.style.display='block'; document.getElementById('p1CakeCSS').style.display='none';">
                    <div class="flex flex-col items-center select-none" id="p1CakeCSS">
                        <!-- Topping glow -->
                        <div class="cake-topping mb-1"></div>
                        <!-- Tier 3 (top, dark magenta) -->
                        <div class="cake-tier cake-tier-3">
                            <span class="text-white text-[10px] font-bold tracking-widest uppercase opacity-80">Zarza</span>
                        </div>
                        <!-- Tier 2 -->
                        <div class="cake-tier cake-tier-2">
                            <div class="flex justify-center gap-2 mt-2">
                                <span style="font-size:1.1rem;">🍓</span>
                                <span style="font-size:1.1rem;">🍓</span>
                            </div>
                        </div>
                        <!-- Tier 1 (base) -->
                        <div class="cake-tier cake-tier-1">
                            <div class="flex justify-center gap-1 mt-3">
                                <span style="font-size:.9rem;">🌸</span>
                                <span style="font-size:.9rem;">🌸</span>
                                <span style="font-size:.9rem;">🌸</span>
                            </div>
                        </div>
                        <!-- Plate -->
                        <div class="cake-plate mt-1"></div>
                    </div>

                    <!-- Inner soft glow behind cake -->
                    <div class="absolute top-1/2 left-1/2 w-64 h-64 rounded-full pointer-events-none"
                         style="transform:translate(-50%,-50%); background:radial-gradient(circle,rgba(249,165,200,.35) 0%,transparent 70%); z-index:-1;"></div>
                </div>
            </div>

        </div><!-- /hero cols -->
    </div><!-- /container -->

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-50">
        <span class="text-xs font-medium text-mag-600 uppercase tracking-widest">Explorar</span>
        <div class="w-px h-10 bg-gradient-to-b from-mag-400 to-transparent"></div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════════════════════ -->
<section id="como-funciona" class="py-24 lg:py-32" style="background:#FFFDF9;">
    <div class="max-w-7xl mx-auto px-6">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-badge mb-5 inline-flex">
                <i class="fa-solid fa-circle-info text-xs"></i>
                Así de fácil
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-mag-900 mt-4 mb-5">
                ¿Cómo funciona<br>
                <span class="text-gradient">La Zarza Contigo?</span>
            </h2>
            <div class="divider-line mx-auto mb-5"></div>
            <p class="text-mag-700 text-lg max-w-xl mx-auto leading-relaxed" style="opacity:.72;">
                En tres sencillos pasos empieza a convertir cada visita en una experiencia que vale doble.
            </p>
        </div>

        <!-- 3 cards -->
        <div class="grid md:grid-cols-3 gap-8">

            <!-- Card 1 -->
            <div class="feature-card reveal d1 relative">
                <div class="feature-icon" style="background: linear-gradient(135deg, rgba(212,35,122,.1), rgba(238,78,160,.1));">
                    <span style="font-size:1.7rem;">🛍️</span>
                </div>
                <div class="absolute top-8 right-8 font-display text-7xl font-bold text-mag-100 select-none leading-none">01</div>
                <h3 class="font-display text-2xl font-bold text-mag-900 mb-3 relative">Realiza tus compras</h3>
                <p class="text-mag-700 leading-relaxed relative" style="opacity:.72;">
                    Compra en cualquiera de nuestras sucursales o en línea. Cada peso gastado acumula puntos en tu cuenta automáticamente.
                </p>
                <div class="mt-6 pt-6 border-t border-mag-50 flex items-center gap-2 text-sm font-semibold text-mag-600">
                    <i class="fa-solid fa-circle-check text-mag-400 text-base"></i>
                    Sin tarjetas ni trámites
                </div>
            </div>

            <!-- Card 2 -->
            <div class="feature-card reveal d2 relative" style="margin-top: 0; border: 2px solid rgba(212,35,122,.15);">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-cta-gradient text-white text-xs font-bold px-4 py-1.5 rounded-full whitespace-nowrap shadow-lg">
                    Más popular
                </div>
                <div class="feature-icon" style="background: linear-gradient(135deg, rgba(139,16,80,.12), rgba(212,35,122,.12));">
                    <span style="font-size:1.7rem;">⭐</span>
                </div>
                <div class="absolute top-8 right-8 font-display text-7xl font-bold text-mag-100 select-none leading-none">02</div>
                <h3 class="font-display text-2xl font-bold text-mag-900 mb-3 relative">Acumula puntos</h3>
                <p class="text-mag-700 leading-relaxed relative" style="opacity:.72;">
                    Tu saldo crece con cada visita. Sube de nivel —Bronze, Plata u Oro— y desbloquea beneficios exclusivos según tu lealtad.
                </p>
                <div class="mt-6 pt-6 border-t border-mag-50 flex items-center gap-2 text-sm font-semibold text-mag-600">
                    <i class="fa-solid fa-circle-check text-mag-400 text-base"></i>
                    Puntos que nunca vencen
                </div>
            </div>

            <!-- Card 3 -->
            <div class="feature-card reveal d3 relative">
                <div class="feature-icon" style="background: linear-gradient(135deg, rgba(255,184,0,.15), rgba(255,140,0,.1));">
                    <span style="font-size:1.7rem;">🎁</span>
                </div>
                <div class="absolute top-8 right-8 font-display text-7xl font-bold text-mag-100 select-none leading-none">03</div>
                <h3 class="font-display text-2xl font-bold text-mag-900 mb-3 relative">Canjea recompensas</h3>
                <p class="text-mag-700 leading-relaxed relative" style="opacity:.72;">
                    Elige entre pasteles gratis, descuentos exclusivos, envíos a domicilio o experiencias VIP. Tú decides cuándo disfrutarlos.
                </p>
                <div class="mt-6 pt-6 border-t border-mag-50 flex items-center gap-2 text-sm font-semibold text-mag-600">
                    <i class="fa-solid fa-circle-check text-mag-400 text-base"></i>
                    Más de 50 premios disponibles
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     REWARDS PREVIEW
══════════════════════════════════════════════════════════ -->
<section id="recompensas" class="py-24 lg:py-32 relative overflow-hidden"
         style="background: linear-gradient(160deg, #FFF0F8 0%, #FFF8F0 50%, #FFF0F8 100%);">

    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 60% 50% at 50% 0%, rgba(212,35,122,.06) 0%, transparent 70%);"></div>

    <div class="max-w-7xl mx-auto px-6 relative">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-badge mb-5 inline-flex">
                <i class="fa-solid fa-gift text-xs"></i>
                Lo que puedes ganar
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-mag-900 mt-4 mb-5">
                Recompensas que
                <span class="text-gradient"> enamoran</span>
            </h2>
            <div class="divider-line mx-auto"></div>
        </div>

        <!-- Rewards grid -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Reward 1 -->
            <div class="reward-card reveal d1" style="background: linear-gradient(135deg, #8B1050, #D4237A);">
                <!-- ══ IMAGEN: public/proposals/p1-reward-1.jpg  (400×200px) ══ -->
                <div class="h-36 overflow-hidden rounded-t-[22px]" id="p1R1Wrap">
                    <img src="/proposals/p1-reward-1.jpg" alt="Pastel de cumpleaños La Zarza"
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="p-7">
                    <div class="text-4xl mb-4">🎂</div>
                    <div class="text-xs font-bold text-mag-200 uppercase tracking-widest mb-2">500 puntos</div>
                    <h4 class="font-display text-xl font-bold text-white mb-2">Pastel de cumpleaños gratis</h4>
                    <p class="text-mag-200 text-sm leading-relaxed" style="opacity:.85;">
                        Un pastel completo para celebrar tu día especial como mereces.
                    </p>
                    <div class="mt-6 pt-4 border-t border-mag-800 flex items-center justify-between">
                        <span class="text-white text-xs font-semibold opacity-80">Canjear ahora</span>
                        <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                            <i class="fa-solid fa-arrow-right text-white text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reward 2 -->
            <div class="reward-card reveal d2" style="background: white; border: 1px solid rgba(249,165,200,.3); box-shadow: 0 4px 24px rgba(139,16,80,.06);">
                <!-- ══ IMAGEN: public/proposals/p1-reward-2.jpg  (400×200px) ══ -->
                <div class="h-36 overflow-hidden rounded-t-[22px]" id="p1R2Wrap">
                    <img src="/proposals/p1-reward-2.jpg" alt="Descuento La Zarza"
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="p-7">
                    <div class="text-4xl mb-4">💸</div>
                    <div class="text-xs font-bold text-mag-400 uppercase tracking-widest mb-2">200 puntos</div>
                    <h4 class="font-display text-xl font-bold text-mag-900 mb-2">5% en tu siguiente compra</h4>
                    <p class="text-mag-600 text-sm leading-relaxed" style="opacity:.8;">
                        Descuento aplicado automáticamente en caja con solo mostrar tu código.
                    </p>
                    <div class="mt-6 pt-4 border-t border-mag-50 flex items-center justify-between">
                        <span class="text-mag-500 text-xs font-semibold">Canjear ahora</span>
                        <div class="w-8 h-8 rounded-full bg-mag-50 flex items-center justify-center">
                            <i class="fa-solid fa-arrow-right text-mag-500 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reward 3 -->
            <div class="reward-card reveal d3" style="background: white; border: 1px solid rgba(249,165,200,.3); box-shadow: 0 4px 24px rgba(139,16,80,.06);">
                <!-- ══ IMAGEN: public/proposals/p1-reward-3.jpg  (400×200px) ══ -->
                <div class="h-36 overflow-hidden rounded-t-[22px]" id="p1R3Wrap">
                    <img src="/proposals/p1-reward-3.jpg" alt="Envío gratis La Zarza"
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="p-7">
                    <div class="text-4xl mb-4">🚚</div>
                    <div class="text-xs font-bold text-mag-400 uppercase tracking-widest mb-2">150 puntos</div>
                    <h4 class="font-display text-xl font-bold text-mag-900 mb-2">Envío gratis a domicilio</h4>
                    <p class="text-mag-600 text-sm leading-relaxed" style="opacity:.8;">
                        Tu pedido llega hasta tu puerta sin costo de envío. Válido en toda el área metropolitana.
                    </p>
                    <div class="mt-6 pt-4 border-t border-mag-50 flex items-center justify-between">
                        <span class="text-mag-500 text-xs font-semibold">Canjear ahora</span>
                        <div class="w-8 h-8 rounded-full bg-mag-50 flex items-center justify-center">
                            <i class="fa-solid fa-arrow-right text-mag-500 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reward 4 -->
            <div class="reward-card reveal d4"
                 style="background: linear-gradient(135deg, #FFF8F0, #FCDAEC); border: 1px solid rgba(212,35,122,.15); box-shadow: 0 4px 24px rgba(139,16,80,.06);">
                <!-- ══ IMAGEN: public/proposals/p1-reward-4.jpg  (400×200px) ══ -->
                <div class="h-36 overflow-hidden rounded-t-[22px]" id="p1R4Wrap">
                    <img src="/proposals/p1-reward-4.jpg" alt="Degustación VIP La Zarza"
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div class="p-7">
                    <div class="text-4xl mb-4">✨</div>
                    <div class="text-xs font-bold text-mag-500 uppercase tracking-widest mb-2">1000 puntos</div>
                    <h4 class="font-display text-xl font-bold text-mag-900 mb-2">Degustación VIP exclusiva</h4>
                    <p class="text-mag-700 text-sm leading-relaxed" style="opacity:.8;">
                        Una tarde privada con nuestros maestros pasteleros. Vive la Zarza por dentro.
                    </p>
                    <div class="mt-6 pt-4 border-t border-mag-200 flex items-center justify-between">
                        <span class="text-mag-600 text-xs font-semibold">Canjear ahora</span>
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                            <i class="fa-solid fa-arrow-right text-mag-500 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA center -->
        <div class="text-center mt-14 reveal d5">
            <a href="/register" class="btn-primary text-base">
                Ver todas las recompensas
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     MEMBERSHIP LEVELS
══════════════════════════════════════════════════════════ -->
<section id="membresias" class="py-24 lg:py-32" style="background:#FFFDF9;">
    <div class="max-w-7xl mx-auto px-6">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-badge mb-5 inline-flex">
                <i class="fa-solid fa-crown text-xs"></i>
                Niveles de membresía
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-mag-900 mt-4 mb-5">
                Sube de nivel,
                <span class="text-gradient"> sube los beneficios</span>
            </h2>
            <div class="divider-line mx-auto"></div>
        </div>

        <!-- 3 membership cards -->
        <div class="grid md:grid-cols-3 gap-8 items-start">

            <!-- Bronze -->
            <div class="membership-card reveal d1"
                 style="background: linear-gradient(160deg, #FFF8F0, #FFE8CC); border: 1px solid rgba(205,127,50,.2); box-shadow: 0 4px 28px rgba(205,127,50,.08);">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
                         style="background: linear-gradient(135deg, #CD7F32, #E8A050);">🥉</div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color:#7B4A1A;">Bronze</h3>
                        <p class="text-xs font-semibold uppercase tracking-widest mt-0.5" style="color:#B87333; opacity:.7;">0 – 499 puntos</p>
                    </div>
                </div>
                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#5A3410;">
                        <i class="fa-solid fa-check w-4 text-center" style="color:#CD7F32;"></i> Acumulación de puntos
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#5A3410;">
                        <i class="fa-solid fa-check w-4 text-center" style="color:#CD7F32;"></i> Acceso al catálogo de premios
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#5A3410;">
                        <i class="fa-solid fa-check w-4 text-center" style="color:#CD7F32;"></i> Notificaciones de promociones
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium opacity-40" style="color:#5A3410;">
                        <i class="fa-solid fa-xmark w-4 text-center"></i> Premios exclusivos
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium opacity-40" style="color:#5A3410;">
                        <i class="fa-solid fa-xmark w-4 text-center"></i> Acceso VIP
                    </div>
                </div>
                <a href="/register"
                   class="block text-center py-3 px-6 rounded-2xl text-sm font-semibold transition-all duration-200"
                   style="background: rgba(205,127,50,.1); color:#7B4A1A; border:1.5px solid rgba(205,127,50,.3);">
                   Comenzar gratis
                </a>
            </div>

            <!-- Plata (featured) -->
            <div class="membership-card reveal d2 relative"
                 style="background: linear-gradient(160deg, #F4F4FC, #E2E2F2); border: 2px solid rgba(130,130,190,.25); box-shadow: 0 12px 50px rgba(130,130,190,.15); margin-top:-12px;">
                <div class="absolute -top-5 left-1/2 -translate-x-1/2 bg-cta-gradient text-white text-xs font-bold px-5 py-2 rounded-full whitespace-nowrap shadow-lg">
                    ⭐ Más popular
                </div>
                <div class="flex items-center gap-3 mb-6 mt-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
                         style="background: linear-gradient(135deg, #9090B0, #B0B0D0);">🥈</div>
                    <div>
                        <h3 class="font-display text-2xl font-bold" style="color:#3A3A5C;">Plata</h3>
                        <p class="text-xs font-semibold uppercase tracking-widest mt-0.5" style="color:#7070A0; opacity:.8;">500 – 1,499 puntos</p>
                    </div>
                </div>
                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#2A2A4A;">
                        <i class="fa-solid fa-check w-4 text-center text-mag-400"></i> Todo lo de Bronze
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#2A2A4A;">
                        <i class="fa-solid fa-check w-4 text-center text-mag-400"></i> Puntos x1.5 en cada compra
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#2A2A4A;">
                        <i class="fa-solid fa-check w-4 text-center text-mag-400"></i> Premios exclusivos de nivel
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium" style="color:#2A2A4A;">
                        <i class="fa-solid fa-check w-4 text-center text-mag-400"></i> Descuentos especiales de temporada
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium opacity-40" style="color:#2A2A4A;">
                        <i class="fa-solid fa-xmark w-4 text-center"></i> Acceso VIP
                    </div>
                </div>
                <a href="/register" class="btn-primary text-sm w-full justify-center">
                    Unirme ahora
                </a>
            </div>

            <!-- Oro -->
            <div class="membership-card reveal d3"
                 style="background: linear-gradient(135deg, #2E0619, #6B0D3E, #8B1050); border: 1px solid rgba(212,35,122,.3); box-shadow: 0 8px 40px rgba(139,16,80,.22);">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
                         style="background: linear-gradient(135deg, #FFD700, #FFA500);">👑</div>
                    <div>
                        <h3 class="font-display text-2xl font-bold text-white">Oro</h3>
                        <p class="text-xs font-semibold uppercase tracking-widest mt-0.5 text-mag-200" style="opacity:.75;">1,500+ puntos</p>
                    </div>
                </div>
                <div class="space-y-3 mb-8">
                    <div class="flex items-center gap-3 text-sm font-medium text-mag-100">
                        <i class="fa-solid fa-check w-4 text-center text-yellow-400"></i> Todo lo de Plata
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium text-mag-100">
                        <i class="fa-solid fa-check w-4 text-center text-yellow-400"></i> Puntos x2 en cada compra
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium text-mag-100">
                        <i class="fa-solid fa-check w-4 text-center text-yellow-400"></i> Pastel de cumpleaños anual gratis
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium text-mag-100">
                        <i class="fa-solid fa-check w-4 text-center text-yellow-400"></i> Acceso VIP a degustaciones
                    </div>
                    <div class="flex items-center gap-3 text-sm font-medium text-mag-100">
                        <i class="fa-solid fa-check w-4 text-center text-yellow-400"></i> Atención personalizada
                    </div>
                </div>
                <a href="/register"
                   class="block text-center py-3 px-6 rounded-2xl text-sm font-semibold transition-all duration-200"
                   style="background: rgba(255,255,255,.12); color:white; border:1.5px solid rgba(255,255,255,.2);"
                   onmouseover="this.style.background='rgba(255,255,255,.2)'"
                   onmouseout="this.style.background='rgba(255,255,255,.12)'">
                   Aspirar al Oro
                </a>
            </div>

        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     TESTIMONIALS
══════════════════════════════════════════════════════════ -->
<section id="testimonios" class="py-24 lg:py-32 relative overflow-hidden"
         style="background: linear-gradient(160deg, #FFF0F8 0%, #FFF8F0 50%, #FFF0F8 100%);">

    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 70% 60% at 50% 100%, rgba(212,35,122,.05) 0%, transparent 70%);"></div>

    <div class="max-w-7xl mx-auto px-6 relative">

        <!-- Heading -->
        <div class="text-center mb-16 reveal">
            <span class="section-badge mb-5 inline-flex">
                <i class="fa-solid fa-heart text-xs"></i>
                Lo que dicen nuestros miembros
            </span>
            <h2 class="font-display text-4xl lg:text-5xl font-bold text-mag-900 mt-4 mb-5">
                Experiencias que
                <span class="text-gradient"> nos llenan el corazón</span>
            </h2>
            <div class="divider-line mx-auto"></div>
        </div>

        <!-- Testimonials grid -->
        <div class="grid md:grid-cols-3 gap-8">

            <!-- T1 -->
            <div class="testi-card reveal d1">
                <div class="flex gap-1 mb-5">
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                </div>
                <p class="text-mag-700 leading-relaxed mb-6 italic font-light text-base" style="opacity:.85;">
                    "Desde que tengo la membresía cada visita a La Zarza es aún más especial.
                    ¡Mi pastel de cumpleaños fue completamente gratis y estaba delicioso!"
                </p>
                <div class="flex items-center gap-4 pt-5 border-t border-mag-50">
                    <div class="w-11 h-11 rounded-full bg-cta-gradient flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        MG
                    </div>
                    <div>
                        <div class="font-semibold text-mag-900 text-sm">María González</div>
                        <div class="text-xs text-mag-500 mt-0.5">Miembro Oro · Guadalajara</div>
                    </div>
                    <div class="ml-auto">
                        <span class="text-xs font-bold text-yellow-500 bg-yellow-50 px-2.5 py-1 rounded-full border border-yellow-200">
                            👑 Oro
                        </span>
                    </div>
                </div>
            </div>

            <!-- T2 -->
            <div class="testi-card reveal d2">
                <div class="flex gap-1 mb-5">
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                </div>
                <p class="text-mag-700 leading-relaxed mb-6 italic font-light text-base" style="opacity:.85;">
                    "La app es súper fácil de usar. Acumulé puntos sin darme cuenta y ya canjeé
                    envío gratis tres veces este mes. ¡Así sí conviene!"
                </p>
                <div class="flex items-center gap-4 pt-5 border-t border-mag-50">
                    <div class="w-11 h-11 rounded-full bg-mag-700 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        CR
                    </div>
                    <div>
                        <div class="font-semibold text-mag-900 text-sm">Carlos Ramírez</div>
                        <div class="text-xs text-mag-500 mt-0.5">Miembro Plata · CDMX</div>
                    </div>
                    <div class="ml-auto">
                        <span class="text-xs font-bold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-full border border-gray-200">
                            🥈 Plata
                        </span>
                    </div>
                </div>
            </div>

            <!-- T3 -->
            <div class="testi-card reveal d3">
                <div class="flex gap-1 mb-5">
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                    <i class="fa-solid fa-star star-filled text-sm"></i>
                </div>
                <p class="text-mag-700 leading-relaxed mb-6 italic font-light text-base" style="opacity:.85;">
                    "Fui a la degustación VIP y fue una experiencia increíble. Ver cómo hacen
                    los pasteles y probarlos directo del horno… ¡La Zarza es otro nivel!"
                </p>
                <div class="flex items-center gap-4 pt-5 border-t border-mag-50">
                    <div class="w-11 h-11 rounded-full bg-mag-200 flex items-center justify-center text-mag-800 font-bold text-sm flex-shrink-0">
                        AL
                    </div>
                    <div>
                        <div class="font-semibold text-mag-900 text-sm">Ana Luisa Torres</div>
                        <div class="text-xs text-mag-500 mt-0.5">Miembro Oro · Monterrey</div>
                    </div>
                    <div class="ml-auto">
                        <span class="text-xs font-bold text-yellow-500 bg-yellow-50 px-2.5 py-1 rounded-full border border-yellow-200">
                            👑 Oro
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Trust indicators -->
        <div class="flex flex-wrap justify-center gap-10 mt-20 reveal d4">
            <div class="flex items-center gap-3 text-mag-700">
                <div class="w-10 h-10 rounded-full bg-mag-50 flex items-center justify-center">
                    <i class="fa-solid fa-shield-halved text-mag-500 text-base"></i>
                </div>
                <span class="text-sm font-medium">Datos 100% seguros</span>
            </div>
            <div class="flex items-center gap-3 text-mag-700">
                <div class="w-10 h-10 rounded-full bg-mag-50 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-mag-500 text-base"></i>
                </div>
                <span class="text-sm font-medium">Registro en menos de 1 minuto</span>
            </div>
            <div class="flex items-center gap-3 text-mag-700">
                <div class="w-10 h-10 rounded-full bg-mag-50 flex items-center justify-center">
                    <i class="fa-solid fa-ban text-mag-500 text-base"></i>
                </div>
                <span class="text-sm font-medium">Sin cuotas ni mensualidades</span>
            </div>
            <div class="flex items-center gap-3 text-mag-700">
                <div class="w-10 h-10 rounded-full bg-mag-50 flex items-center justify-center">
                    <i class="fa-solid fa-store text-mag-500 text-base"></i>
                </div>
                <span class="text-sm font-medium">Válido en todas las sucursales</span>
            </div>
        </div>

    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════════════════════ -->
<section class="py-24 relative overflow-hidden"
         style="background: linear-gradient(135deg, #2E0619 0%, #6B0D3E 40%, #8B1050 70%, #4A0A2C 100%);">

    <!-- Decorative circles -->
    <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full opacity-10 pointer-events-none"
         style="background: radial-gradient(circle, #EE4EA0, transparent);"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 rounded-full opacity-10 pointer-events-none"
         style="background: radial-gradient(circle, #D4237A, transparent);"></div>

    <div class="max-w-4xl mx-auto px-6 text-center relative">
        <div class="text-5xl mb-6 reveal">🎂</div>
        <h2 class="font-display text-4xl lg:text-5xl font-bold text-white mb-6 leading-tight reveal d1">
            Cada visita merece una recompensa.<br>
            <span style="color:#F585C0;">¿Ya eres miembro?</span>
        </h2>
        <p class="text-mag-200 text-lg mb-10 max-w-xl mx-auto leading-relaxed reveal d2" style="opacity:.8;">
            Únete hoy, acumula tus primeros puntos en tu próxima compra y empieza a vivir La Zarza de una forma completamente nueva.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center reveal d3">
            <a href="/register"
               class="btn-primary text-base"
               style="background: white; color: #8B1050; box-shadow: 0 8px 32px rgba(255,255,255,.2);"
               onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 16px 48px rgba(255,255,255,.35)'"
               onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 32px rgba(255,255,255,.2)'">
                <i class="fa-solid fa-star text-mag-500 text-sm"></i>
                Registrarme gratis
            </a>
            <a href="/login"
               class="btn-outline text-base"
               style="border-color: rgba(255,255,255,.3); color: white;"
               onmouseover="this.style.background='rgba(255,255,255,.08)'; this.style.borderColor='rgba(255,255,255,.6)'"
               onmouseout="this.style.background=''; this.style.borderColor='rgba(255,255,255,.3)'">
                Ya tengo cuenta
            </a>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ -->
<footer class="bg-footer text-white pt-20 pb-10">
    <div class="max-w-7xl mx-auto px-6">

        <div class="grid md:grid-cols-4 gap-12 pb-16 border-b border-white border-opacity-10">

            <!-- Brand -->
            <div class="md:col-span-2">
                <img src="/lazarzacontigowhite.png" alt="La Zarza Contigo" class="h-12 w-auto object-contain mb-6">
                <p class="text-mag-200 text-sm leading-relaxed max-w-xs" style="opacity:.7;">
                    Tu programa de lealtad favorito. Cada momento en La Zarza cuenta, cada punto te acerca a algo especial.
                </p>
                <div class="flex gap-4 mt-8">
                    <a href="#"
                       class="w-10 h-10 rounded-full bg-white bg-opacity-10 flex items-center justify-center transition-all duration-200 hover:bg-opacity-20 hover:-translate-y-1">
                        <i class="fa-brands fa-instagram text-sm"></i>
                    </a>
                    <a href="#"
                       class="w-10 h-10 rounded-full bg-white bg-opacity-10 flex items-center justify-center transition-all duration-200 hover:bg-opacity-20 hover:-translate-y-1">
                        <i class="fa-brands fa-facebook text-sm"></i>
                    </a>
                    <a href="#"
                       class="w-10 h-10 rounded-full bg-white bg-opacity-10 flex items-center justify-center transition-all duration-200 hover:bg-opacity-20 hover:-translate-y-1">
                        <i class="fa-brands fa-tiktok text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- Links -->
            <div>
                <h4 class="text-sm font-bold uppercase tracking-widest text-mag-300 mb-6">Plataforma</h4>
                <ul class="space-y-4">
                    <li><a href="#como-funciona" class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">¿Cómo funciona?</a></li>
                    <li><a href="#recompensas"  class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Recompensas</a></li>
                    <li><a href="#membresias"   class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Membresías</a></li>
                    <li><a href="/sucursales"   class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Sucursales</a></li>
                </ul>
            </div>

            <!-- Account -->
            <div>
                <h4 class="text-sm font-bold uppercase tracking-widest text-mag-300 mb-6">Mi cuenta</h4>
                <ul class="space-y-4">
                    <li><a href="/register" class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Registrarme</a></li>
                    <li><a href="/login"    class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Iniciar sesión</a></li>
                    <li><a href="/perfil"   class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Mi perfil</a></li>
                    <li><a href="/cupones"  class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Mis promociones</a></li>
                    <li><a href="/compras"  class="text-mag-200 text-sm hover:text-white transition-colors duration-200" style="opacity:.7;">Mis compras</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-mag-300" style="opacity:.5;">
                © 2026 La Zarza Contigo. Todos los derechos reservados.
            </p>
            <div class="flex items-center gap-6">
                <a href="#" class="text-xs text-mag-300 hover:text-white transition-colors duration-200" style="opacity:.5;">Aviso de privacidad</a>
                <a href="#" class="text-xs text-mag-300 hover:text-white transition-colors duration-200" style="opacity:.5;">Términos de uso</a>
            </div>
        </div>
    </div>
</footer>


<!-- ══════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════ -->
<script>
    // IntersectionObserver for scroll reveal
    document.addEventListener('DOMContentLoaded', function () {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const delay = parseFloat(el.dataset.delay || 0);
                        setTimeout(() => {
                            el.classList.add('visible');
                        }, delay * 1000);
                        observer.unobserve(el);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );

        document.querySelectorAll('.reveal, .reveal-scale').forEach((el, i) => {
            // Stagger siblings in same parent
            const siblings = el.parentElement.querySelectorAll('.reveal, .reveal-scale');
            const idx = Array.from(siblings).indexOf(el);
            el.dataset.delay = (idx * 0.12).toString();
            observer.observe(el);
        });
    });
</script>

</body>
</html>
