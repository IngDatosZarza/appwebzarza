<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido - La Zarza Contigo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-green-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-4">
        <div class="bg-white rounded-2xl shadow-xl p-8">

            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">La Zarza Contigo</h1>
                <p class="text-gray-500 text-sm mt-1">Entorno de Revisión QA</p>
            </div>

            {{-- Mensaje --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 text-yellow-800">
                    <i class="fas fa-lock text-sm"></i>
                    <span class="text-sm font-medium">Acceso restringido al equipo de revisión</span>
                </div>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2 text-red-700">
                        <i class="fas fa-exclamation-circle text-sm"></i>
                        <span class="text-sm">Contraseña incorrecta. Inténtalo de nuevo.</span>
                    </div>
                </div>
            @endif

            {{-- Formulario --}}
            <form method="POST" action="{{ route('qa.access.verify') }}">
                @csrf
                <input type="hidden" name="redirect" value="{{ $redirect }}">

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-key mr-1"></i> Contraseña de Acceso QA
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"
                        placeholder="Ingresa la contraseña de acceso"
                        autofocus
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-sign-in-alt"></i>
                    Ingresar al entorno QA
                </button>
            </form>

            {{-- Footer --}}
            <p class="text-center text-xs text-gray-400 mt-6">
                Solo personal autorizado del equipo de revisión
            </p>
        </div>
    </div>
</body>
</html>
