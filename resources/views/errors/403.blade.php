@extends('errors.layout')

@section('title', '403 - Acceso Denegado')

@section('content')
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-ban text-red-500 text-4xl"></i>
    </div>
    <h1 class="text-6xl font-bold text-gray-300 mb-2">403</h1>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Acceso Denegado</h2>
    <p class="text-gray-600 mb-8">
        No tienes permiso para acceder a esta página. Si crees que esto es un error, por favor contacta al administrador.
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
