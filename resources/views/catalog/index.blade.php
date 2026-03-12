@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-book text-[#FF6600] mr-2"></i>
            Catálogo de Productos
        </h1>
        <p class="text-gray-600">Explora nuestra selección completa de productos</p>
    </div>

    <!-- Botones de acción -->
    <div class="mb-4 flex flex-wrap gap-3">
        <a href="{{ asset('catalogo_productos.pdf') }}" 
           target="_blank"
           class="inline-flex items-center px-4 py-2 bg-[#FF6600] text-white rounded-lg hover:bg-[#E55A00] transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>
            Abrir en nueva pestaña
        </a>
        <a href="{{ asset('catalogo_productos.pdf') }}" 
           download="Catalogo_Productos_Zarza.pdf"
           class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors">
            <i class="fas fa-download mr-2"></i>
            Descargar PDF
        </a>
    </div>

    <!-- Visor de PDF embebido -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="relative" style="padding-top: 141.42%;">
            <!-- Ratio 1:√2 (formato A4) -->
            <iframe 
                src="{{ asset('catalogo_productos.pdf') }}#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
                class="absolute top-0 left-0 w-full h-full border-0"
                style="min-height: 800px;"
                title="Catálogo de Productos Zarza"
                loading="lazy"
            ></iframe>
        </div>
    </div>

    <!-- Mensaje alternativo para navegadores que no soportan iframe -->
    <noscript>
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Tu navegador no soporta la visualización de PDFs embebidos. 
                <a href="{{ asset('catalogo_productos.pdf') }}" target="_blank" class="text-[#FF6600] underline font-semibold">
                    Haz clic aquí para ver el catálogo
                </a>
            </p>
        </div>
    </noscript>

    <!-- Información adicional -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-3">
            <i class="fas fa-info-circle text-[#FF6600] mr-2"></i>
            Información del Catálogo
        </h2>
        <ul class="space-y-2 text-gray-700">
            <li>
                <i class="fas fa-check text-green-600 mr-2"></i>
                Todos nuestros productos acumulan puntos de fidelidad
            </li>
            <li>
                <i class="fas fa-check text-green-600 mr-2"></i>
                Encuentra productos disponibles en todas nuestras sucursales
            </li>
            <li>
                <i class="fas fa-check text-green-600 mr-2"></i>
                Precios sujetos a cambios sin previo aviso
            </li>
            <li>
                <i class="fas fa-check text-green-600 mr-2"></i>
                Catálogo actualizado mensualmente
            </li>
        </ul>
    </div>

    <!-- Call to action -->
    <div class="mt-8 text-center bg-gradient-to-r from-[#FF6600] to-[#E55A00] rounded-lg p-8 text-white">
        <h3 class="text-2xl font-bold mb-3">¿Listo para acumular puntos?</h3>
        <p class="text-lg mb-4">Visita cualquiera de nuestras sucursales y comienza a ganar recompensas</p>
        <a href="{{ route('branches.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-white text-[#FF6600] rounded-lg hover:bg-gray-100 transition-colors font-semibold">
            <i class="fas fa-map-marker-alt mr-2"></i>
            Ver sucursales
        </a>
    </div>
</div>
@endsection
