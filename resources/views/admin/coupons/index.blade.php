@extends('layouts.app')

@section('title', 'Gestión de Cupones - Admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-ticket-alt text-purple-600 mr-3"></i>
                    Gestión de Cupones
                </h1>
                <p class="text-gray-600 mt-2">Administra cupones, ofertas y promociones del sistema</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.coupons.validate') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-qrcode mr-2"></i>
                    Validar Cupones
                </a>
                <a href="{{ route('admin.coupons.create') }}" class="zarza-bg hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition-colors duration-200 inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Cupón
                </a>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Activos</h3>
                    <p class="text-2xl font-bold text-green-600">
                        {{ collect($cupones)->where('activo', true)->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Vigentes</h3>
                    <p class="text-2xl font-bold text-blue-600">
                        {{ collect($cupones)->where('estado_vigencia', 'vigente')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-ticket-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Asignados</h3>
                    <p class="text-2xl font-bold text-yellow-600">
                        {{ collect($cupones)->sum('total_asignados') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Usados</h3>
                    <p class="text-2xl font-bold text-purple-600">
                        {{ collect($cupones)->sum('usados') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Cupones -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-list mr-2"></i>
                Lista de Cupones
            </h2>
        </div>

        @if(count($cupones) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cupón</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vigencia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estadísticas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($cupones as $cupon)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $cupon['nombre'] }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($cupon['descripcion'], 60) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ date('d/m/Y', strtotime($cupon['fecha_inicio'])) }} - 
                                        {{ date('d/m/Y', strtotime($cupon['fecha_fin'])) }}
                                    </div>
                                    @if($cupon['estado_vigencia'] == 'vigente')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Vigente
                                        </span>
                                    @elseif($cupon['estado_vigencia'] == 'vencido')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Vencido
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                            <i class="fas fa-clock mr-1"></i>
                                            Futuro
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($cupon['activo'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-eye mr-1"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-eye-slash mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <div class="flex items-center">
                                            <i class="fas fa-ticket-alt text-blue-500 mr-1"></i>
                                            <span class="mr-3">{{ $cupon['total_asignados'] }} asignados</span>
                                        </div>
                                        <div class="flex items-center mt-1">
                                            <i class="fas fa-check text-green-500 mr-1"></i>
                                            <span class="mr-3">{{ $cupon['usados'] }} usados</span>
                                            <i class="fas fa-clock text-yellow-500 mr-1"></i>
                                            <span>{{ $cupon['disponibles'] }} disponibles</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.coupons.edit', $cupon['id']) }}" 
                                           class="text-blue-600 hover:text-blue-900 transition-colors"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button class="btn-assign text-green-600 hover:text-green-900 transition-colors"
                                                data-id="{{ $cupon['id'] }}"
                                                data-name="{{ $cupon['nombre'] }}"
                                                title="Asignar a cliente">
                                            <i class="fas fa-user-plus"></i>
                                        </button>

                                        <button class="btn-view text-purple-600 hover:text-purple-900 transition-colors"
                                                data-id="{{ $cupon['id'] }}"
                                                title="Ver asignaciones">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        
                                        @if($cupon['total_asignados'] == 0)
                                            <button class="btn-delete text-red-600 hover:text-red-900 transition-colors"
                                                    data-id="{{ $cupon['id'] }}"
                                                    data-name="{{ $cupon['nombre'] }}"
                                                    title="Eliminar cupón">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <i class="fas fa-ticket-alt text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay cupones creados</h3>
                <p class="text-gray-500 mb-4">Comienza creando tu primer cupón para el sistema</p>
                <a href="{{ route('admin.coupons.create') }}" class="zarza-bg hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Cupón
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Asignación de Cupón -->
<div id="assignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-user-plus text-green-600 mr-2"></i>
                        Asignar Cupón a Cliente
                    </h3>
                    <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="assignForm" method="POST">
                    @csrf
                    <div class="mb-4 p-3 bg-purple-50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cupón seleccionado:</label>
                        <p id="couponName" class="text-gray-900 font-medium"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="usuario_id" class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Cliente:</label>
                        <select id="usuario_id" 
                                name="usuario_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                                required>
                            <option value="">-- Selecciona un cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente['id'] }}">
                                    {{ $cliente['nombre_completo'] }} ({{ $cliente['email'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div id="clienteInfo" class="mb-4 p-3 bg-blue-50 rounded-lg hidden">
                        <h4 class="text-sm font-medium text-blue-800 mb-2">Cliente seleccionado</h4>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" 
                                onclick="closeAssignModal()" 
                                class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                id="submitBtn"
                                class="zarza-bg hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-user-plus mr-2"></i>
                            Asignar Cupón
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ver Asignaciones -->
<div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-users text-purple-600 mr-2"></i>
                        Asignaciones del Cupón
                    </h3>
                    <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="assignmentsList" class="max-h-96 overflow-y-auto">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                        Confirmar Eliminación
                    </h3>
                </div>
                
                <p class="text-gray-600 mb-4">
                    ¿Estás seguro de que deseas eliminar el cupón "<span id="deleteCouponName" class="font-medium"></span>"?
                </p>
                <p class="text-sm text-red-600 mb-4">Esta acción no se puede deshacer.</p>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentCouponPoints = 0;

function openAssignModal(couponId, couponName) {
    document.getElementById('assignModal').classList.remove('hidden');
    document.getElementById('couponName').textContent = couponName;
    document.getElementById('assignForm').action = `/admin/cupones/${couponId}/asignar`;
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('usuario_id').value = '';
    document.getElementById('clienteInfo').classList.add('hidden');
}

// Manejar eventos
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners para los botones
    document.querySelectorAll('.btn-assign').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            openAssignModal(id, name);
        });
    });

    document.querySelectorAll('.btn-view').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            openViewModal(id);
        });
    });

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            confirmDelete(id, name);
        });
    });

    // Manejar cambio de cliente seleccionado
    const usuarioSelect = document.getElementById('usuario_id');
    const clienteInfo = document.getElementById('clienteInfo');
    const submitBtn = document.getElementById('submitBtn');
    
    usuarioSelect.addEventListener('change', function() {
        if (this.value) {
            clienteInfo.classList.remove('hidden');
        } else {
            clienteInfo.classList.add('hidden');
        }
    });
});

function openViewModal(couponId) {
    document.getElementById('viewModal').classList.remove('hidden');
    document.getElementById('assignmentsList').innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Cargando asignaciones...</div>';
    
    // Cargar asignaciones via fetch
    fetch(`/admin/cupones/${couponId}/asignaciones`)
        .then(response => response.json())
        .then(data => {
            if (data.assignments && data.assignments.length > 0) {
                let html = '<div class="space-y-3">';
                data.assignments.forEach(assignment => {
                    let estadoClass = assignment.estado === 'disponible' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    let estadoIcon = assignment.estado === 'disponible' ? 'fa-check-circle' : 'fa-times-circle';
                    
                    html += `
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">${assignment.nombre_cliente}</h4>
                                    <p class="text-sm text-gray-600">${assignment.email}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-clock mr-1"></i>
                                        Asignado: ${new Date(assignment.created_at).toLocaleDateString('es-ES')}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${estadoClass}">
                                        <i class="fas ${estadoIcon} mr-1"></i>
                                        ${assignment.estado === 'disponible' ? 'Disponible' : 'Usado'}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-qrcode mr-1"></i>
                                        ${assignment.codigo_qr}
                                    </p>
                                </div>
                            </div>
                            ${assignment.fecha_redencion ? `
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <p class="text-xs text-gray-600">
                                        <i class="fas fa-store mr-1"></i>
                                        Usado en: ${assignment.sucursal_redencion || 'N/A'} - ${new Date(assignment.fecha_redencion).toLocaleDateString('es-ES')}
                                    </p>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });
                html += '</div>';
                document.getElementById('assignmentsList').innerHTML = html;
            } else {
                document.getElementById('assignmentsList').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-users text-gray-300 text-4xl mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Sin asignaciones</h3>
                        <p class="text-gray-500">Este cupón aún no ha sido asignado a ningún cliente</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('assignmentsList').innerHTML = `
                <div class="text-center py-4 text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error al cargar las asignaciones
                </div>
            `;
        });
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function confirmDelete(couponId, couponName) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteCouponName').textContent = couponName;
    document.getElementById('deleteForm').action = `/admin/cupones/${couponId}`;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<style>
.zarza-bg { background-color: #b51a8a; }
</style>
@endsection