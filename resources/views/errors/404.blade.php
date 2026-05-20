@extends('errors.layout')

@section('title', '404 - Página No Encontrada')

@section('content')
    <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-search text-purple-500 text-4xl"></i>
    </div>
    <h1 class="text-6xl font-bold text-gray-300 mb-2">404</h1>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Página No Encontrada</h2>
    <p class="text-gray-600 mb-8">
        La página que buscas no existe o ha sido movida. Verifica la dirección e inténtalo de nuevo.
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
