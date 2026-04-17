@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@push('styles')
<style>
    .catalog-container {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    .catalog-container iframe {
        -webkit-touch-callout: none;
    }
    
    /* Permitir selección en elementos interactivos importantes */
    .catalog-container a,
    .catalog-container button {
        -webkit-user-select: auto;
        -moz-user-select: auto;
        -ms-user-select: auto;
        user-select: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    // Deshabilitar clic derecho en toda la página del catálogo
    document.addEventListener('contextmenu', function(e) {
        if (e.target.closest('.catalog-container')) {
            e.preventDefault();
            return false;
        }
    });

    // Deshabilitar atajos de teclado comunes para imprimir y guardar
    document.addEventListener('keydown', function(e) {
        // Ctrl+P (imprimir) o Cmd+P (Mac)
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            return false;
        }
        // Ctrl+S (guardar) o Cmd+S (Mac)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            return false;
        }
    });
</script>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 catalog-container">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="fas fa-book text-[#7a2682] mr-2"></i>
            Catálogo de Productos
        </h1>
        <p class="text-gray-600">Conoce nuestros productos</p>
    </div>


    <!-- Visor de PDF embebido -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden" oncontextmenu="return false;">
        <div class="relative" style="padding-top: 141.42%;">
            <!-- Ratio 1:√2 (formato A4) -->
            <iframe 
                src="{{ asset('catalogo_productos.pdf') }}#toolbar=0&navpanes=0&scrollbar=1&view=FitH&zoom=page-fit"
                class="absolute top-0 left-0 w-full h-full border-0"
                style="min-height: 800px; pointer-events: auto;"
                title="Catálogo de Productos Zarza"
                loading="lazy"
                oncontextmenu="return false;"
            ></iframe>
        </div>
    </div>

    <!-- Mensaje alternativo para navegadores que no soportan iframe -->
    <noscript>
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Tu navegador no soporta la visualización de PDFs embebidos. Por favor, visita nuestras sucursales para obtener una copia del catálogo.
            </p>
        </div>
    </noscript>

    <!-- Aviso de protección de contenido -->
    

    <!-- Información adicional -->
    <div class="mt-6 bg-gray-50 rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-3">
            <i class="fas fa-info-circle text-[#7a2682] mr-2"></i>
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
    <div class="mt-8 text-center bg-gradient-to-r from-[#b31983] to-[#b31983] rounded-lg p-8 text-white">
        <h3 class="text-2xl font-bold mb-3">¿Listo para acumular puntos?</h3>
        <p class="text-lg mb-4">Visita cualquiera de nuestras sucursales y comienza a ganar recompensas</p>
        <a href="{{ route('branches.index') }}" 
           class="inline-flex items-center px-6 py-3 bg-white text-[#7a2682] rounded-lg hover:bg-gray-100 transition-colors font-semibold">
            <i class="fas fa-map-marker-alt mr-2"></i>
            Ver sucursales
        </a>
    </div>
</div>
@endsection
