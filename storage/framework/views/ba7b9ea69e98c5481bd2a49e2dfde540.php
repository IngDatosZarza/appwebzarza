<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Admin - Iniciar Sesión | La Zarza Contigo</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Mercurius';
            src: url('/fonts/MercuriusMedium.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        .font-mercurius { font-family: 'Mercurius', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">

    <div class="w-full max-w-md px-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-16 w-auto mx-auto mb-3">
            <h1 class="text-white text-2xl font-bold font-mercurius">Panel de Administración</h1>
            <p class="text-gray-400 text-sm mt-1">Acceso restringido a personal autorizado</p>
        </div>

        <!-- Card de login -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php if(session('success')): ?>
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    <i class="fas fa-check-circle mr-1"></i> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.login.post')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope mr-1 text-gray-400"></i> Correo electrónico
                    </label>
                    <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required autofocus
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="tu@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock mr-1 text-gray-400"></i> Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="eye-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Recordar sesión</label>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-lg text-white font-semibold transition-all duration-200 hover:shadow-lg"
                    style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
                    <i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión
                </button>
            </form>
        </div>

        <div class="text-center mt-6">
            <p class="text-gray-500 text-xs">
                <i class="fas fa-shield-alt mr-1"></i>
                Conexión segura · Acceso monitoreado
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\appwebzarza\resources\views/admin/auth/login.blade.php ENDPATH**/ ?>