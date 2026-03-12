<?php 
// Incluir helpers CSRF
$helperPath = realpath(__DIR__ . '/../../../app/Helpers/csrf_helper.php');
if ($helperPath && file_exists($helperPath)) {
    require_once $helperPath;
} else {
    // Fallback: crear funciones básicas si no se encuentra el helper
    if (!function_exists('csrf_token')) {
        function csrf_token() {
            if (session_status() == PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION['_token'])) {
                $_SESSION['_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['_token'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - La Zarza Contigo</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <!-- Tailwind CSS CDN - Solo para desarrollo, cambiar a build para producción -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        .zarza-gradient { background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%); }
        .zarza-gradient-reverse { background: linear-gradient(135deg, #71398d 0%, #b51a8a 100%); }
        .form-input:focus { border-color: #b51a8a; box-shadow: 0 0 0 3px rgba(181, 26, 138, 0.1); }
        .zarza-text { color: #b51a8a; }
        .zarza-bg { background-color: #b51a8a; }
        .zarza-bg-hover:hover { background-color: #71398d; }
        .zarza-border { border-color: #b51a8a; }
        .zarza-ring:focus { --tw-ring-color: #b51a8a; }
    </style>
</head>
<body class="zarza-gradient min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="text-center">
                <div class="mx-auto mb-6">
                    <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-20 w-auto mx-auto mb-4 drop-shadow-lg">
                </div>
                <h2 class="text-3xl font-extrabold text-white">
                    Iniciar Sesión
                </h2>
                <p class="mt-2 text-sm text-pink-100">
                    Accede a tu cuenta de La Zarza Contigo
                </p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-xl p-8" x-data="{ showPassword: false }">
            <form method="POST" action="/login" class="space-y-6">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-gray-400 mr-2"></i>
                        Correo Electrónico
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email"
                        required 
                        class="form-input appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 focus:z-10 sm:text-sm"
                        placeholder="tu@email.com"
                        value="{{ old('email', '') }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>
                        Contraseña
                    </label>
                    <div class="relative">
                        <input 
                            id="password" 
                            name="password" 
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            required 
                            class="form-input appearance-none relative block w-full px-3 py-3 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 focus:z-10 sm:text-sm"
                            placeholder="Tu contraseña"
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            @click="showPassword = !showPassword"
                        >
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember-me" 
                            name="remember-me" 
                            type="checkbox" 
                            class="h-4 w-4 zarza-text zarza-ring border-gray-300 rounded"
                        >
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Recordarme
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium zarza-text hover:text-pink-600">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white zarza-bg zarza-bg-hover focus:outline-none focus:ring-2 focus:ring-offset-2 zarza-ring transition-colors duration-200"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-pink-200 group-hover:text-pink-100"></i>
                        </span>
                        Iniciar Sesión
                    </button>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        ¿No tienes una cuenta?
                        <a href="/register" class="font-medium zarza-text hover:text-pink-600">
                            Regístrate aquí
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Demo Credentials -->
        <div class="bg-white bg-opacity-20 rounded-lg p-4 text-white text-sm">
            <h3 class="font-semibold mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Cuentas de Prueba:
            </h3>
            <div class="space-y-1">
                <p><strong>Cliente:</strong> cliente@test.com / password</p>
                <p><strong>Admin:</strong> admin@test.com / password</p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="/" class="inline-flex items-center text-white hover:text-pink-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al Inicio
            </a>
        </div>
    </div>

    <!-- Flash Messages usando Laravel Session -->
    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-4 text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-4 text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
</body>
</html>