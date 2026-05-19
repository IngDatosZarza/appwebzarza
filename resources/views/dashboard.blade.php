@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Puntos')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-chart-line text-blue-500 mr-3"></i>
                    @if(isset($isAuthenticated) && $isAuthenticated)
                        Mi Dashboard
                    @else
                        Sistema de Puntos de Fidelidad
                    @endif
                </h1>
                <p class="text-gray-600 mt-2">
                    @if(isset($isAuthenticated) && $isAuthenticated)
                        Bienvenido {{ $userData['nombre'] ?? 'Usuario' }}, aquí tienes tu resumen de puntos
                    @else
                        Acumula puntos y disfruta de increíbles beneficios
                    @endif
                </p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Última actualización</div>
                <div class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    @if(isset($isAuthenticated) && $isAuthenticated)
        <!-- User Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Mis Puntos</p>
                        <p class="text-3xl font-bold">{{ number_format($userData['puntos'] ?? 0) }}</p>
                    </div>
                    <div class="text-blue-200">
                        <i class="fas fa-coins text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('points.history') }}" class="text-blue-100 hover:text-white text-sm font-medium">
                        Ver historial <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Mis Compras</p>
                        <p class="text-3xl font-bold">{{ number_format($comprasData['total_compras'] ?? 0) }}</p>
                    </div>
                    <div class="text-green-200">
                        <i class="fas fa-shopping-cart text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('purchases.index') }}" class="text-green-100 hover:text-white text-sm font-medium">
                        Ver compras <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Cupones Disponibles</p>
                        <p class="text-3xl font-bold">{{ number_format($cuponesDisponibles ?? 0) }}</p>
                    </div>
                    <div class="text-purple-200">
                        <i class="fas fa-ticket-alt text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('coupons.index') }}" class="text-purple-100 hover:text-white text-sm font-medium">
                        Promociones <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-sm p-6 text-white card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Mis Cupones</p>
                        <p class="text-3xl font-bold">{{ number_format($misCupones ?? 0) }}</p>
                    </div>
                    <div class="text-orange-200">
                        <i class="fas fa-trophy text-3xl"></i>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-orange-100 text-sm font-medium">
                        @php
                            $puntos = auth()->user()->puntos->puntos_acumulados ?? 0;
                            $siguiente = $puntos < 100 ? 100 : ($puntos < 500 ? 500 : ($puntos < 1000 ? 1000 : 'Máximo'));
                        @endphp
                        @if($siguiente !== 'Máximo')
                            {{ $siguiente - $puntos }} pts para subir
                        @else
                            Nivel máximo alcanzado
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Purchases -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-shopping-bag text-green-500 mr-2"></i>
                        Compras Recientes
                    </h2>
                    <a href="{{ route('purchases.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Ver todas
                    </a>
                </div>
                
                @if(auth()->user()->compras->count() > 0)
                    <div class="space-y-3">
                        @foreach(auth()->user()->compras->take(3) as $compra)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-store text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $compra->sucursal->nombre }}</p>
                                        <p class="text-sm text-gray-500">{{ $compra->fecha_compra->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">${{ number_format($compra->monto, 2) }}</p>
                                    <p class="text-sm text-green-600">+{{ $compra->puntos_generados }} pts</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">No tienes compras registradas aún</p>
                        <p class="text-sm text-gray-500 mt-2">¡Realiza tu primera compra para ganar puntos!</p>
                    </div>
                @endif
            </div>

            <!-- Available Coupons -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-gift text-purple-500 mr-2"></i>
                        Cupones Disponibles
                    </h2>
                    <a href="{{ route('coupons.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Ver todos
                    </a>
                </div>
                
                @php
                    $cuponesDisponibles = \App\Models\Cupon::where('activo', true)
                        ->where('fecha_vencimiento', '>=', now())
                        ->where('cantidad_disponible', '>', 0)
                        ->orderBy('puntos_requeridos', 'asc')
                        ->take(3)
                        ->get();
                @endphp
                
                @if($cuponesDisponibles->count() > 0)
                    <div class="space-y-3">
                        @foreach($cuponesDisponibles as $cupon)
                            <div class="border border-gray-200 rounded-lg p-3 hover:border-purple-300 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $cupon->nombre }}</p>
                                        <p class="text-sm text-gray-500">{{ $cupon->descripcion }}</p>
                                        <p class="text-sm text-purple-600 font-medium mt-1">
                                            {{ $cupon->puntos_requeridos }} puntos requeridos
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        @if($cupon->tipo_descuento === 'porcentaje')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $cupon->valor_descuento }}% OFF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ${{ number_format($cupon->valor_descuento, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-ticket-alt text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">No hay cupones disponibles</p>
                        <p class="text-sm text-gray-500 mt-2">¡Sigue comprando para desbloquear ofertas!</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Guest Welcome Section -->
        <div class="text-center py-12">
            <div class="max-w-3xl mx-auto">
                <i class="fas fa-star text-6xl text-yellow-400 mb-6"></i>
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    ¡Bienvenido a FidelityPoints!
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Únete a nuestro programa de fidelidad y comienza a ganar puntos con cada compra
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shopping-cart text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Compra y Gana</h3>
                        <p class="text-gray-600">Gana 1 punto por cada peso gastado en nuestras sucursales</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-gift text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Canjea Cupones</h3>
                        <p class="text-gray-600">Usa tus puntos para obtener descuentos y ofertas especiales</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Sube de Nivel</h3>
                        <p class="text-gray-600">Alcanza diferentes niveles y desbloquea beneficios exclusivos</p>
                    </div>
                </div>
                
                <div class="space-x-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        Registrarse Gratis
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Public Dashboard (Non-authenticated users) -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-8 text-white text-center">
            <div class="max-w-2xl mx-auto">
                <i class="fas fa-star text-4xl mb-4"></i>
                <h2 class="text-3xl font-bold mb-4">¡Únete a Nuestro Sistema de Puntos!</h2>
                <p class="text-lg mb-6 text-blue-100">
                    Acumula puntos con cada compra y disfruta de increíbles beneficios y descuentos exclusivos.
                </p>
                <div class="space-x-4">
                    <a href="/register" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        Registrarse
                    </a>
                    <a href="/login" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors border border-blue-400">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Iniciar Sesión
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Benefits Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-coins text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Acumula Puntos</h3>
                <p class="text-gray-600">Gana puntos con cada compra que realices en nuestras sucursales.</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-gift text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Canjea Cupones</h3>
                <p class="text-gray-600">Utiliza tus puntos para obtener descuentos y ofertas especiales.</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-crown text-purple-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Beneficios Exclusivos</h3>
                <p class="text-gray-600">Accede a promociones especiales solo para miembros registrados.</p>
            </div>
        </div>
    @endif

    <!-- System Stats (Public) -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6 text-center">
            <i class="fas fa-chart-bar text-indigo-500 mr-2"></i>
            Estadísticas del Sistema
        </h2>
        
        @php
            try {
                $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
                $pdo->exec('SET search_path TO appweb, public');
                
                $usuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() ?: 0;
                $compras = $pdo->query('SELECT COUNT(*) FROM compras')->fetchColumn() ?: 0;
                $cupones = $pdo->query('SELECT COUNT(*) FROM cupones WHERE activo = true')->fetchColumn() ?: 0;
                $sucursales = $pdo->query('SELECT COUNT(*) FROM sucursales WHERE activo = true')->fetchColumn() ?: 0;
            } catch (Exception $e) {
                $usuarios = $compras = $cupones = $sucursales = 0;
            }
        @endphp
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600">{{ number_format($usuarios) }}</div>
                <div class="text-sm text-gray-600 mt-1">Usuarios Registrados</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600">{{ number_format($compras) }}</div>
                <div class="text-sm text-gray-600 mt-1">Compras Realizadas</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600">{{ number_format($cupones) }}</div>
                <div class="text-sm text-gray-600 mt-1">Cupones Disponibles</div>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-orange-600">{{ number_format($sucursales) }}</div>
                <div class="text-sm text-gray-600 mt-1">Sucursales Activas</div>
            </div>
        </div>
    </div>
</div>
@endsection