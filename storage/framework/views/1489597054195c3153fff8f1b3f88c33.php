

<?php $__env->startSection('title', 'Dashboard - Superadmin'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
            Dashboard General
        </h1>
        <p class="text-gray-500 mt-1">Resumen del sistema de fidelización</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <i class="fas fa-users-cog text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Admins Activos</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($totalAdmins); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Total Clientes</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($totalClientes); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Hoy</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($clientesHoy); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-week text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Esta Semana</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($clientesSemana); ?></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Este Mes</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($clientesMes); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sucursales breakdown -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-800 flex-1">
                <i class="fas fa-store text-indigo-600 mr-2"></i>
                Clientes por Sucursal y Administrador
            </h2>
            <input type="text" id="buscadorSucursales" placeholder="Buscar sucursal..." oninput="filtrarTabla('buscadorSucursales','tablaSucursales')"
                class="w-full sm:w-64 px-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto" style="max-height: 420px; overflow-y: auto;">
            <table id="tablaSucursales" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Clientes Registrados</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Administradores</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?php echo e($sucursal->nombre); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($sucursal->codigo); ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?php echo e($sucursal->clientes_registrados_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                <?php echo e($sucursal->clientes_registrados_count); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php $__empty_1 = true; $__currentLoopData = $sucursal->administradores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="flex items-center justify-between mb-1 last:mb-0">
                                    <span class="text-sm text-gray-700"><?php echo e($adm->nombre_completo); ?></span>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 ml-2">
                                        <?php echo e($adm->clientes_registrados_count); ?> clientes
                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-sm text-gray-400">Sin admin asignado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Últimos clientes -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-800 flex-1">
                <i class="fas fa-clock text-indigo-600 mr-2"></i>
                Últimos Clientes Registrados
            </h2>
            <input type="text" id="buscadorUltClientes" placeholder="Buscar cliente..." oninput="filtrarTabla('buscadorUltClientes','tablaUltClientes')"
                class="w-full sm:w-64 px-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto" style="max-height: 420px; overflow-y: auto;">
            <table id="tablaUltClientes" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sucursal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $ultimosClientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900"><?php echo e($cliente->nombre_completo); ?></td>
                        <td class="px-6 py-3 text-sm text-gray-600"><?php echo e($cliente->email); ?></td>
                        <td class="px-6 py-3 text-sm">
                            <?php if($cliente->registradoPorAdministrador): ?>
                                <span class="text-blue-700"><?php echo e($cliente->registradoPorAdministrador->nombre_completo); ?></span>
                            <?php elseif($cliente->origen_registro === 'autoregistro'): ?>
                                <span class="text-gray-400">Autoregistro</span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 text-sm">
                            <?php if($cliente->sucursalRegistro): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">
                                    <?php echo e($cliente->sucursalRegistro->nombre); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500"><?php echo e($cliente->created_at->format('d/m/Y H:i')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function filtrarTabla(inputId, tablaId) {
    const filtro = document.getElementById(inputId).value.toLowerCase();
    const filas = document.querySelectorAll('#' + tablaId + ' tbody tr');
    filas.forEach(fila => {
        fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>