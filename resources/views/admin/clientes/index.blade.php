@extends('layouts.admin')

@section('title', 'Todos los Clientes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-users text-indigo-600 mr-2"></i>
            Todos los Clientes
        </h1>
        <p class="text-gray-500 mt-1">Vista completa de clientes con información de quién los registró</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600"><i class="fas fa-users text-xl"></i></div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalClientes }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600"><i class="fas fa-store text-xl"></i></div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Por Admin Sucursal</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPorAdmin }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gray-100 text-gray-600"><i class="fas fa-user text-xl"></i></div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Autoregistro</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalAutoregistro }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('admin.clientes.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, email, teléfono..."
                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Administrador</label>
                <select name="administrador_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    @foreach($administradores as $adm)
                        <option value="{{ $adm->id }}" {{ request('administrador_id') == $adm->id ? 'selected' : '' }}>
                            {{ $adm->nombre_completo }} {{ $adm->sucursal ? '('.$adm->sucursal->nombre.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Origen</label>
                <select name="origen" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="autoregistro" {{ request('origen') === 'autoregistro' ? 'selected' : '' }}>Autoregistro</option>
                    <option value="admin_sucursal" {{ request('origen') === 'admin_sucursal' ? 'selected' : '' }}>Admin Sucursal</option>
                    <option value="campana" {{ request('origen') === 'campana' ? 'selected' : '' }}>Campaña</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('admin.clientes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Limpiar</a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-4 pt-4 pb-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <span class="text-sm text-gray-500">{{ $clientes->total() }} clientes encontrados</span>
            <input type="text" id="buscadorClientesRapido" placeholder="Filtrar en esta página..."
                oninput="filtrarTabla('buscadorClientesRapido','tablaClientes')"
                class="flex-1 sm:max-w-xs px-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto" style="max-height: 520px; overflow-y: auto;">
            <table id="tablaClientes" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $cliente->nombre_completo }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $cliente->id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-700">{{ $cliente->email }}</div>
                            <div class="text-xs text-gray-500">{{ $cliente->telefono }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($cliente->registradoPorAdministrador)
                                <div class="text-blue-700 font-medium">{{ $cliente->registradoPorAdministrador->nombre_completo }}</div>
                                <div class="text-xs text-gray-500">{{ $cliente->registradoPorAdministrador->email }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($cliente->sucursalRegistro)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">
                                    <i class="fas fa-store mr-1"></i> {{ $cliente->sucursalRegistro->nombre }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($cliente->origen_registro === 'admin_sucursal')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">Sucursal</span>
                            @elseif($cliente->origen_registro === 'campana')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-purple-50 text-purple-700">Campaña</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-50 text-gray-600">Auto</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cliente->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <p>No se encontraron clientes con los filtros seleccionados.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $clientes->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function filtrarTabla(inputId, tablaId) {
    const filtro = document.getElementById(inputId).value.toLowerCase();
    const filas = document.querySelectorAll('#' + tablaId + ' tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
}
</script>
@endpush
