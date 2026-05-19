<?php $__env->startSection('title', 'Mi Perfil - La Zarza Contigo'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Encabezado -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mi Perfil</h1>
                    <p class="text-gray-600">Administra tu información personal y configuración de cuenta</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Información de la cuenta -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Información de la Cuenta
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Nombre Completo</p>
                            <p class="font-medium text-gray-900"><?php echo e($user['nombres']); ?> <?php echo e($user['apellido_paterno']); ?> <?php echo e($user['apellido_materno']); ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium text-gray-900"><?php echo e($user['email']); ?></p>
                        </div>
                                                
                        <div>
                            <p class="text-sm text-gray-600">Miembro desde</p>
                            <p class="font-medium text-gray-900"><?php echo e(date('d/m/Y', strtotime($user['created_at']))); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de edición -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">
                        <i class="fas fa-edit text-green-500 mr-2"></i>
                        Editar Información Personal
                    </h3>

                    <form method="POST" action="<?php echo e(route('profile.update')); ?>" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <!-- Información Personal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="nombres" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>
                                    Nombres *
                                </label>
                                <input type="text" 
                                       id="nombres" 
                                       name="nombres" 
                                       value="<?php echo e(old('nombres', $user['nombres'])); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['nombres'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                                <?php $__errorArgs = ['nombres'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>
                                    Apellido Paterno *
                                </label>
                                <input type="text" 
                                       id="apellido_paterno" 
                                       name="apellido_paterno" 
                                       value="<?php echo e(old('apellido_paterno', $user['apellido_paterno'])); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['apellido_paterno'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                                <?php $__errorArgs = ['apellido_paterno'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>
                                    Apellido Materno
                                </label>
                                <input type="text" 
                                       id="apellido_materno" 
                                       name="apellido_materno" 
                                       value="<?php echo e(old('apellido_materno', $user['apellido_materno'])); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['apellido_materno'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['apellido_materno'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email *
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo e(old('email', $user['email'])); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       required>
                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-1"></i>
                                    Teléfono
                                </label>
                                <input type="tel" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?php echo e(old('telefono', $user['telefono'])); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['telefono'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Fecha de Nacimiento
                                </label>
                                <input type="date" 
                                       id="fecha_nacimiento" 
                                       name="fecha_nacimiento" 
                                       value="<?php echo e(old('fecha_nacimiento', $user['fecha_nacimiento'] ? date('Y-m-d', strtotime($user['fecha_nacimiento'])) : '')); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['fecha_nacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['fecha_nacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <!-- Dirección -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">
                                <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                                Dirección Principal
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="calle" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-road mr-1"></i>
                                        Calle *
                                    </label>
                                    <input type="text" 
                                           id="calle" 
                                           name="calle" 
                                           value="<?php echo e(old('calle', $direccion?->calle ?? '')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['calle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['calle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-hashtag mr-1"></i>
                                        Número *
                                    </label>
                                    <input type="text" 
                                           id="numero" 
                                           name="numero" 
                                           value="<?php echo e(old('numero', $direccion?->numero ?? '')); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['numero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['numero'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-map mr-1"></i>
                                        Estado *
                                    </label>
                                    <select id="estado" 
                                            name="estado"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="">Selecciona un estado</option>
                                    </select>
                                    <?php $__errorArgs = ['estado'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="municipio" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-city mr-1"></i>
                                        Municipio *
                                    </label>
                                    <select id="municipio" 
                                            name="municipio"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['municipio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            disabled>
                                        <option value="">Primero selecciona estado</option>
                                    </select>
                                    <?php $__errorArgs = ['municipio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="codigo_postal_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-mail-bulk mr-1"></i>
                                        Colonia / CP *
                                    </label>
                                    <select id="codigo_postal_id" 
                                            name="codigo_postal_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['codigo_postal_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            disabled>
                                        <option value="">Primero selecciona municipio</option>
                                    </select>
                                    <?php $__errorArgs = ['codigo_postal_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-home mr-1"></i>
                                        Tipo de Dirección *
                                    </label>
                                    <select id="tipo" 
                                            name="tipo"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                        <option value="casa" <?php echo e(old('tipo', $direccion?->tipo ?? 'casa') == 'casa' ? 'selected' : ''); ?>>Casa</option>
                                        <option value="trabajo" <?php echo e(old('tipo', $direccion?->tipo ?? '') == 'trabajo' ? 'selected' : ''); ?>>Trabajo</option>
                                        <option value="otro" <?php echo e(old('tipo', $direccion?->tipo ?? '') == 'otro' ? 'selected' : ''); ?>>Otro</option>
                                    </select>
                                    <?php $__errorArgs = ['tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="referencias" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Referencias
                                    </label>
                                    <input type="text" 
                                           id="referencias" 
                                           name="referencias" 
                                           value="<?php echo e(old('referencias', $direccion?->referencias ?? '')); ?>"
                                           placeholder="Ej: Entre calle A y B"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['referencias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['referencias'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Cambio de Contraseña -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">
                                <i class="fas fa-lock text-yellow-500 mr-2"></i>
                                Cambiar Contraseña (Opcional)
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nueva Contraseña
                                    </label>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Confirmar Nueva Contraseña
                                    </label>
                                    <input type="password" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="<?php echo e(route('dashboard')); ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-all transform hover:scale-105">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('styles'); ?>
<style>
    .text-gradient {
        background: linear-gradient(135deg, #ec4899, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const estadoSelect = document.getElementById('estado');
    const municipioSelect = document.getElementById('municipio');
    const coloniaSelect = document.getElementById('codigo_postal_id');
    
    // Datos precargados de la dirección
    const direccionActual = {
        estado: <?php echo json_encode(isset($direccion) ? $direccion->estado : '', 15, 512) ?>,
        municipio: <?php echo json_encode(isset($direccion) ? $direccion->municipio : '', 15, 512) ?>,
        codigo_postal_id: <?php echo json_encode(isset($direccion) ? $direccion->codigo_postal_id : null, 15, 512) ?>
    };
    
    // Cargar estados al inicio
    cargarEstados();
    
    // Event listeners
    estadoSelect.addEventListener('change', function() {
        const estado = this.value;
        if (estado) {
            cargarMunicipios(estado);
        } else {
            resetSelect(municipioSelect, 'Primero selecciona estado');
            resetSelect(coloniaSelect, 'Primero selecciona municipio');
        }
    });
    
    municipioSelect.addEventListener('change', function() {
        const municipio = this.value;
        const estado = estadoSelect.value;
        if (municipio && estado) {
            cargarColonias(estado, municipio);
        } else {
            resetSelect(coloniaSelect, 'Primero selecciona municipio');
        }
    });
    
    /**
     * Cargar estados desde la API
     */
    function cargarEstados() {
        fetch('/api/codigos-postales/estados')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    estadoSelect.innerHTML = '<option value="">Selecciona un estado</option>';
                    data.data.forEach(estado => {
                        const option = document.createElement('option');
                        option.value = estado;
                        option.textContent = estado;
                        if (estado === direccionActual.estado) {
                            option.selected = true;
                        }
                        estadoSelect.appendChild(option);
                    });
                    
                    // Si hay estado precargado, cargar municipios
                    if (direccionActual.estado) {
                        cargarMunicipios(direccionActual.estado);
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar estados:', error);
                showError('Error al cargar estados');
            });
    }
    
    /**
     * Cargar municipios según el estado seleccionado
     */
    function cargarMunicipios(estado) {
        fetch(`/api/codigos-postales/municipios?estado=${encodeURIComponent(estado)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    municipioSelect.innerHTML = '<option value="">Selecciona un municipio</option>';
                    municipioSelect.disabled = false;
                    
                    data.data.forEach(municipio => {
                        const option = document.createElement('option');
                        option.value = municipio;
                        option.textContent = municipio;
                        if (municipio === direccionActual.municipio) {
                            option.selected = true;
                        }
                        municipioSelect.appendChild(option);
                    });
                    
                    // Si hay municipio precargado, cargar colonias
                    if (direccionActual.municipio) {
                        cargarColonias(estado, direccionActual.municipio);
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar municipios:', error);
                showError('Error al cargar municipios');
            });
    }
    
    /**
     * Cargar colonias según estado y municipio
     */
    function cargarColonias(estado, municipio) {
        fetch(`/api/codigos-postales/colonias?estado=${encodeURIComponent(estado)}&municipio=${encodeURIComponent(municipio)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    coloniaSelect.innerHTML = '<option value="">Selecciona colonia / código postal</option>';
                    coloniaSelect.disabled = false;
                    
                    data.data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = `${item.colonia} (CP: ${item.codigo_postal})`;
                        if (item.id == direccionActual.codigo_postal_id) {
                            option.selected = true;
                        }
                        coloniaSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar colonias:', error);
                showError('Error al cargar colonias');
            });
    }
    
    /**
     * Resetear un select
     */
    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        select.disabled = true;
    }
    
    /**
     * Mostrar error
     */
    function showError(message) {
        console.error(message);
        // Aquí podrías agregar un toast notification si lo deseas
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/profile/show.blade.php ENDPATH**/ ?>