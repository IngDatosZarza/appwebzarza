@extends('layouts.app')

@section('title', 'Inicio - La Zarza Contigo')

@push('styles')
<style>
    body {
        background: transparent !important;
    }
    main {
        padding: 0 !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    /* Hero gradient overlay */
    .hero-overlay {
        background: linear-gradient(135deg,
            rgba(113, 57, 141, 0.75) 0%,
            rgba(181, 26, 138, 0.65) 50%,
            rgba(0, 0, 0, 0.55) 100%);
    }

    /* Glassy card */
    .glass-card {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(50px);
        -webkit-backdrop-filter: blur(50px);
        border: 1px solid rgba(255, 255, 255, 0.25);
    }

    /* Primary CTA button */
    .btn-cta-primary {
        background: linear-gradient(135deg, #b51a8a 0%, #d63a9e 100%);
        transition: all 0.3s ease;
        box-shadow: 0 4px 20px rgba(181, 26, 138, 0.45);
    }
    .btn-cta-primary:hover {
        background: linear-gradient(135deg, #9e1577 0%, #c0348b 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(181, 26, 138, 0.6);
    }

    /* Secondary CTA button */
    .btn-cta-secondary {
        background: rgba(255, 255, 255, 0.15);
        border: 2px solid rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
    }
    .btn-cta-secondary:hover {
        background: rgba(255, 255, 255, 0.28);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 255, 255, 0.2);
    }

    /* Benefit cards */
    .benefit-card {
        background: rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.20);
        transition: all 0.3s ease;
    }
    .benefit-card:hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    /* Icon circle */
    .icon-circle {
        background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%);
        box-shadow: 0 4px 15px rgba(181, 26, 138, 0.5);
    }

    /* Badge floating */
    .badge-promo {
        background: linear-gradient(135deg, #d63a9e 0%, #b51a8a 100%);
        animation: float 3s ease-in-out infinite;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-6px); }
    }

    /* Subtle pulse on star */
    .star-pulse {
        animation: starPulse 2s ease-in-out infinite;
    }
    @keyframes starPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.75; transform: scale(1.15); }
    }

    /* Dashboard cards */
    .dash-card {
        transition: all 0.3s ease;
    }
    .dash-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    /* Hover lift para stat cards */
    .hover-lift {
        transition: all 0.25s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 45px rgba(0,0,0,0.35);
        background: rgba(255,255,255,0.18) !important;
    }

    /* Tiles de acción rápida */
    .action-tile {
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.15);
        transition: all 0.2s ease;
    }
    .action-tile:hover {
        background: rgba(255,255,255,0.22);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        border-color: rgba(255,255,255,0.35);
    }

    /* Icono dentro del tile */
    .icon-tile {
        background: linear-gradient(135deg, rgba(181,26,138,0.85) 0%, rgba(113,57,141,0.85) 100%);
        box-shadow: 0 4px 12px rgba(181,26,138,0.4);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .action-tile:hover .icon-tile {
        box-shadow: 0 8px 20px rgba(181,26,138,0.6);
    }

    /* Wave emoji */
    .wave {
        display: inline-block;
        animation: waveHand 2.5s ease-in-out infinite;
        transform-origin: 70% 70%;
    }
    @keyframes waveHand {
        0%,60%,100% { transform: rotate(0deg); }
        10%          { transform: rotate(14deg); }
        20%          { transform: rotate(-8deg); }
        30%          { transform: rotate(14deg); }
        40%          { transform: rotate(-4deg); }
        50%          { transform: rotate(10deg); }
    }

    /* Barra de progreso animada */
    .progress-bar-fill {
        animation: growBar 1.2s ease-out forwards;
        width: 0%;
    }
    @keyframes growBar {
        to { width: var(--target-width); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('/PROPORCIONAL FHD.jpg');">
    <div class="min-h-screen hero-overlay flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

        @if(isset($isAuthenticated) && $isAuthenticated)
        {{-- ── DASHBOARD AUTENTICADO ── --}}
        <div class="w-full max-w-5xl space-y-6">

            {{-- Saludo personalizado --}}
            <div class="text-center">
                <p class="text-pink-200 text-xs font-semibold uppercase tracking-widest mb-2" id="greeting-time">¡Bienvenido de vuelta!</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight tracking-tight">
                    Hola, <span class="text-pink-300">{{ $userData['nombre'] ?? 'Usuario' }}</span>
                    <span class="wave">👋</span>
                </h1>
                <p class="text-pink-200 text-sm mt-2 opacity-75">Aquí está el resumen de tu cuenta</p>
            </div>

            {{-- Tarjeta de estadísticas principal --}}
            <div class="glass-card hover-lift rounded-2xl p-8">
                <div class="flex items-center justify-between gap-6">
                    <div class="flex items-center gap-5 flex-1">
                        <div class="icon-circle w-16 h-16 rounded-2xl flex-shrink-0 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-pink-200 text-xs font-semibold uppercase tracking-widest mb-1">Cupones Activos</p>
                            <p class="text-5xl font-extrabold text-white leading-none counter" data-target="{{ $misCupones ?? 0 }}">{{ $misCupones ?? 0 }}</p>
                            <p class="text-pink-200 text-sm mt-2 opacity-75">disponibles para usar</p>
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <div class="star-pulse">
                            <i class="fas fa-star text-yellow-300 text-4xl opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="glass-card rounded-2xl p-6">
                <p class="text-pink-200 text-xs font-semibold uppercase tracking-widest mb-4">Acciones Rápidas</p>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('coupons.index') }}" class="action-tile group flex flex-col items-center gap-3 py-6 px-4 rounded-xl text-white text-center">
                        <div class="icon-tile w-14 h-14 rounded-xl flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-lg"></i>
                        </div>
                        <span class="text-sm font-semibold leading-tight">Mis Cupones</span>
                        <span class="text-xs text-pink-200 opacity-75">Ver todos mis cupones</span>
                    </a>
                    <a href="/mi-tarjeta" class="action-tile group flex flex-col items-center gap-3 py-6 px-4 rounded-xl text-white text-center">
                        <div class="icon-tile w-14 h-14 rounded-xl flex items-center justify-center">
                            <i class="fas fa-qrcode text-lg"></i>
                        </div>
                        <span class="text-sm font-semibold leading-tight">Mi Tarjeta QR</span>
                        <span class="text-xs text-pink-200 opacity-75">Código para sucursales</span>
                    </a>
                </div>
            </div>

        </div>

        @else
        {{-- ── HERO LANDING (no autenticado) ── --}}
        <div class="w-full max-w-2xl">

            {{-- Badge promo --}}
            <div class="flex justify-center mb-6">
                <span class="badge-promo inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2 rounded-full shadow-lg">
                    <i class="fas fa-gem"></i> ¡Bienvenido al programa de fidelidad!
                </span>
            </div>

            {{-- Tarjeta principal --}}
            <div class="glass-card rounded-3xl shadow-2xl p-8 sm:p-10 text-center">

                {{-- Logo --}}
                <div class="flex justify-center mb-5">
                    <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-16 w-auto drop-shadow-lg">
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight tracking-tight">
                    Ser parte<br>
                    tiene sus ventajas
                    
                </h1>

                <p class="text-pink-100 text-base sm:text-lg mt-4 leading-relaxed">
                    Únete al programa <strong class="text-white font-mercurius">La Zarza Contigo</strong> y accede a cupones y beneficios exclusivos en cada compra.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                    <a href="/register"
                       class="btn-cta-primary text-white font-bold px-8 py-3 rounded-xl text-base inline-flex items-center justify-center gap-2">
                        <i class="fas fa-user-plus"></i> Registrarme gratis
                    </a>
                    <a href="/login"
                       class="btn-cta-secondary text-white font-semibold px-8 py-3 rounded-xl text-base inline-flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                    </a>
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-4 my-8">
                    <div class="flex-1 h-px bg-white bg-opacity-20"></div>
                    <span class="text-pink-200 text-xs font-medium uppercase tracking-widest">Beneficios</span>
                    <div class="flex-1 h-px bg-white bg-opacity-20"></div>
                </div>

                {{-- Benefit cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
                    <div class="benefit-card rounded-2xl p-4 text-center">
                        <div class="icon-circle w-11 h-11 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-qrcode text-white text-base"></i>
                        </div>
                        <h3 class="text-white font-semibold text-sm">Tu Tarjeta Digital</h3>
                        <p class="text-pink-200 text-xs mt-1 leading-relaxed">Identifícate fácilmente en sucursales con tu código QR personal.</p>
                    </div>
                    <div class="benefit-card rounded-2xl p-4 text-center">
                        <div class="icon-circle w-11 h-11 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-ticket-alt text-white text-base"></i>
                        </div>
                        <h3 class="text-white font-semibold text-sm">Cupones Exclusivos</h3>
                        <p class="text-pink-200 text-xs mt-1 leading-relaxed">Accede a descuentos y promociones especiales para miembros.</p>
                    </div>
                    <a href="https://lazarza.com.mx/sucursales" target="_blank" rel="noopener noreferrer" class="benefit-card rounded-2xl p-4 text-center block">
                        <div class="icon-circle w-11 h-11 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-map-marker-alt text-white text-base"></i>
                        </div>
                        <h3 class="text-white font-semibold text-sm">Conoce Nuestras Sucursales</h3>
                        <p class="text-pink-200 text-xs mt-1 leading-relaxed">Descubre nuestras ubicaciones y disfruta de beneficios exclusivos en cada visita.</p>
                    </a>
                </div>

                <p class="text-pink-300 text-xs mt-6">
                    <i class="fas fa-lock mr-1"></i> Registro gratuito · Sin compromisos
                </p>
            </div>
        </div>
        @endif

        @if(isset($error))
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-red-600 bg-opacity-95 border border-red-400 text-white px-6 py-3 rounded-xl shadow-2xl text-sm z-50">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}
        </div>
        @endif

    </div>
</div>

@if(isset($isAuthenticated) && $isAuthenticated)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Saludo según hora del día
    const greetEl = document.getElementById('greeting-time');
    if (greetEl) {
        const h = new Date().getHours();
        if (h >= 6  && h < 12) greetEl.textContent = '¡Buenos días!';
        else if (h >= 12 && h < 19) greetEl.textContent = '¡Buenas tardes!';
        else greetEl.textContent = '¡Buenas noches!';
    }

    // Contadores animados
    document.querySelectorAll('.counter').forEach(function (el) {
        const target = parseInt(el.dataset.target) || 0;
        if (target === 0) return;
        let current = 0;
        const duration = 900;
        const steps = 40;
        const increment = target / steps;
        const interval = duration / steps;
        const timer = setInterval(function () {
            current = Math.min(current + increment, target);
            el.textContent = Math.floor(current).toLocaleString();
            if (current >= target) {
                el.textContent = target.toLocaleString();
                clearInterval(timer);
            }
        }, interval);
    });
});
</script>
@endpush
@endif

@endsection