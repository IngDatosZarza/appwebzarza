<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - La Zarza Contigo')</title>
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
        .admin-navbar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }
        .admin-sidebar { background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased flex flex-col min-h-screen">
    @php $admin = Auth::guard('admin')->user(); @endphp

    <!-- Top Navbar -->
    <nav class="admin-navbar shadow-lg sticky top-0 z-40" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center min-w-0">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="/logoZarza.webp" alt="La Zarza Contigo" class="h-10 w-auto mr-2">
                        <h1 class="text-white text-lg font-bold whitespace-nowrap font-mercurius">Administración</h1>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:items-center md:space-x-1">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-white bg-white bg-opacity-10' : '' }}">
                            <i class="fas fa-chart-line mr-1"></i> Dashboard
                        </a>

                        @if($admin->esSuperadmin())
                            <a href="{{ route('admin.usuarios.index') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.usuarios.*') ? 'text-white bg-white bg-opacity-10' : '' }}">
                                <i class="fas fa-users-cog mr-1"></i> Administradores
                            </a>
                            <a href="{{ route('admin.clientes.index') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.clientes.*') ? 'text-white bg-white bg-opacity-10' : '' }}">
                                <i class="fas fa-users mr-1"></i> Clientes
                            </a>
                        @endif

                        @if($admin->esAdminSucursal())
                            <a href="{{ route('admin.mi-sucursal.clientes') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.mi-sucursal.*') ? 'text-white bg-white bg-opacity-10' : '' }}">
                                <i class="fas fa-users mr-1"></i> Mis Clientes
                            </a>
                        @endif

                        <a href="{{ route('admin.clientes.registrar') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.clientes.registrar*') ? 'text-white bg-white bg-opacity-10' : '' }}">
                            <i class="fas fa-user-plus mr-1"></i> Registrar Cliente
                        </a>

                        <a href="{{ route('admin.promos-oppen.index') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.promos-oppen.*') ? 'text-white bg-white bg-opacity-10' : '' }}">
                            <i class="fas fa-tags mr-1"></i> Promociones
                        </a>
                    </div>
                </div>

                <!-- User area -->
                <div class="flex items-center space-x-2">
                    <div class="hidden md:block relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-gray-300 hover:text-white focus:outline-none">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $admin->esSuperadmin() ? 'bg-yellow-500 text-yellow-900' : 'bg-blue-500 text-white' }}">
                                {{ strtoupper(substr($admin->nombres, 0, 1)) }}
                            </div>
                            <div class="ml-2 text-left hidden lg:block">
                                <div class="text-sm text-white">{{ $admin->nombres }}</div>
                                <div class="text-xs {{ $admin->esSuperadmin() ? 'text-yellow-400' : 'text-blue-400' }}">
                                    {{ $admin->esSuperadmin() ? 'Superadmin' : 'Admin Sucursal' }}
                                </div>
                            </div>
                            <i class="fas fa-chevron-down ml-2 text-xs text-gray-400"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="font-medium text-gray-800">{{ $admin->nombre_completo }}</div>
                                <div class="text-xs text-gray-500">{{ $admin->email }}</div>
                                @if($admin->esAdminSucursal() && $admin->sucursal)
                                    <div class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-store mr-1"></i> {{ $admin->sucursal->nombre }}
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile hamburger -->
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 focus:outline-none transition-colors">
                        <i x-show="!mobileOpen" class="fas fa-bars text-xl"></i>
                        <i x-show="mobileOpen" class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileOpen" x-transition class="md:hidden border-t border-white border-opacity-10">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-chart-line w-5 mr-2"></i> Dashboard
                </a>
                @if($admin->esSuperadmin())
                    <a href="{{ route('admin.usuarios.index') }}" class="flex items-center text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-users-cog w-5 mr-2"></i> Administradores
                    </a>
                    <a href="{{ route('admin.clientes.index') }}" class="flex items-center text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-users w-5 mr-2"></i> Todos los Clientes
                    </a>
                @endif
                @if($admin->esAdminSucursal())
                    <a href="{{ route('admin.mi-sucursal.clientes') }}" class="flex items-center text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                        <i class="fas fa-users w-5 mr-2"></i> Mis Clientes
                    </a>
                @endif
                <a href="{{ route('admin.clientes.registrar') }}" class="flex items-center text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-user-plus w-5 mr-2"></i> Registrar Cliente
                </a>
                <a href="{{ route('admin.promos-oppen.index') }}" class="flex items-center text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-tags w-5 mr-2"></i> Promociones
                </a>

                <div class="border-t border-white border-opacity-10 pt-2 mt-2">
                    <div class="px-3 py-2 text-gray-400 text-sm">
                        <div class="font-medium text-white">{{ $admin->nombre_completo }}</div>
                        <div class="text-xs">{{ $admin->email }}</div>
                        <div class="text-xs mt-1 {{ $admin->esSuperadmin() ? 'text-yellow-400' : 'text-blue-400' }}">
                            {{ $admin->esSuperadmin() ? 'Superadmin' : 'Admin Sucursal' }}
                            @if($admin->esAdminSucursal() && $admin->sucursal)
                                · {{ $admin->sucursal->nombre }}
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full text-gray-300 hover:text-white hover:bg-white hover:bg-opacity-10 px-3 py-2 rounded-md text-base font-medium">
                            <i class="fas fa-sign-out-alt w-5 mr-2"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success') || session('error'))
    <div id="toast-container" class="fixed top-20 right-4 z-[60] flex flex-col gap-3 pointer-events-none" style="max-width: 380px;">
        @if(session('success'))
        <div id="toast-success" class="pointer-events-auto flex items-start gap-3 px-5 py-4 rounded-2xl shadow-2xl text-white transition-all duration-500 opacity-100"
             style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 8px 32px rgba(5,150,105,0.4);">
            <div class="w-8 h-8 rounded-full bg-white bg-opacity-25 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check text-white text-sm"></i>
            </div>
            <p class="text-sm font-medium leading-snug flex-1">{{ session('success') }}</p>
            <button onclick="dismissToast('toast-success')" class="text-white text-opacity-70 hover:text-opacity-100"><i class="fas fa-times text-xs"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div id="toast-error" class="pointer-events-auto flex items-start gap-3 px-5 py-4 rounded-2xl shadow-2xl text-white transition-all duration-500 opacity-100"
             style="background: linear-gradient(135deg, #dc2626 0%, #9f1239 100%);">
            <div class="w-8 h-8 rounded-full bg-white bg-opacity-25 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation text-white text-sm"></i>
            </div>
            <p class="text-sm font-medium leading-snug flex-1">{{ session('error') }}</p>
            <button onclick="dismissToast('toast-error')" class="text-white text-opacity-70 hover:text-opacity-100"><i class="fas fa-times text-xs"></i></button>
        </div>
        @endif
    </div>
    <script>
        function dismissToast(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.opacity = '0';
            el.style.transform = 'translateY(-12px)';
            setTimeout(() => el.remove(), 500);
        }
        document.addEventListener('DOMContentLoaded', function () {
            ['toast-success', 'toast-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) setTimeout(() => dismissToast(id), 4500);
            });
        });
    </script>
    @endif

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-gray-400 text-center py-4 text-sm">
        <p>&copy; {{ date('Y') }} <span class="font-mercurius">La Zarza Contigo</span> · Panel de Administración</p>
    </footer>

    @stack('scripts')
</body>
</html>
