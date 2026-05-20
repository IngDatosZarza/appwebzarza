<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error') - La Zarza Contigo</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Mercurius';
            src: url('/fonts/MercuriusMedium.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        .font-mercurius { font-family: 'Mercurius', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%); }
        .btn-primary {
            background: linear-gradient(135deg, #b51a8a 0%, #d63a9e 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #9e1577 0%, #c0348b 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(181, 26, 138, 0.4);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16">
                <a href="/" class="flex items-center">
                    <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-10 w-auto mr-2">
                    <h1 class="text-white text-lg font-bold font-mercurius">La Zarza Contigo</h1>
                </a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full">
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 text-center py-4 text-sm">
        <p>&copy; {{ date('Y') }} La Zarza Contigo. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
