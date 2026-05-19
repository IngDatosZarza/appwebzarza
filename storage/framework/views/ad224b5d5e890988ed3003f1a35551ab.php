

<?php $__env->startSection('title', 'Administradores de Sucursal'); ?>

<?php $__env->startSection('content'); ?>
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
            <a href="<?php echo e(route('admin.usuarios.create')); ?>" class="inline-flex items-center px-5 py-2.5 rounded-lg text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
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
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($administradores->where('activo', true)->count()); ?></p>
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
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($totalClientes); ?></p>
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
                    <p class="text-2xl font-bold text-gray-800"><?php echo e($sucursales->count()); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="<?php echo e(route('admin.usuarios.index')); ?>" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas</option>
                    <?php $__currentLoopData = $sucursales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sucursal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($sucursal->id); ?>" <?php echo e(request('sucursal_id') == $sucursal->id ? 'selected' : ''); ?>>
                            <?php echo e($sucursal->nombre); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                <select name="activo" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="1" <?php echo e(request('activo') === '1' ? 'selected' : ''); ?>>Activos</option>
                    <option value="0" <?php echo e(request('activo') === '0' ? 'selected' : ''); ?>>Inactivos</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="<?php echo e(route('admin.usuarios.index')); ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition-colors">
                Limpiar
            </a>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-4 pt-4 pb-3 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <span class="text-sm text-gray-500"><?php echo e($administradores->count()); ?> administradores</span>
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
                    <?php $__empty_1 = true; $__currentLoopData = $administradores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $adm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm">
                                    <?php echo e(strtoupper(substr($adm->nombres, 0, 1) . substr($adm->apellido_paterno, 0, 1))); ?>

                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($adm->nombre_completo); ?></div>
                                    <div class="text-xs text-gray-500">ID: <?php echo e($adm->id); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($adm->sucursal): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-store mr-1"></i> <?php echo e($adm->sucursal->nombre); ?>

                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900"><?php echo e($adm->email); ?></div>
                            <div class="text-xs text-gray-500"><?php echo e($adm->telefono); ?></div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?php echo e($adm->clientes_registrados_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                <?php echo e($adm->clientes_registrados_count); ?>

                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($adm->activo): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Activo
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Inactivo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            <?php echo e($adm->ultimo_acceso ? $adm->ultimo_acceso->diffForHumans() : 'Nunca'); ?>

                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="<?php echo e(route('admin.usuarios.edit', $adm->id)); ?>" class="text-indigo-600 hover:text-indigo-800" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?php echo e(route('admin.usuarios.toggle', $adm->id)); ?>" class="inline" onsubmit="return confirm('¿Estás seguro de <?php echo e($adm->activo ? 'desactivar' : 'activar'); ?> este administrador?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="<?php echo e($adm->activo ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700'); ?>" title="<?php echo e($adm->activo ? 'Desactivar' : 'Activar'); ?>">
                                        <i class="fas <?php echo e($adm->activo ? 'fa-ban' : 'fa-check'); ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo e(route('admin.usuarios.reset-password', $adm->id)); ?>" class="inline" onsubmit="return confirm('¿Generar nueva contraseña temporal para <?php echo e($adm->nombre_completo); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="text-yellow-500 hover:text-yellow-700" title="Resetear contraseña">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users-cog text-4xl text-gray-300 mb-2"></i>
                            <p>No hay administradores de sucursal registrados.</p>
                            <a href="<?php echo e(route('admin.usuarios.create')); ?>" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2 inline-block">
                                <i class="fas fa-plus mr-1"></i> Crear el primero
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
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

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/admin/usuarios/index.blade.php ENDPATH**/ ?>