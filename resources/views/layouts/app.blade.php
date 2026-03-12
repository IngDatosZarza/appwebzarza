<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'La Zarza Contigo - Sistema de Puntos de Fidelidad')</title>
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
<body class="bg-gray-50 font-sans antialiased flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="navbar-gradient shadow-lg" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo + links de escritorio -->
                <div class="flex items-center min-w-0">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-10 w-auto mr-2">
                        <h1 class="text-white text-lg font-bold whitespace-nowrap">La Zarza Contigo</h1>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:items-center md:space-x-1">
                        <a href="{{ route('dashboard') }}" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('catalog.index') }}" class="text-white hover:text-pink-200 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            <i class="fas fa-book mr-1"></i> Catálogo
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

                <!-- Área derecha: puntos + usuario (escritorio) + hamburguesa (móvil) -->
                <div class="flex items-center space-x-2">
                    @if(Session::get('user_authenticated', false))
                        <!-- Puntos (solo escritorio) -->
                        <div class="hidden md:flex items-center text-white text-sm mr-1">
                            <i class="fas fa-coins text-pink-200 mr-1"></i>
                            <span class="font-semibold">{{ Session::get('user_puntos', 0) }} pts</span>
                        </div>
                        <!-- Menú de usuario (escritorio) -->
                        <div class="hidden md:block relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center text-white hover:text-pink-200 focus:outline-none">
                                <i class="fas fa-user-circle text-xl mr-1"></i>
                                <span>{{ Session::get('user_nombre', 'Usuario') }}</span>
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
                    @else
                        <!-- Botones login/registro (solo escritorio) -->
                        <div class="hidden md:flex items-center space-x-2">
                            <a href="/login" class="text-white hover:text-pink-200 px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                <i class="fas fa-sign-in-alt mr-1"></i> Iniciar Sesión
                            </a>
                            <a href="/register" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors backdrop-blur-sm">
                                <i class="fas fa-user-plus mr-1"></i> Registrarse
                            </a>
                        </div>
                    @endif

                    <!-- Botón hamburguesa (solo móvil) -->
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-white hover:text-pink-200 hover:bg-white hover:bg-opacity-10 focus:outline-none transition-colors" aria-label="Abrir menú">
                        <i x-show="!mobileOpen" class="fas fa-bars text-xl"></i>
                        <i x-show="mobileOpen" class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menú móvil desplegable -->
        <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden border-t border-white border-opacity-20">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-home w-5 mr-2"></i> Dashboard
                </a>
                <a href="{{ route('catalog.index') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                    <i class="fas fa-book w-5 mr-2"></i> Catálogo
                </a>
                @if(Session::get('user_authenticated', false))
                    <a href="{{ route('tickets.index') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                        <i class="fas fa-receipt w-5 mr-2"></i> Tickets
                    </a>
                    <a href="{{ route('purchases.index') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                        <i class="fas fa-shopping-cart w-5 mr-2"></i> Compras
                    </a>
                    <a href="{{ route('coupons.index') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                        <i class="fas fa-ticket-alt w-5 mr-2"></i> Cupones
                    </a>
                    @if(Session::get('user_rol') === 'admin')
                        <div class="border-t border-white border-opacity-20 pt-2 mt-2">
                            <p class="px-3 py-1 text-pink-200 text-xs font-semibold uppercase tracking-wide">Administración</p>
                            <a href="/admin/points" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                                <i class="fas fa-coins w-5 mr-2"></i> Gestión Puntos
                            </a>
                            <a href="{{ route('admin.coupons.index') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                                <i class="fas fa-ticket-alt w-5 mr-2"></i> Gestión Cupones
                            </a>
                            <a href="{{ route('admin.transactions') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                                <i class="fas fa-exchange-alt w-5 mr-2"></i> Transacciones
                            </a>
                            <a href="{{ route('admin.clients.create') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                                <i class="fas fa-user-plus w-5 mr-2"></i> Registrar Cliente
                            </a>
                        </div>
                    @endif
                    <!-- Info usuario en móvil -->
                    <div class="border-t border-white border-opacity-20 pt-2 mt-2">
                        <div class="px-3 py-2 text-pink-100 text-sm">
                            <div class="font-medium">{{ Session::get('user_nombre', 'Usuario') }} {{ Session::get('user_apellido', '') }}</div>
                            <div class="text-xs opacity-75">{{ Session::get('user_email', '') }}</div>
                            <div class="mt-1"><i class="fas fa-coins mr-1"></i> {{ Session::get('user_puntos', 0) }} puntos</div>
                        </div>
                        <a href="{{ route('profile.show') }}" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                            <i class="fas fa-user w-5 mr-2"></i> Mi Perfil
                        </a>
                        <a href="/points/history" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                            <i class="fas fa-history w-5 mr-2"></i> Historial de Puntos
                        </a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="flex items-center w-full text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                                <i class="fas fa-sign-out-alt w-5 mr-2"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-white border-opacity-20 pt-2 mt-2 space-y-1">
                        <a href="/login" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                            <i class="fas fa-sign-in-alt w-5 mr-2"></i> Iniciar Sesión
                        </a>
                        <a href="/register" @click="mobileOpen = false" class="flex items-center text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium transition-colors">
                            <i class="fas fa-user-plus w-5 mr-2"></i> Registrarse
                        </a>
                    </div>
                @endif
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
    <main class="flex-grow max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-gradient text-white">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">
                        <i class="fas fa-gem text-pink-200 mr-2"></i>
                        La Zarza Contigo
                    </h3>
                    <p class="text-gray-200">Sistema de puntos de fidelidad para recompensar a nuestros clientes más valiosos.</p>
                </div>
                <div>
                    <h4 class="text-md font-semibold mb-4">Enlaces Rápidos</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('dashboard') }}" class="text-gray-200 hover:text-pink-200 transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('coupons.index') }}" class="text-gray-200 hover:text-pink-200 transition-colors">Cupones</a></li>
                        <li><a href="{{ route('branches.index') }}" class="text-gray-200 hover:text-pink-200 transition-colors">Sucursales</a></li>                        <li><a href="https://lazarza.com.mx/aviso-de-privacidad" target="_blank" rel="noopener noreferrer" class="text-gray-200 hover:text-pink-200 transition-colors">Aviso de Privacidad</a></li>                    </ul>
                </div>
                <div>
                    <h4 class="text-md font-semibold mb-4">Contacto</h4>
                    <p class="text-gray-200">
                        <i class="fas fa-envelope mr-2"></i>
                        info@lazarza.com.mx
                    </p>
                    <p class="text-gray-200 mt-2">
                        <i class="fas fa-phone mr-2"></i>
                        +1 (555) 123-4567
                    </p>
                    <div class="mt-4">
                        <h5 class="text-sm font-semibold mb-3">Síguenos en Redes Sociales</h5>
                        <div class="flex space-x-3">
                            <a href="https://www.facebook.com/PasteleriasLaZarza/?locale=es_ES" target="_blank" rel="noopener noreferrer" class="text-gray-200 hover:text-pink-200 transition-colors transform hover:scale-110 duration-200" title="Facebook">
                                <i class="fab fa-facebook text-2xl"></i>
                            </a>
                            <a href="https://www.instagram.com/pastelerias_lazarza/?hl=es" target="_blank" rel="noopener noreferrer" class="text-gray-200 hover:text-pink-200 transition-colors transform hover:scale-110 duration-200" title="Instagram">
                                <i class="fab fa-instagram text-2xl"></i>
                            </a>
                            <a href="https://www.youtube.com/@pastelerias_lazarza/videos" target="_blank" rel="noopener noreferrer" class="text-gray-200 hover:text-pink-200 transition-colors transform hover:scale-110 duration-200" title="YouTube">
                                <i class="fab fa-youtube text-2xl"></i>
                            </a>
                            <a href="https://www.tiktok.com/@pasteleriaslazarza" target="_blank" rel="noopener noreferrer" class="text-gray-200 hover:text-pink-200 transition-colors transform hover:scale-110 duration-200" title="TikTok">
                                <i class="fab fa-tiktok text-2xl"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?phone=5212223592131" target="_blank" rel="noopener noreferrer" class="text-gray-200 hover:text-pink-200 transition-colors transform hover:scale-110 duration-200" title="WhatsApp">
                                <i class="fab fa-whatsapp text-2xl"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-purple-400 border-opacity-30 mt-8 pt-6 text-center text-gray-200">
                <p>&copy; {{ date('Y') }} La Zarza Contigo. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>