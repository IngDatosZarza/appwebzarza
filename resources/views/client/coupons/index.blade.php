@extends('layouts.app')

@section('title', 'Promociones - La Zarza Contigo')

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
    .promo-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
    }
    .promo-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }
    .day-badge-active {
        background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%);
        color: white;
    }
    .day-badge-inactive {
        background: rgba(255, 255, 255, 0.3);
        color: rgba(255, 255, 255, 0.5);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('/PROPORCIONAL FHD.jpg');">
<div style="background: linear-gradient(135deg, rgba(113, 57, 141, 0.80) 0%, rgba(181, 26, 138, 0.70) 100%); min-height: 100vh;" class="py-8 px-4 sm:px-6 lg:px-8">
<div class="max-w-6xl mx-auto">

    <!-- Header -->
    <div class="glass-card rounded-2xl p-8 mb-8 text-white text-center">
        <h1 class="text-4xl font-bold mb-3">
            <i class="fas fa-tags mr-3"></i>
            Promociones Disponibles
        </h1>
        <p class="text-pink-100 text-lg mb-4">Descubre las ofertas vigentes en todas nuestras sucursales</p>
        <div class="flex items-center justify-center gap-2 text-sm text-pink-200">
            <i class="fas fa-qrcode"></i>
            <span>Presenta tu <strong>código QR</strong> en el punto de venta para aplicar la promoción</span>
        </div>
    </div>

    <!-- Promociones Grid -->
    @if(isset($promociones) && $promociones->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($promociones as $promo)
                <div class="promo-card rounded-xl overflow-hidden shadow-lg">
                    <!-- Header de la promo con color -->
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-5 py-4 text-white">
                        <div class="flex items-start justify-between">
                            <h3 class="text-lg font-bold flex-1">{{ $promo->nombre }}</h3>
                            <span class="ml-2 bg-white bg-opacity-25 text-white text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap">
                                {{ $promo->resumen_accion }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <!-- Descripción -->
                        @if($promo->descripcion_limpia)
                            <p class="text-gray-600 text-sm mb-4">{{ $promo->descripcion_limpia }}</p>
                        @endif

                        <!-- Vigencia -->
                        <div class="flex items-center text-sm text-gray-500 mb-3">
                            <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                            <span>
                                {{ $promo->fecha_inicio->format('d/m/Y') }} — {{ $promo->fecha_fin->format('d/m/Y') }}
                            </span>
                        </div>

                        <!-- Horario -->
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <i class="fas fa-clock text-purple-500 mr-2"></i>
                            <span>{{ $promo->horario_texto }}</span>
                        </div>

                        <!-- Días de la semana -->
                        <div class="flex flex-wrap gap-1 mb-4">
                            @php
                                $diasMap = [
                                    'Monday' => 'L', 'Tuesday' => 'M', 'Wednesday' => 'X',
                                    'Thursday' => 'J', 'Friday' => 'V', 'Saturday' => 'S', 'Sunday' => 'D'
                                ];
                                $diasPromo = $promo->dias_semana ?? [];
                                $diaHoy = now()->format('l');
                            @endphp
                            @foreach($diasMap as $en => $abbr)
                                @php $activo = !empty($diasPromo[$en]); @endphp
                                <span class="w-8 h-8 flex items-center justify-center rounded-full text-xs font-bold
                                    {{ $activo ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-400' }}
                                    {{ $activo && $en === $diaHoy ? 'ring-2 ring-pink-400 ring-offset-1' : '' }}">
                                    {{ $abbr }}
                                </span>
                            @endforeach
                        </div>

                        <!-- Disponible ahora -->
                        @if($promo->estaDisponibleAhora())
                            <div class="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-2 text-sm text-green-700">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                <span class="font-medium">Disponible ahora</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-500">
                                <i class="fas fa-moon"></i>
                                <span>Fuera de horario</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Sin promociones -->
        <div class="glass-card rounded-2xl p-12 text-center text-white mb-8">
            <i class="fas fa-tags text-6xl mb-4 opacity-50"></i>
            <h3 class="text-2xl font-bold mb-2">No hay promociones disponibles</h3>
            <p class="text-pink-200">¡Vuelve pronto para descubrir nuevas ofertas!</p>
        </div>
    @endif

    <!-- Cómo funciona -->
    <div class="glass-card rounded-2xl p-6 mb-8">
        <h3 class="text-lg font-bold text-white mb-5 text-center">
            <i class="fas fa-question-circle mr-2"></i>
            ¿Cómo aplico una promoción?
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-white">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold">1</div>
                <div>
                    <div class="font-semibold mb-1">Revisa las promociones</div>
                    <div class="text-sm text-pink-100">Consulta aquí las promos vigentes y sus horarios</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold">2</div>
                <div>
                    <div class="font-semibold mb-1">Presenta tu QR</div>
                    <div class="text-sm text-pink-100">Muestra tu código QR personal al cajero en cualquier sucursal</div>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold">3</div>
                <div>
                    <div class="font-semibold mb-1">¡Listo!</div>
                    <div class="text-sm text-pink-100">El cajero aplica la promoción automáticamente en tu compra</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón a mi tarjeta QR -->
    @if(Session::get('user_authenticated', false))
        <div class="text-center mb-8">
            <a href="{{ route('client.mi-tarjeta') }}" class="inline-flex items-center gap-3 bg-white text-purple-700 font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-qrcode text-2xl"></i>
                <span>Ver mi código QR</span>
            </a>
        </div>
    @endif

</div>
</div>
</div>
@endsection
