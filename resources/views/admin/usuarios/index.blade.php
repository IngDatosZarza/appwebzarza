@extends('layouts.admin')

@section('title', 'Administradores de Sucursal')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-users-cog text-indigo-600 mr-2"></i>
                    Administradores de Sucursal
                </h1>
                <p class="text-gray-500 mt-1">Gestiona los administradores que registran clientes en cada sucursal</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
                <i class="fas fa-plus mr-2"></i> Nuevo Administrador
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <i class="fas fa-users-cog text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Admins Activos</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $administradores->where('activo', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalClientes }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-store text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Sucursales</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $sucursales->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('admin.usuarios.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('sucursal_id') == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                <select name="activo" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="1" {{ request('activo') === '1' ? 'selected' : '' }}>Activos</option>
                    <option value="0" {{ request('activo') === '0' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                Limpiar
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-4 pt-4 pb-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <span class="text-sm text-gray-500">{{ $administradores->count() }} administradores</span>
            <input type="text" id="buscadorAdmins" placeholder="Buscar por nombre, email, sucursal..."
                oninput="filtrarTabla('buscadorAdmins','tablaAdmins')"
                class="flex-1 sm:max-w-xs px-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">
            <table id="tablaAdmins" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Administrador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Clientes</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Último Acceso</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($administradores as $adm)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($adm->nombres, 0, 1) . substr($adm->apellido_paterno, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $adm->nombre_completo }}</div>
                                    <div class="text-xs text-gray-500">ID: {{ $adm->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($adm->sucursal)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-store mr-1"></i> {{ $adm->sucursal->nombre }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">Sin asignar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $adm->email }}</div>
                            <div class="text-xs text-gray-500">{{ $adm->telefono }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $adm->clientes_registrados_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $adm->clientes_registrados_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($adm->activo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            {{ $adm->ultimo_acceso ? $adm->ultimo_acceso->diffForHumans() : 'Nunca' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="{{ route('admin.usuarios.edit', $adm->id) }}" class="text-indigo-600 hover:text-indigo-800" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.usuarios.toggle', $adm->id) }}" class="inline" onsubmit="return confirm('¿Estás seguro de {{ $adm->activo ? 'desactivar' : 'activar' }} este administrador?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $adm->activo ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700' }}" title="{{ $adm->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas {{ $adm->activo ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.usuarios.reset-password', $adm->id) }}" class="inline" onsubmit="return confirm('¿Generar nueva contraseña temporal para {{ $adm->nombre_completo }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-yellow-500 hover:text-yellow-700" title="Resetear contraseña">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users-cog text-4xl text-gray-300 mb-2"></i>
                            <p>No hay administradores de sucursal registrados.</p>
                            <a href="{{ route('admin.usuarios.create') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i> Crear el primero
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
