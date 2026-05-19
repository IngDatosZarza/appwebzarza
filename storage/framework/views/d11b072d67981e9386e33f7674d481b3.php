

<?php $__env->startSection('title', 'Detalle Promoción - ' . $promocion->nombre); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">

    
    <div class="mb-6">
        <a href="<?php echo e(route('admin.promos-oppen.index')); ?>" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Promociones Oppen
        </a>
    </div>

    
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-mono font-bold bg-purple-100 text-purple-700">
                        <?php echo e($promocion->oppen_code); ?>

                    </span>
                    <?php if($promocion->activo): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-eye mr-1"></i>Activa
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-eye-slash mr-1"></i>Inactiva
                        </span>
                    <?php endif; ?>
                    <?php if($promocion->estaDisponibleAhora()): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                            Disponible ahora
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="text-3xl font-bold text-gray-800"><?php echo e($promocion->nombre); ?></h1>
                <?php if($promocion->descripcion_limpia): ?>
                    <p class="text-gray-600 mt-2"><?php echo e($promocion->descripcion_limpia); ?></p>
                <?php endif; ?>
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                    <?php echo e($promocion->resumen_accion); ?>

                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                    Información General
                </h2>
            </div>
            <div class="p-6">
                <dl class="space-y-4">
                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Código Oppen</dt>
                        <dd class="text-sm text-gray-900 font-mono font-bold"><?php echo e($promocion->oppen_code); ?></dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="text-sm text-gray-900"><?php echo e($promocion->nombre); ?></dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Vigencia</dt>
                        <dd class="text-sm text-gray-900">
                            <?php echo e($promocion->fecha_inicio->format('d/m/Y')); ?> — <?php echo e($promocion->fecha_fin->format('d/m/Y')); ?>

                        </dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Horario</dt>
                        <dd class="text-sm text-gray-900"><?php echo e($promocion->horario_texto); ?></dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Combinable</dt>
                        <dd class="text-sm">
                            <?php if($promocion->combinable): ?>
                                <span class="text-green-600"><i class="fas fa-check mr-1"></i>Sí</span>
                            <?php else: ?>
                                <span class="text-red-500"><i class="fas fa-times mr-1"></i>No</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-2">Días habilitados</dt>
                        <dd>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                    $diasMap = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
                                    $diasPromo = $promocion->dias_semana ?? [];
                                ?>
                                <?php $__currentLoopData = $diasMap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $en => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo e(!empty($diasPromo[$en]) ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400 line-through'); ?>">
                                        <?php echo e($label); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        
        <div class="space-y-8">
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                        Acciones de la Promoción
                    </h2>
                </div>
                <div class="p-6">
                    <?php if(!empty($promocion->acciones) && is_array($promocion->acciones)): ?>
                        <?php $__currentLoopData = $promocion->acciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-purple-50 rounded-lg p-4 mb-3 last:mb-0">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <?php if(!empty($accion['type'])): ?>
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-purple-200 text-purple-800"><?php echo e($accion['type']); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($accion['subtype'])): ?>
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-pink-200 text-pink-800"><?php echo e($accion['subtype']); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($accion['label'])): ?>
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-green-200 text-green-800"><?php echo e($accion['label']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <dl class="grid grid-cols-2 gap-2 text-xs">
                                    <?php if(!empty($accion['perEach'])): ?>
                                        <div>
                                            <dt class="text-gray-500">Compra</dt>
                                            <dd class="font-bold text-gray-800"><?php echo e($accion['perEach']); ?> unidades</dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!empty($accion['freeUnits'])): ?>
                                        <div>
                                            <dt class="text-gray-500">Gratis</dt>
                                            <dd class="font-bold text-gray-800"><?php echo e($accion['freeUnits']); ?> unidades</dd>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!empty($accion['applyTo'])): ?>
                                        <div>
                                            <dt class="text-gray-500">Aplica a</dt>
                                            <dd class="font-bold text-gray-800"><?php echo e($accion['applyTo']); ?></dd>
                                        </div>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-gray-400 text-sm">Sin acciones definidas</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        Condiciones
                    </h2>
                </div>
                <div class="p-6">
                    <?php if(!empty($promocion->condiciones)): ?>
                        <?php $cond = $promocion->condiciones; ?>
                        <?php if(isset($cond['logicalOperator'])): ?>
                            <div class="mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    Operador: <?php echo e(ucfirst($cond['logicalOperator'])); ?>

                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($cond['children'])): ?>
                            <?php $__currentLoopData = $cond['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!empty($child['query'])): ?>
                                    <div class="bg-blue-50 rounded-lg p-3 mb-2 last:mb-0 text-sm">
                                        <span class="font-bold text-blue-800"><?php echo e($child['query']['rule'] ?? '—'); ?></span>
                                        <span class="text-gray-500 mx-1"><?php echo e($child['query']['operator'] ?? ''); ?></span>
                                        <span class="font-mono text-blue-600">"<?php echo e($child['query']['value'] ?? ''); ?>"</span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-400 text-sm">Sin condiciones definidas</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-sync-alt text-green-500 mr-2"></i>
                Datos de Sincronización
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Última Sincronización</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1">
                        <?php echo e($promocion->ultima_sincronizacion ? $promocion->ultima_sincronizacion->format('d/m/Y H:i:s') : 'Nunca'); ?>

                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Creado en BD</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1"><?php echo e($promocion->created_at->format('d/m/Y H:i:s')); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Última actualización BD</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1"><?php echo e($promocion->updated_at->format('d/m/Y H:i:s')); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">ID Interno Oppen</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1"><?php echo e($promocion->datos_raw['internalId'] ?? '—'); ?></dd>
                </div>
            </div>

            
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-code"></i>
                    <span>Datos crudos de la API (JSON)</span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-xs"></i>
                </button>
                <div x-show="open" x-transition class="mt-3">
                    <pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto text-xs leading-relaxed max-h-96 overflow-y-auto"><?php echo e(json_encode($promocion->datos_raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
                </div>
            </div>
        </div>
    </div>

    
    <?php if($promocion->descripcion): ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-align-left text-gray-500 mr-2"></i>
                    Comunicación (HTML original)
                </h2>
            </div>
            <div class="p-6">
                <div class="prose max-w-none text-sm"><?php echo $promocion->descripcion; ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/admin/promos-oppen/show.blade.php ENDPATH**/ ?>