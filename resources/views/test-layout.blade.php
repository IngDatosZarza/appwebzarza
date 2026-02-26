@extends('layouts.app')

@section('title', 'Test Layout')

@section('content')
<div class="container mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900">Test de Layout</h1>
        <p class="text-gray-600 mt-2">Esta es una vista de prueba para verificar que el layout funciona.</p>
        
        <div class="mt-4">
            <p><strong>Usuario autenticado:</strong> {{ Session::get('user_authenticated', false) ? 'Sí' : 'No' }}</p>
            <p><strong>Nombre:</strong> {{ Session::get('user_nombre', 'N/A') }}</p>
            <p><strong>Email:</strong> {{ Session::get('user_email', 'N/A') }}</p>
            <p><strong>Puntos:</strong> {{ Session::get('user_puntos', 0) }}</p>
            <p><strong>Rol:</strong> {{ Session::get('user_rol', 'N/A') }}</p>
        </div>
    </div>
</div>
@endsection