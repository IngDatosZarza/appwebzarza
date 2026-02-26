@extends('layouts.app')

@section('title', 'Dashboard - ZarzaPoints')

@section('content')
<div class="container mx-auto py-8">
        @if(isset($isAuthenticated) && $isAuthenticated)
            <div class="bg-white rounded-lg shadow p-6">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Cliente</h1>
                <p class="text-gray-600 mt-2">Bienvenido {{ $userData['nombre'] ?? 'Usuario' }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="bg-blue-100 p-4 rounded">
                        <h3 class="font-semibold">Mis Puntos</h3>
                        <p class="text-2xl font-bold">{{ number_format($userData['puntos'] ?? 0) }}</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded">
                        <h3 class="font-semibold">Mis Compras</h3>
                        <p class="text-2xl font-bold">{{ $comprasData['total_compras'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded">
                        <h3 class="font-semibold">Cupones</h3>
                        <p class="text-2xl font-bold">{{ $misCupones ?? 0 }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Sistema de Puntos</h1>
                <p class="text-gray-600 mt-4">Inicia sesión para ver tu dashboard personalizado</p>
                <div class="mt-6 space-x-4">
                    <a href="/login" class="bg-blue-500 text-white px-4 py-2 rounded">Iniciar Sesión</a>
                    <a href="/register" class="bg-green-500 text-white px-4 py-2 rounded">Registrarse</a>
                </div>
            </div>
        @endif
        
        @if(isset($error))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4">
                Error: {{ $error }}
            </div>
        @endif
    </div>
@endsection