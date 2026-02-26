@extends('layouts.app')

@section('title', 'Panel de Administración - Sistema de Puntos')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-shield-alt text-red-500 mr-3"></i>
                    Panel de Administración
                </h1>
                <p class="text-gray-600 mt-2">Gestión y estadísticas del sistema de puntos de fidelidad</p>
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-crown mr-1"></i>
                        Administrador: {{ Session::get('user_nombre', 'Admin') }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Última actualización</div>
                <div class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Usuarios</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_users'] ?? 0) }}</p>
                </div>
                <div class="text-blue-200">
                    <i class="fas fa-users text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Puntos en Circulación</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_points'] ?? 0) }}</p>
                </div>
                <div class="text-green-200">
                    <i class="fas fa-coins text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Transacciones del Mes</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['monthly_transactions'] ?? 0) }}</p>
                </div>
                <div class="text-purple-200">
                    <i class="fas fa-exchange-alt text-3xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Cupones Canjeados</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['monthly_coupons'] ?? 0) }}</p>
                </div>
                <div class="text-orange-200">
                    <i class="fas fa-ticket-alt text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-history text-blue-500 mr-2"></i>
                    Transacciones Recientes
                </h2>
                <a href="{{ route('admin.transactions') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentTransactions ?? [] as $transaction)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if($transaction['tipo'] == 'ganancia')
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus text-green-600 text-sm"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-minus text-red-600 text-sm"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $transaction['nombres'] }} {{ $transaction['apellido_paterno'] }}</p>
                                <p class="text-xs text-gray-500">{{ $transaction['descripcion'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ $transaction['tipo'] == 'ganancia' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction['tipo'] == 'ganancia' ? '+' : '-' }}{{ number_format($transaction['puntos']) }}
                            </p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('d/m H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p>No hay transacciones recientes</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Users -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                    Usuarios Top
                </h2>
                <span class="text-sm text-gray-500">Por puntos acumulados</span>
            </div>
            
            <div class="space-y-3">
                @forelse($topUsers ?? [] as $index => $user)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $index == 0 ? 'bg-yellow-100 text-yellow-600' : ($index == 1 ? 'bg-gray-100 text-gray-600' : ($index == 2 ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) }}">
                                    @if($index < 3)
                                        <i class="fas fa-crown text-sm"></i>
                                    @else
                                        <span class="text-sm font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $user['nombres'] }} {{ $user['apellido_paterno'] }}</p>
                                <p class="text-xs text-gray-500">{{ $user['email'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-green-600">{{ number_format($user['saldo']) }} pts</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-users text-3xl mb-2"></i>
                        <p>No hay usuarios registrados</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-tools text-gray-500 mr-2"></i>
            Acciones de Administración
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.transactions') }}" class="flex items-center justify-center p-4 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                <i class="fas fa-exchange-alt mr-2"></i>
                Ver Transacciones
            </a>
            <a href="/purchase" class="flex items-center justify-center p-4 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                <i class="fas fa-plus-circle mr-2"></i>
                Registrar Compra
            </a>
            <a href="/coupons" class="flex items-center justify-center p-4 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors">
                <i class="fas fa-ticket-alt mr-2"></i>
                Gestionar Cupones
            </a>
            <a href="/notifications" class="flex items-center justify-center p-4 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition-colors">
                <i class="fas fa-bell mr-2"></i>
                Notificaciones
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-refresh stats every 30 seconds
    setTimeout(() => {
        location.reload();
    }, 30000);
</script>
@endsection