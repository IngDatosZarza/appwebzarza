@extends('layouts.app')

@section('title', 'Cupones Disponibles')

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
</style>
@endpush

@section('content')
<div class="min-h-screen bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('/PROPORCIONAL FHD.jpg');">
<div style="background: linear-gradient(135deg, rgba(113, 57, 141, 0.75) 0%, rgba(181, 26, 138, 0.65) 100%); min-height: 100vh;" class="py-8 px-4 sm:px-6 lg:px-8">
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-ticket-alt text-purple-500 mr-3"></i>
                    Cupones Disponibles
                </h1>
                <p class="text-gray-600 mt-2">Canjea tus puntos por increíbles descuentos y ofertas</p>
            </div>
            @auth
                <div class="text-right">
                    <div class="text-sm text-gray-500">Tus puntos disponibles</div>
                    <div class="text-2xl font-bold text-purple-600">
                        <i class="fas fa-coins mr-1"></i>
                        {{ number_format(auth()->user()->puntos->saldo ?? 0) }}
                    </div>
                </div>
            @endauth
        </div>
    </div>

    @auth
        <!-- User's Active and Blocked Coupons -->
        @php
            $misCuponesActivos = auth()->user()->cuponesAsignados->where('estado', 'pendiente');
            $misCuponesBloqueados = auth()->user()->cuponesAsignados->where('estado', 'bloqueado');
        @endphp
        
        @if($misCuponesActivos->count() > 0 || $misCuponesBloqueados->count() > 0)
            <div class="space-y-6">
                <!-- Active Coupons -->
                @if($misCuponesActivos->count() > 0)
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-bookmark text-green-500 mr-2"></i>
                            Mis Cupones Listos para Usar
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($misCuponesActivos as $cuponAsignado)
                                <div class="bg-white rounded-lg border border-green-200 p-4 card-hover">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $cuponAsignado->cupon->nombre }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ $cuponAsignado->cupon->descripcion }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Listo
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            @if($cuponAsignado->cupon->tipo_descuento === 'porcentaje')
                                                <span class="text-lg font-bold text-green-600">{{ $cuponAsignado->cupon->valor_descuento }}% OFF</span>
                                            @else
                                                <span class="text-lg font-bold text-green-600">${{ number_format($cuponAsignado->cupon->valor_descuento, 2) }}</span>
                                            @endif
                                        </div>
                                        <div class="text-center">
                                            <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mb-1">
                                                <i class="fas fa-qrcode text-green-600"></i>
                                            </div>
                                            <p class="text-xs text-gray-500">{{ $cuponAsignado->codigo_qr }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Blocked Coupons (Gamification) -->
                @if($misCuponesBloqueados->count() > 0)
                    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-target text-orange-500 mr-2"></i>
                            Mis Metas de Cupones
                            <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                {{ $misCuponesBloqueados->count() }} 🎯
                            </span>
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($misCuponesBloqueados as $cuponBloqueado)
                                @php
                                    $puntosActuales = auth()->user()->puntos->saldo ?? 0;
                                    $puntosNecesarios = $cuponBloqueado->cupon->puntos_requeridos;
                                    $porcentajeProgreso = $puntosNecesarios > 0 ? min(100, ($puntosActuales / $puntosNecesarios) * 100) : 0;
                                @endphp
                                <div class="bg-white rounded-lg border border-orange-200 p-4 card-hover">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $cuponBloqueado->cupon->nombre }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ $cuponBloqueado->cupon->descripcion }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            <i class="fas fa-lock mr-1"></i>
                                            Bloqueado
                                        </span>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600">Progreso</span>
                                            <span class="font-semibold text-orange-600">{{ number_format($porcentajeProgreso, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-orange-200 rounded-full h-2">
                                            @php
                                                $widthClass = $porcentajeProgreso < 25 ? 'w-1/4' : ($porcentajeProgreso < 50 ? 'w-1/2' : ($porcentajeProgreso < 75 ? 'w-3/4' : 'w-full'));
                                            @endphp
                                            <div class="bg-gradient-to-r from-orange-400 to-yellow-400 h-2 rounded-full transition-all duration-500 {{ $widthClass }}"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                                            <span>{{ number_format($puntosActuales) }} puntos</span>
                                            <span>{{ number_format($puntosNecesarios) }} puntos</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            @if($cuponBloqueado->cupon->tipo_descuento === 'porcentaje')
                                                <span class="text-lg font-bold text-orange-600">{{ $cuponBloqueado->cupon->valor_descuento }}% OFF</span>
                                            @else
                                                <span class="text-lg font-bold text-orange-600">${{ number_format($cuponBloqueado->cupon->valor_descuento, 2) }}</span>
                                            @endif
                                            <p class="text-xs text-orange-600 font-medium">
                                                ¡Faltan {{ number_format($puntosNecesarios - $puntosActuales) }} puntos!
                                            </p>
                                        </div>
                                        <div class="text-center">
                                            <div class="w-16 h-16 bg-orange-100 rounded-lg flex items-center justify-center mb-1">
                                                <i class="fas fa-lock text-orange-600"></i>
                                            </div>
                                            <p class="text-xs text-gray-500">Bloqueado</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 space-y-3">
                            <!-- Botón para verificar desbloqueos -->
                            <form action="{{ route('coupons.unlock') }}" method="POST" class="w-full">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105"
                                >
                                    <i class="fas fa-unlock-alt mr-2"></i>
                                    ¡Verificar si puedo desbloquear cupones!
                                </button>
                            </form>
                            
                            <!-- Tip -->
                            <div class="bg-orange-100 border border-orange-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-lightbulb text-orange-500 mr-3"></i>
                                    <div>
                                        <h4 class="font-medium text-orange-900">💡 ¡Tip para completar tus metas!</h4>
                                        <p class="text-sm text-orange-700 mt-1">
                                            Sigue comprando en nuestras sucursales para ganar más puntos. ¡Cada compra te acerca más a desbloquear tus cupones!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        
        @if($misCuponesActivos->count() === 0 && $misCuponesBloqueados->count() === 0)
            <!-- Mensaje cuando no tiene cupones -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg shadow-sm p-6 text-center">
                <i class="fas fa-gift text-gray-400 text-4xl mb-3"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">¡Comienza tu colección de cupones!</h3>
                <p class="text-gray-600">Obtén cupones para crear metas y desbloquearlos ganando puntos.</p>
            </div>
        @endif
        
        @if($misCuponesActivos->count() > 0)
            <!-- Original active coupons code for backward compatibility -->
            <div style="display: none;">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($misCuponesActivos as $cuponAsignado)
                        <div class="bg-white rounded-lg border border-green-200 p-4 card-hover">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $cuponAsignado->cupon->nombre }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $cuponAsignado->cupon->descripcion }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Activo
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    @if($cuponAsignado->cupon->tipo_descuento === 'porcentaje')
                                        <span class="text-lg font-bold text-green-600">{{ $cuponAsignado->cupon->valor_descuento }}% OFF</span>
                                    @else
                                        <span class="text-lg font-bold text-green-600">${{ number_format($cuponAsignado->cupon->valor_descuento, 2) }}</span>
                                    @endif
                                    <p class="text-xs text-gray-500">Vence: {{ $cuponAsignado->fecha_vencimiento->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mb-1">
                                        <i class="fas fa-qrcode text-gray-600"></i>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $cuponAsignado->codigo_qr }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endauth

    <!-- Available Coupons -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-shopping-bag text-purple-500 mr-2"></i>
            Cupones para Canjear
        </h2>
        
        @php
            $cupones = \App\Models\Cupon::where('activo', true)
                ->where('fecha_vencimiento', '>=', now())
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('puntos_requeridos', 'asc')
                ->get();
        @endphp
        
        @if($cupones->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="{ redeeming: null }">
                @foreach($cupones as $cupon)
                    <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 card-hover">
                        <!-- Coupon Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $cupon->nombre }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $cupon->descripcion }}</p>
                            </div>
                            @if($cupon->tipo_descuento === 'porcentaje')
                                <div class="bg-gradient-to-r from-red-500 to-pink-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                    {{ $cupon->valor_descuento }}% OFF
                                </div>
                            @else
                                <div class="bg-gradient-to-r from-green-500 to-blue-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                    ${{ number_format($cupon->valor_descuento, 2) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Coupon Details -->
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Puntos requeridos:</span>
                                <span class="font-semibold text-purple-600">
                                    <i class="fas fa-coins mr-1"></i>
                                    {{ number_format($cupon->puntos_requeridos) }}
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Disponibles:</span>
                                <span class="font-semibold text-gray-900">{{ $cupon->cantidad_disponible }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Válido hasta:</span>
                                <span class="font-semibold text-gray-900">{{ $cupon->fecha_vencimiento->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        @auth
                            @php
                                $puntosUsuario = auth()->user()->puntos->saldo ?? 0;
                                $puedeCanjar = $puntosUsuario >= $cupon->puntos_requeridos;
                                $yaCanjeado = auth()->user()->cuponesAsignados
                                    ->where('cupon_id', $cupon->id)
                                    ->where('estado', 'asignado')
                                    ->count() > 0;
                            @endphp
                            
                            @if($yaCanjeado && !$cupon->multiple_uso)
                                <button disabled class="w-full bg-gray-100 text-gray-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed">
                                    <i class="fas fa-check mr-2"></i>
                                    Ya Canjeado
                                </button>
                            @elseif($puedeCanjar)
                                <form action="{{ route('coupons.redeem') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="cupon_id" value="{{ $cupon->id }}">
                                    <button 
                                        type="submit" 
                                        class="w-full bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105"
                                        x-bind:disabled="redeeming === {{ $cupon->id }}"
                                        @click="redeeming = {{ $cupon->id }}"
                                    >
                                        <span x-show="redeeming !== {{ $cupon->id }}">
                                            <i class="fas fa-gift mr-2"></i>
                                            Canjear Cupón
                                        </span>
                                        <span x-show="redeeming === {{ $cupon->id }}" class="flex items-center justify-center">
                                            <i class="fas fa-spinner fa-spin mr-2"></i>
                                            Canjeando...
                                        </span>
                                    </button>
                                </form>
                            @else
                                <div class="w-full space-y-2">
                                    <!-- Botón para obtener cupón (gamificación) -->
                                    <form action="{{ route('coupons.assign') }}" method="POST" class="w-full">
                                        @csrf
                                        <input type="hidden" name="cupon_id" value="{{ $cupon->id }}">
                                        <button 
                                            type="submit" 
                                            class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 hover:from-orange-600 hover:to-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105 mb-2"
                                            x-bind:disabled="redeeming === {{ $cupon->id }}"
                                            @click="redeeming = {{ $cupon->id }}"
                                        >
                                            <span x-show="redeeming !== {{ $cupon->id }}">
                                                <i class="fas fa-target mr-2"></i>
                                                ¡Obtener Meta!
                                            </span>
                                            <span x-show="redeeming === {{ $cupon->id }}" class="flex items-center justify-center">
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                Obteniendo...
                                            </span>
                                        </button>
                                    </form>
                                    
                                    <!-- Información de puntos faltantes -->
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-bullseye text-orange-500 mr-2"></i>
                                                <span class="text-sm font-medium text-orange-800">Meta de Puntos</span>
                                            </div>
                                            <span class="text-sm font-bold text-orange-600">
                                                +{{ number_format($cupon->puntos_requeridos - $puntosUsuario) }} puntos
                                            </span>
                                        </div>
                                        <div class="mt-2 w-full bg-orange-200 rounded-full h-2">
                                            @php
                                                $porcentaje = $cupon->puntos_requeridos > 0 ? min(100, ($puntosUsuario / $cupon->puntos_requeridos) * 100) : 0;
                                                $widthClass = $porcentaje < 25 ? 'w-1/4' : ($porcentaje < 50 ? 'w-1/2' : ($porcentaje < 75 ? 'w-3/4' : 'w-full'));
                                            @endphp
                                            <div class="bg-gradient-to-r from-orange-400 to-yellow-400 h-2 rounded-full transition-all duration-500 {{ $widthClass }}"></div>
                                        </div>
                                        <p class="text-xs text-orange-700 mt-1">
                                            {{ number_format($porcentaje, 1) }}% completado
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white text-center px-4 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Iniciar Sesión para Canjear
                            </a>
                        @endauth
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-ticket-alt text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay cupones disponibles</h3>
                <p class="text-gray-600">¡Vuelve pronto para ver nuevas ofertas!</p>
            </div>
        @endif
    </div>

    <!-- How It Works -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6 text-center">
            <i class="fas fa-question-circle text-blue-500 mr-2"></i>
            ¿Cómo Funciona el Canje?
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-blue-600 font-bold">1</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Acumula Puntos</h3>
                <p class="text-sm text-gray-600">Gana puntos con cada compra en nuestras sucursales</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-purple-600 font-bold">2</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Elige tu Cupón</h3>
                <p class="text-sm text-gray-600">Selecciona el cupón que más te convenga</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-green-600 font-bold">3</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Canjea</h3>
                <p class="text-sm text-gray-600">Usa tus puntos para obtener el cupón</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-orange-600 font-bold">4</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Disfruta</h3>
                <p class="text-sm text-gray-600">Presenta tu código QR en cualquier sucursal</p>
            </div>
        </div>
    </div>
</div>
</div>
</div>
@endsection