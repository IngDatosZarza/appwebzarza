

<?php $__env->startSection('title', 'Mis Clientes'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-users text-blue-600 mr-2"></i>
                    Mis Clientes Registrados
                </h1>
                <p class="text-gray-500 mt-1">
                    <?php echo e($admin->sucursal ? $admin->sucursal->nombre : ''); ?> · Total: <?php echo e($totalRegistrados); ?> clientes
                </p>
            </div>
            <a href="<?php echo e(route('admin.clientes.registrar')); ?>" class="inline-flex items-center px-5 py-2.5 rounded-lg text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
                <i class="fas fa-user-plus mr-2"></i> Registrar Cliente
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="<?php echo e(route('admin.mi-sucursal.clientes')); ?>" class="flex gap-3">
            <input type="text" name="buscar" value="<?php echo e(request('buscar')); ?>" placeholder="Buscar por nombre, email o teléfono..."
                class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fas fa-search mr-1"></i> Buscar
            </button>
            <?php if(request('buscar')): ?>
                <a href="<?php echo e(route('admin.mi-sucursal.clientes')); ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
                    Limpiar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-4 pt-4 pb-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <span class="text-sm text-gray-500"><?php echo e($clientes->total()); ?> clientes</span>
            <input type="text" id="buscadorMisClientes" placeholder="Filtrar en esta página..."
                oninput="filtrarTabla('buscadorMisClientes','tablaMisClientes')"
                class="flex-1 sm:max-w-xs px-3 py-1.5 rounded-lg border border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="overflow-x-auto" style="max-height: 520px; overflow-y: auto;">
            <table id="tablaMisClientes" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Registro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?php echo e($cliente->nombre_completo); ?></div>
                            <div class="text-xs text-gray-500">RFC: <?php echo e($cliente->rfc ?? '-'); ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($cliente->email); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($cliente->telefono); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo e($cliente->created_at->format('d/m/Y H:i')); ?></td>
                        <td class="px-6 py-4">
                            <?php if($cliente->origen_registro === 'admin_sucursal'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700">
                                    <i class="fas fa-store mr-1"></i> Sucursal
                                </span>
                            <?php elseif($cliente->origen_registro === 'campana'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-purple-50 text-purple-700">
                                    <i class="fas fa-bullhorn mr-1"></i> Campaña
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-50 text-gray-600">
                                    Auto
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                            <p>No se encontraron clientes.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($clientes->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo e($clientes->withQueryString()->links()); ?>

        </div>
        <?php endif; ?>
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/admin/sucursal/clientes.blade.php ENDPATH**/ ?>