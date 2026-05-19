@extends('layouts.admin')

@section('title', 'Mi Sucursal - Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-store text-blue-600 mr-2"></i>
            Mi Sucursal: {{ $admin->sucursal ? $admin->sucursal->nombre : 'Sin asignar' }}
        </h1>
        <p class="text-gray-500 mt-1">Bienvenido, {{ $admin->nombres }}. Aquí puedes ver el resumen de tu actividad.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Mis Clientes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $misClientes }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Hoy</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $clientesHoy }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-calendar-week text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Esta Semana</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $clientesSemana }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs text-gray-500">Este Mes</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $clientesMes }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('admin.clientes.registrar') }}" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow group">
            <div class="flex items-center">
                <div class="p-4 rounded-full bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200 transition-colors">
                    <i class="fas fa-user-plus text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Registrar Cliente</h3>
                    <p class="text-sm text-gray-500">Dar de alta un nuevo cliente del programa de fidelización</p>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.mi-sucursal.clientes') }}" class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow group">
            <div class="flex items-center">
                <div class="p-4 rounded-full bg-green-100 text-green-600 group-hover:bg-green-200 transition-colors">
                    <i class="fas fa-list text-2xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-800">Ver Mis Clientes</h3>
                    <p class="text-sm text-gray-500">Consulta la lista de clientes que has registrado</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Últimos clientes -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-clock text-blue-600 mr-2"></i>
                Últimos Clientes Registrados por Ti
            </h2>
        </div>
        @if($ultimosClientes->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($ultimosClientes as $cliente)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $cliente->nombre_completo }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $cliente->email }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $cliente->telefono }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500">{{ $cliente->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">
            <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
            <p>Aún no has registrado clientes.</p>
            <a href="{{ route('admin.clientes.registrar') }}" class="text-indigo-600 hover:text-indigo-800 text-sm mt-2 inline-block">
                <i class="fas fa-user-plus mr-1"></i> Registrar tu primer cliente
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
