<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - Sistema de Puntos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Error Icon -->
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            
            <!-- Error Message -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Error del Servidor</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-red-800"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php else: ?>
                <p class="text-gray-600 mb-6">
                    Ha ocurrido un error interno en el servidor. Por favor, inténtalo de nuevo más tarde.
                </p>
            <?php endif; ?>
            
            <!-- Actions -->
            <div class="space-y-3">
                <a href="/" 
                   class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors duration-200 inline-block">
                    <i class="fas fa-home mr-2"></i>
                    Volver al Inicio
                </a>
                
                <button onclick="history.back()" 
                        class="w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver Atrás
                </button>
            </div>
        </div>
    </div>
</body>
</html>