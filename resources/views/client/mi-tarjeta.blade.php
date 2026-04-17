@extends('layouts.app')

@section('title', 'Mi Tarjeta QR - La Zarza Contigo')

@push('styles')
<style>
    .qr-card {
        background: linear-gradient(135deg, #71398d 0%, #b51a8a 100%);
    }
    .qr-code-container {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px;
        display: inline-block;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }
    .qr-img {
        width: 220px;
        height: 220px;
        display: block;
    }
    .code-badge {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.875rem;
        letter-spacing: 0.15em;
    }
    .info-row {
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }
    .info-row:last-child {
        border-bottom: none;
    }
</style>
@endpush

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">

    {{-- Tarjeta QR --}}
    <div class="qr-card rounded-2xl text-white shadow-xl overflow-hidden">

        {{-- Encabezado --}}
        <div class="px-6 pt-6 pb-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-white/70 uppercase tracking-wider font-semibold">La Zarza Contigo</p>
                <h1 class="text-2xl font-bold mt-1">
                    {{ $usuario->nombres }} {{ $usuario->apellido_paterno }}
                </h1>
                <p class="text-white/60 text-sm mt-0.5">
                    Miembro desde {{ $usuario->created_at->format('M Y') }}
                </p>
            </div>
            <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                <i class="fas fa-user text-2xl text-white/80"></i>
            </div>
        </div>

        {{-- Código QR --}}
        <div class="flex flex-col items-center py-8 bg-black/10">
            <div class="qr-code-container">
                <img
                    id="qr-image"
                    src="{{ route('qr.usuario', $usuario->qr_codigo) }}"
                    alt="Código QR personal"
                    class="qr-img"
                    onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode('ZRZ|' . $usuario->qr_codigo) }}&bgcolor=ffffff&color=71398d&margin=10'"
                />
            </div>
            <p class="mt-4 text-white/60 text-sm text-center px-6">
                Muestra este código en la sucursal para que te identifiquen
            </p>
            <div class="mt-3 bg-white/20 rounded-full px-5 py-2">
                <span class="code-badge text-white font-bold">{{ $usuario->qr_codigo }}</span>
            </div>
        </div>

        {{-- Datos del usuario --}}
        <div class="px-6 py-4 space-y-3">
            <div class="info-row flex justify-between items-center pb-3">
                <span class="text-white/60 text-sm"><i class="fas fa-envelope mr-2"></i>Correo</span>
                <span class="text-sm font-medium text-white">{{ $usuario->email }}</span>
            </div>
            <div class="info-row flex justify-between items-center pb-3">
                <span class="text-white/60 text-sm"><i class="fas fa-phone mr-2"></i>Teléfono</span>
                <span class="text-sm font-medium text-white">{{ $usuario->telefono }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-white/60 text-sm"><i class="fas fa-tag mr-2"></i>Cupones disponibles</span>
                <span class="text-sm font-bold text-white bg-white/20 rounded-full px-3 py-0.5">
                    {{ $cuponesDisponibles }}
                </span>
            </div>
        </div>

        {{-- Pie de tarjeta --}}
        <div class="px-6 pt-2 pb-5">
            <button
                onclick="window.print()"
                class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold rounded-xl py-3 transition text-sm mt-2"
            >
                <i class="fas fa-print mr-2"></i> Imprimir mi tarjeta
            </button>
        </div>
    </div>

    {{-- Instrucciones --}}
    <div class="mt-6 bg-purple-50 border border-purple-100 rounded-xl p-5 space-y-3">
        <h3 class="font-semibold text-purple-800 flex items-center gap-2">
            <i class="fas fa-info-circle"></i> ¿Cómo usar mi QR?
        </h3>
        <ol class="list-decimal list-inside text-purple-700 text-sm space-y-1.5 leading-relaxed">
            <li>Muestra la pantalla de tu celular al personal de la sucursal.</li>
            <li>Ellos escaneará el código para identificar tu cuenta.</li>
            <li>Podrás recibir cupones y registrar tus compras.</li>
            <li>También puedes imprimir esta tarjeta para llevarla contigo.</li>
        </ol>
    </div>

    {{-- Acciones rápidas --}}
    <div class="mt-4 grid grid-cols-2 gap-3">
        <a href="{{ route('tickets.create') }}"
           class="flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl py-3 text-sm shadow hover:shadow-md transition">
            <i class="fas fa-receipt"></i> Registrar ticket
        </a>
        <a href="{{ route('coupons.my') }}"
           class="flex items-center justify-center gap-2 bg-white border-2 border-purple-200 text-purple-700 font-semibold rounded-xl py-3 text-sm shadow hover:shadow-md transition">
            <i class="fas fa-ticket-alt"></i> Mis cupones
        </a>
    </div>

</div>
@endsection
