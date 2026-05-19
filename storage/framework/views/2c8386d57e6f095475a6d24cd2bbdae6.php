

<?php $__env->startSection('title', 'Promociones Oppen - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">

    
    <?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?php echo e(session('success')); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">&times;</button>
        </div>
    <?php endif; ?>
    <?php if(session('warning')): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span><?php echo e(session('warning')); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-yellow-700 hover:text-yellow-900">&times;</button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between" role="alert">
            <div class="flex items-center">
                <i class="fas fa-times-circle mr-2"></i>
                <span><?php echo e(session('error')); ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">&times;</button>
        </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-tags text-purple-600 mr-3"></i>
                    Promociones Oppen
                </h1>
                <p class="text-gray-600 mt-2">Promociones sincronizadas automáticamente desde el sistema Oppen POS</p>
            </div>
            <div class="flex items-center gap-3">
                
                <div class="text-right text-sm text-gray-500 hidden md:block">
                    <div class="font-medium">Última sincronización</div>
                    <?php if($stats['ultima_sync']): ?>
                        <div class="text-gray-700">
                            <?php echo e(\Carbon\Carbon::parse($stats['ultima_sync'])->format('d/m/Y H:i')); ?>

                        </div>
                        <div class="text-xs <?php echo e(\Carbon\Carbon::parse($stats['ultima_sync'])->diffInHours(now()) > 2 ? 'text-red-500' : 'text-green-500'); ?>">
                            <?php echo e(\Carbon\Carbon::parse($stats['ultima_sync'])->diffForHumans()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-yellow-600">Nunca</div>
                    <?php endif; ?>
                </div>

                
                <form action="<?php echo e(route('admin.promos-oppen.sync')); ?>" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fas fa-spinner fa-spin mr-2\'></i>Sincronizando...';">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 rounded-lg transition-colors duration-200 inline-flex items-center font-medium">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Sincronizar Ahora
                    </button>
                </form>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Total</h3>
                    <p class="text-2xl font-bold text-purple-600"><?php echo e($stats['total']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Activas</h3>
                    <p class="text-2xl font-bold text-green-600"><?php echo e($stats['activas']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Vigentes hoy</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['vigentes']); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-gray-100 text-gray-500">
                    <i class="fas fa-ban text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-700">Inactivas</h3>
                    <p class="text-2xl font-bold text-gray-500"><?php echo e($stats['inactivas']); ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-5 mb-8">
        <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-purple-500 mt-1"></i>
            <div class="text-sm text-gray-700">
                <p class="font-semibold text-purple-700 mb-1">Sincronización automática</p>
                <p>Las promociones se sincronizan automáticamente <strong>cada hora</strong> desde el endpoint
                    <code class="bg-white px-2 py-0.5 rounded text-xs font-mono text-purple-600">PromotionRecord</code> de la API Oppen.
                    Las promociones se muestran en la sección <strong>Promociones</strong> del sitio público para los clientes.</p>
                <p class="mt-1">Los clientes presentan su <strong>código QR</strong> en el punto de venta y el cajero aplica la promoción directamente en Oppen.</p>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-list mr-2"></i>
                Promociones Sincronizadas
            </h2>
        </div>

        <?php if($promociones->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promoción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vigencia</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Días</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Sync</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $promociones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-purple-100 text-purple-700">
                                        <?php echo e($promo->oppen_code); ?>

                                    </span>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($promo->nombre); ?></div>
                                    <?php if($promo->descripcion_limpia): ?>
                                        <div class="text-xs text-gray-500 mt-1"><?php echo e(Str::limit($promo->descripcion_limpia, 80)); ?></div>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-700">
                                        <?php echo e($promo->resumen_accion); ?>

                                    </span>
                                    <?php if($promo->combinable): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-600 mt-1" title="Combinable con otras promos">
                                            <i class="fas fa-layer-group mr-1"></i>Comb.
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-900"><?php echo e($promo->fecha_inicio->format('d/m/Y')); ?></div>
                                    <div class="text-gray-500"><?php echo e($promo->fecha_fin->format('d/m/Y')); ?></div>
                                    <?php
                                        $hoy = now()->startOfDay();
                                        if ($promo->fecha_fin < $hoy) {
                                            $estadoVig = 'vencida';
                                        } elseif ($promo->fecha_inicio > $hoy) {
                                            $estadoVig = 'futura';
                                        } else {
                                            $estadoVig = 'vigente';
                                        }
                                    ?>
                                    <?php if($estadoVig === 'vigente'): ?>
                                        <span class="inline-flex items-center text-xs text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i>Vigente</span>
                                    <?php elseif($estadoVig === 'vencida'): ?>
                                        <span class="inline-flex items-center text-xs text-red-500 mt-1"><i class="fas fa-times-circle mr-1"></i>Vencida</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center text-xs text-blue-500 mt-1"><i class="fas fa-clock mr-1"></i>Futura</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-0.5">
                                        <?php
                                            $diasMap = ['Monday'=>'L','Tuesday'=>'M','Wednesday'=>'X','Thursday'=>'J','Friday'=>'V','Saturday'=>'S','Sunday'=>'D'];
                                            $diasPromo = $promo->dias_semana ?? [];
                                        ?>
                                        <?php $__currentLoopData = $diasMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $en => $abbr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="w-6 h-6 flex items-center justify-center rounded text-xs font-bold <?php echo e(!empty($diasPromo[$en]) ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-400'); ?>"><?php echo e($abbr); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1"><?php echo e($promo->horario_texto); ?></div>
                                </td>

                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($promo->activo): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-eye mr-1"></i>Activa
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-eye-slash mr-1"></i>Inactiva
                                        </span>
                                    <?php endif; ?>
                                    <?php if($promo->estaDisponibleAhora()): ?>
                                        <div class="flex items-center gap-1 text-xs text-green-600 mt-1">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                            Disponible ahora
                                        </div>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if($promo->ultima_sincronizacion): ?>
                                        <div class="text-gray-700"><?php echo e($promo->ultima_sincronizacion->format('d/m/Y')); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($promo->ultima_sincronizacion->format('H:i:s')); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo e($promo->ultima_sincronizacion->diffForHumans()); ?></div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">—</span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?php echo e(route('admin.promos-oppen.show', $promo->id)); ?>"
                                       class="text-purple-600 hover:text-purple-900 transition-colors"
                                       title="Ver detalle API">
                                        <i class="fas fa-search-plus"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-tags text-5xl mb-4 opacity-30"></i>
                <h3 class="text-xl font-semibold mb-2">No hay promociones sincronizadas</h3>
                <p class="mb-4">Haz clic en <strong>Sincronizar Ahora</strong> para obtener las promociones desde Oppen.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/admin/promos-oppen/index.blade.php ENDPATH**/ ?>