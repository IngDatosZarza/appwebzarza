@extends('layouts.app')

@section('title', 'Historial de Puntos | La Zarza Contigo')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </span>
                Historial de Puntos
            </h1>
            <p class="text-gray-600 mt-2">Revisa todos los movimientos de tus puntos La Zarza Contigo.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('purchase.form') }}" class="btn-primary text-white px-5 py-3 rounded-lg shadow-sm inline-flex items-center gap-2">
                <i class="fas fa-shopping-bag"></i>
                Registrar compra
            </a>
            <a href="{{ route('coupons.index') }}" class="bg-white border border-purple-200 text-purple-600 px-5 py-3 rounded-lg shadow-sm inline-flex items-center gap-2 hover:bg-purple-50">
                <i class="fas fa-ticket-alt"></i>
                Ver cupones
            </a>
        </div>
    </div>

    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl p-6 text-white shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-purple-100">Saldo actual</p>
                <p class="text-4xl font-bold mt-2">{{ number_format($saldoActual) }} pts</p>
                <p class="text-purple-100 text-sm mt-1">Puntos disponibles para canjear</p>
            </div>
            <div class="text-6xl opacity-20">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Movimientos recientes</h2>
            <span class="text-sm text-gray-500">{{ $transacciones->total() }} transacciones registradas</span>
        </div>

        @if($transacciones->isEmpty())
            <div class="text-center py-16">
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-4">
                    <i class="fas fa-exchange-alt text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sin movimientos aún</h3>
                <p class="text-gray-500 mb-6">Registra una compra o canjea un cupón para ver tus movimientos aquí.</p>
                <a href="{{ route('purchase.form') }}" class="btn-primary text-white px-5 py-3 rounded-lg shadow-sm inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Registrar compra
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transacciones as $transaccion)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($transaccion->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badgeClasses = [
                                            'compra' => 'bg-green-100 text-green-800',
                                            'canje' => 'bg-red-100 text-red-800',
                                            'ajuste' => 'bg-blue-100 text-blue-800',
                                        ];
                                        $badgeClass = $badgeClasses[$transaccion->tipo] ?? 'bg-gray-100 text-gray-800';
                                        $icons = [
                                            'compra' => 'fa-plus',
                                            'canje' => 'fa-minus',
                                            'ajuste' => 'fa-adjust',
                                        ];
                                        $icon = $icons[$transaccion->tipo] ?? 'fa-exchange-alt';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        <i class="fas {{ $icon }} mr-1"></i>
                                        {{ ucfirst($transaccion->tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $transaccion->descripcion }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    @if($transaccion->puntos > 0)
                                        <span class="text-green-600">+{{ number_format($transaccion->puntos) }}</span>
                                    @else
                                        <span class="text-red-600">{{ number_format($transaccion->puntos) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ optional($transaccion->registradoPor)->nombre_completo ?? 'Sistema' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $transacciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
