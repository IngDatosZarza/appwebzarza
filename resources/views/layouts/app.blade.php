<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ZarzaPoints - Sistema de Puntos de Fidelidad')</title>
    <link rel="icon" type="image/png" href="/logozarza.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        /* Colores personalizados */
        .navbar-gradient {
            background: linear-gradient(135deg, #b51a8a 0%, #d63a9e 100%);
        }
        
        .footer-gradient {
            background: linear-gradient(135deg, #71398d 0%, #8b4a9c 100%);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #b51a8a 0%, #71398d 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #b51a8a 0%, #d63a9e 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #9e1577 0%, #c0348b 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(181, 26, 138, 0.4);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="navbar-gradient shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="/logoZarza.webp" alt="ZarzaPoints" class="h-10 w-auto mr-3">
                        <h1 class="text-white text-xl font-bold">
                            ZarzaPoints
                        </h1>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        @if(Session::get('user_authenticated', false))
                            <a href="{{ route('tickets.index') }}" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-receipt mr-1"></i> Tickets
                            </a>
                            <a href="{{ route('purchases.index') }}" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-shopping-cart mr-1"></i> Compras
                            </a>
                            <a href="{{ route('coupons.index') }}" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-ticket-alt mr-1"></i> Cupones
                            </a>
                            @if(Session::get('user_rol') === 'admin')
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                                        <i class="fas fa-cog mr-1"></i> Admin
                                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                        <a href="/admin/points" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-coins mr-2"></i> Gestión Puntos
                                        </a>
                                        <a href="{{ route('admin.coupons.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-ticket-alt mr-2"></i> Gestión Cupones
                                        </a>
                                        <a href="{{ route('admin.transactions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-exchange-alt mr-2"></i> Transacciones
                                        </a>
                                        <a href="{{ route('admin.clients.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-user-plus mr-2"></i> Registrar Cliente
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    @if(Session::get('user_authenticated', false))
                        <div class="flex items-center space-x-3">
                            <div class="text-white text-sm">
                                <i class="fas fa-coins text-pink-200 mr-1"></i>
                                <span class="font-semibold">{{ Session::get('user_puntos', 0) }} pts</span>
                            </div>
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-white hover:text-pink-200 focus:outline-none">
                                    <i class="fas fa-user-circle text-xl mr-1"></i>
                                    <span class="hidden md:block">{{ Session::get('user_nombre', 'Usuario') }}</span>
                                    <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                                        <div class="font-medium">{{ Session::get('user_nombre', 'Usuario') }} {{ Session::get('user_apellido', '') }}</div>
                                        <div class="text-xs text-gray-500">{{ Session::get('user_email', '') }}</div>
                                    </div>
                                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i> Mi Perfil
                                    </a>
                                    <a href="{{ route('tickets.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-receipt mr-2"></i> Mis Tickets
                                    </a>
                                    <a href="{{ route('coupons.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-ticket-alt mr-2"></i> Mis Cupones
                                    </a>
                                    <a href="/points/history" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-history mr-2"></i> Historial de Puntos
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="/logout">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex space-x-2">
                            <a href="/login" class="text-white hover:text-pink-200 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-sign-in-alt mr-1"></i> Iniciar Sesión
                            </a>
                            <a href="/register" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors backdrop-blur-sm">
                                <i class="fas fa-user-plus mr-1"></i> Registrarse
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-gradient text-white mt-12">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-gem text-pink-200 mr-2"></i>
                        ZarzaPoints
                    </h3>
                    <p class="text-gray-200">Sistema de puntos de fidelidad para recompensar a nuestros clientes más valiosos.</p>
                </div>
                <div>
                    <h4 class="text-md font-semibold mb-4">Enlaces Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('dashboard') }}" class="text-gray-200 hover:text-pink-200 transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('coupons.index') }}" class="text-gray-200 hover:text-pink-200 transition-colors">Cupones</a></li>
                        <li><a href="#" class="text-gray-200 hover:text-pink-200 transition-colors">Sucursales</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-md font-semibold mb-4">Contacto</h4>
                    <p class="text-gray-200">
                        <i class="fas fa-envelope mr-2"></i>
                        info@zarzapoints.com
                    </p>
                    <p class="text-gray-200 mt-2">
                        <i class="fas fa-phone mr-2"></i>
                        +1 (555) 123-4567
                    </p>
                </div>
            </div>
            <div class="border-t border-purple-400 border-opacity-30 mt-8 pt-6 text-center text-gray-200">
                <p>&copy; {{ date('Y') }} ZarzaPoints. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>