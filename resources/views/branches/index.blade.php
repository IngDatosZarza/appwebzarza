@extends('layouts.app')

@section('title', 'Nuestras Sucursales - La Zarza Contigo')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <i class="fas fa-store text-4xl gradient-bg text-transparent bg-clip-text mr-3"></i>
            <h1 class="text-3xl font-bold text-gray-800">Nuestras Sucursales</h1>
        </div>
        <p class="text-gray-600">Encuentra la sucursal más cercana a ti. Visítanos y acumula puntos con cada compra.</p>
    </div>

    @if($sucursales->count() > 0)
        <!-- Listado de Sucursales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sucursales as $sucursal)
                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden card-hover">
                    <!-- Header de la tarjeta -->
                    <div class="gradient-bg px-6 py-4">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            {{ $sucursal->nombre }}
                        </h3>
                        @if($sucursal->codigo)
                            <p class="text-pink-200 text-sm mt-1">
                                <i class="fas fa-tag mr-1"></i>
                                Código: {{ $sucursal->codigo }}
                            </p>
                        @endif
                    </div>
                    
                    <!-- Contenido de la tarjeta -->
                    <div class="p-6 space-y-4">
                        <!-- Dirección -->
                        <div class="flex items-start">
                            <i class="fas fa-location-dot text-purple-600 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-1">Dirección</p>
                                <p class="text-gray-600">
                                    {{ $sucursal->direccion }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Teléfono -->
                        @if($sucursal->telefono)
                            <div class="flex items-start">
                                <i class="fas fa-phone text-purple-600 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-700 mb-1">Teléfono</p>
                                    <a href="tel:{{ $sucursal->telefono }}" class="text-purple-600 hover:text-purple-800 transition-colors">
                                        {{ $sucursal->telefono }}
                                    </a>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Acciones -->
                        <div class="pt-4 border-t border-gray-100 flex space-x-2">
                            <!-- Botón para abrir en Google Maps -->
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($sucursal->direccion) }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="flex-1 btn-primary text-white py-2 px-4 rounded-lg text-center text-sm font-semibold hover:opacity-90 transition-all flex items-center justify-center">
                                <i class="fas fa-map-location-dot mr-2"></i>
                                Cómo llegar
                            </a>
                            
                            @if($sucursal->telefono)
                                <!-- Botón para llamar -->
                                <a href="tel:{{ $sucursal->telefono }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-center text-sm font-semibold transition-colors flex items-center justify-center">
                                    <i class="fas fa-phone mr-2"></i>
                                    Llamar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Estado vacío -->
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-store-slash text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay sucursales disponibles</h3>
            <p class="text-gray-500">Por el momento no tenemos información de sucursales disponibles.</p>
        </div>
    @endif

    <!-- Información adicional -->
    <div class="mt-8 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-purple-600 text-2xl mt-1 mr-4"></i>
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">¿Cómo acumular puntos?</h3>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Visita cualquiera de nuestras sucursales
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Realiza tu compra y solicita registrar tu ticket
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Acumula puntos y canjéalos por cupones increíbles
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
