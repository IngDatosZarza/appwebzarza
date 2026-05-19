@extends('layouts.app')

@section('title', 'Escanear Cliente QR - La Zarza Contigo')

@push('styles')
<style>
    .scan-box {
        background: linear-gradient(135deg, #71398d 0%, #b51a8a 100%);
    }
    .profile-card {
        border-left: 4px solid #b51a8a;
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    <h1 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
        <i class="fas fa-qrcode text-purple-600"></i> Identificar Cliente por QR
    </h1>
    <p class="text-gray-500 text-sm mb-6">
        Escanea el código QR del cliente o ingresa su código manualmente.
    </p>

    {{-- Formulario de búsqueda --}}
    <form method="GET" action="{{ route('admin.qr.scan') }}" class="bg-white rounded-2xl shadow p-6 mb-6">
        @csrf
        <label class="block text-sm font-medium text-gray-700 mb-2">Código QR del cliente</label>
        <div class="flex gap-3">
            <input
                type="text"
                name="codigo"
                id="codigo-input"
                value="{{ request('codigo') }}"
                placeholder="ZRZ-XXXXXXXXXXXXXXXX"
                class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400"
                autofocus
            />
            <button
                type="submit"
                class="px-5 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl text-sm hover:opacity-90 transition"
            >
                <i class="fas fa-search mr-1"></i> Buscar
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            Si usas un lector de QR físico, coloca el cursor en el campo y escanea.
        </p>
    </form>

    {{-- Error --}}
    @if($error)
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-lg"></i>
        <span>{{ $error }}</span>
    </div>
    @endif

    {{-- Perfil del cliente encontrado --}}
    @if($usuario)
    <div class="bg-white rounded-2xl shadow-md overflow-hidden profile-card">

        {{-- Header del perfil --}}
        <div class="scan-box text-white px-6 py-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-2xl text-white/80"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold">{{ $usuario->nombre_completo }}</h2>
                <p class="text-white/70 text-sm">{{ $usuario->email }}</p>
                <span class="inline-block mt-1 text-xs bg-white/20 rounded-full px-3 py-0.5 font-mono">
                    {{ $usuario->qr_codigo }}
                </span>
            </div>
        </div>

        {{-- Datos del cliente --}}
        <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b border-gray-100">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Teléfono</p>
                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $usuario->telefono }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider">Miembro desde</p>
                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $usuario->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Resumen de cupones y compras --}}
        <div class="px-6 py-4 grid grid-cols-2 gap-4 border-b border-gray-100">
            <div class="bg-purple-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-purple-700">
                    {{ $usuario->cuponesAsignados->count() }}
                </p>
                <p class="text-xs text-purple-500 mt-1">Cupones disponibles</p>
            </div>
            <div class="bg-pink-50 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-pink-700">
                    {{ $usuario->compras->count() }}
                </p>
                <p class="text-xs text-pink-500 mt-1">Compras registradas</p>
            </div>
        </div>

        {{-- Cupones asignados --}}
        @if($usuario->cuponesAsignados->count() > 0)
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-ticket-alt text-pink-500"></i> Cupones disponibles
            </h3>
            <div class="space-y-2">
                @foreach($usuario->cuponesAsignados as $asignado)
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $asignado->cupon->nombre ?? 'Cupón' }}</p>
                        <p class="text-xs text-gray-400">{{ $asignado->cupon->descripcion ?? '' }}</p>
                    </div>
                    <span class="text-xs bg-green-100 text-green-700 font-semibold rounded-full px-3 py-1">
                        Vigente
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Últimas compras --}}
        @if($usuario->compras->count() > 0)
        <div class="px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="fas fa-receipt text-purple-500"></i> Últimas compras
            </h3>
            <div class="space-y-2">
                @foreach($usuario->compras as $compra)
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2">
                    <div>
                        <p class="text-sm font-medium text-gray-800">
                            Ticket #{{ $compra->numero_ticket ?? $compra->id }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $compra->fecha_compra ? \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') : '—' }}
                        </p>
                    </div>
                    <span class="text-sm font-bold text-gray-700">
                        ${{ number_format($compra->monto, 2) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Acciones --}}
        <div class="px-6 py-4 bg-gray-50 flex gap-3">
            <a href="{{ route('purchase.form') }}?usuario_id={{ $usuario->id }}"
               class="flex-1 text-center py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold rounded-xl hover:opacity-90 transition">
                <i class="fas fa-receipt mr-1"></i> Registrar compra
            </a>
        </div>

    </div>
    @endif

</div>
@endsection
