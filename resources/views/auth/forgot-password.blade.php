<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Olvidaste tu contraseña? - La Zarza Contigo</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @font-face {
            font-family: 'Mercurius';
            src: url('/fonts/MercuriusMedium.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        .font-mercurius { font-family: 'Mercurius', sans-serif; }
        .zarza-gradient { background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%); }
        .form-input:focus { border-color: #b51a8a; box-shadow: 0 0 0 3px rgba(181, 26, 138, 0.1); }
        .zarza-text { color: #b51a8a; }
        .zarza-bg { background-color: #b51a8a; }
        .zarza-bg-hover:hover { background-color: #71398d; }
        .zarza-ring:focus { --tw-ring-color: #b51a8a; }
    </style>
</head>
<body class="zarza-gradient min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">

        <!-- Logo & título -->
        <div class="text-center">
            <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-20 w-auto mx-auto mb-4 drop-shadow-lg">
            <h2 class="text-3xl font-extrabold text-white">¿Olvidaste tu contraseña?</h2>
            <p class="mt-2 text-sm text-pink-100">
                Ingresa tu correo y te enviaremos un enlace para restablecerla.
            </p>
        </div>

        <!-- Tarjeta del formulario -->
        <div class="bg-white rounded-lg shadow-xl p-8">

            <form method="POST" action="{{ route('password.send-link') }}" class="space-y-6">
                @csrf

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
                        class="form-input appearance-none block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none zarza-ring focus:border-pink-500 sm:text-sm"
                        placeholder="tu@email.com"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Botón enviar -->
                <button
                    type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white zarza-bg zarza-bg-hover focus:outline-none focus:ring-2 focus:ring-offset-2 zarza-ring transition-colors duration-200"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-paper-plane text-pink-200 group-hover:text-pink-100"></i>
                    </span>
                    Enviar enlace de restablecimiento
                </button>

                <!-- Volver al login -->
                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="text-sm font-medium zarza-text hover:text-pink-600">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Volver a Iniciar Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
    <!-- Flash Messages -->
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
