@extends('errors.layout')

@section('title', '500 - Error del Servidor')

@section('content')
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-server text-red-500 text-4xl"></i>
    </div>
    <h1 class="text-6xl font-bold text-gray-300 mb-2">500</h1>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Error del Servidor</h2>
    <p class="text-gray-600 mb-8">
        Ha ocurrido un error interno en el servidor. Nuestro equipo ha sido notificado. Por favor, inténtalo de nuevo más tarde.
    </p>
    <div class="space-y-3">
        <a href="/" class="block w-full btn-primary text-white py-3 px-6 rounded-lg font-medium">
            <i class="fas fa-home mr-2"></i> Volver al Inicio
        </a>
        <button onclick="history.back()" class="block w-full bg-gray-100 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-200 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Regresar
        </button>
    </div>
@endsection
