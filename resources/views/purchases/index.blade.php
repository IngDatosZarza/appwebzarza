@extends('layouts.app')

@section('title', 'Mis Compras | La Zarza Contigo')

@section('content')
<div class="space-y-8">
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-pink-100 text-pink-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </span>
                Mis Compras
            </h1>
            <p class="text-gray-600 mt-2">Consulta el historial de compras que has registrado.</p>
        </div>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Total de compras</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['total_compras'] ?? 0) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Monto acumulado</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">$ {{ number_format($stats['total_monto'] ?? 0, 2) }}</div>
        </div>
    </div>

    <!-- Tabla de compras -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Historial</h2>
            <span class="text-sm text-gray-500">Mostrando {{ $compras->count() }} de {{ $compras->total() }} compras</span>
        </div>

        @if($compras->isEmpty())
            <div class="text-center py-16">
                <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-4">
                    <i class="fas fa-shopping-basket text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aún no registras compras</h3>
                <p class="text-gray-500 mb-6">Tus compras aparecerán aquí cuando sean registradas.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sucursal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método de pago</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($compras as $compra)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ optional($compra->fecha_compra)->format('d/m/Y') ?? optional($compra->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $compra->numero_ticket ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $compra->sucursal->nombre ?? 'Sin sucursal' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    $ {{ number_format($compra->monto ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $compra->metodo_pago ?? 'No especificado' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $compras->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
