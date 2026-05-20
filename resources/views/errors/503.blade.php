@extends('errors.layout')

@section('title', '503 - Servicio No Disponible')

@section('content')
    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-tools text-blue-500 text-4xl"></i>
    </div>
    <h1 class="text-6xl font-bold text-gray-300 mb-2">503</h1>
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Servicio No Disponible</h2>
    <p class="text-gray-600 mb-8">
        Estamos realizando mantenimiento en el sistema. Volveremos en breve. Gracias por tu paciencia.
    </p>
    <div class="space-y-3">
        <button onclick="location.reload()" class="block w-full btn-primary text-white py-3 px-6 rounded-lg font-medium">
            <i class="fas fa-redo mr-2"></i> Reintentar
        </button>
    </div>
@endsection
