@extends('layouts.admin')

@section('title', 'Detalle Promoción - ' . $promocion->nombre)

@section('content')
<div class="container mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <div class="mb-6">
        <a href="{{ route('admin.promos-oppen.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Promociones Oppen
        </a>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-mono font-bold bg-purple-100 text-purple-700">
                        {{ $promocion->oppen_code }}
                    </span>
                    @if($promocion->activo)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-eye mr-1"></i>Activa
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-eye-slash mr-1"></i>Inactiva
                        </span>
                    @endif
                    @if($promocion->estaDisponibleAhora())
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                            Disponible ahora
                        </span>
                    @endif
                </div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $promocion->nombre }}</h1>
                @if($promocion->descripcion_limpia)
                    <p class="text-gray-600 mt-2">{{ $promocion->descripcion_limpia }}</p>
                @endif
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                    {{ $promocion->resumen_accion }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- Información general --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-info-circle text-purple-500 mr-2"></i>
                    Información General
                </h2>
            </div>
            <div class="p-6">
                <dl class="space-y-4">
                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Código Oppen</dt>
                        <dd class="text-sm text-gray-900 font-mono font-bold">{{ $promocion->oppen_code }}</dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="text-sm text-gray-900">{{ $promocion->nombre }}</dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Vigencia</dt>
                        <dd class="text-sm text-gray-900">
                            {{ $promocion->fecha_inicio->format('d/m/Y') }} — {{ $promocion->fecha_fin->format('d/m/Y') }}
                        </dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Horario</dt>
                        <dd class="text-sm text-gray-900">{{ $promocion->horario_texto }}</dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div class="flex justify-between items-start">
                        <dt class="text-sm font-medium text-gray-500">Combinable</dt>
                        <dd class="text-sm">
                            @if($promocion->combinable)
                                <span class="text-green-600"><i class="fas fa-check mr-1"></i>Sí</span>
                            @else
                                <span class="text-red-500"><i class="fas fa-times mr-1"></i>No</span>
                            @endif
                        </dd>
                    </div>
                    <div class="border-t border-gray-100"></div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-2">Días habilitados</dt>
                        <dd>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $diasMap = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
                                    $diasPromo = $promocion->dias_semana ?? [];
                                @endphp
                                @foreach($diasMap as $en => $label)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ !empty($diasPromo[$en]) ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400 line-through' }}">
                                        {{ $label }}
                                    </span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Acciones y condiciones --}}
        <div class="space-y-8">
            {{-- Acciones --}}
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                        Acciones de la Promoción
                    </h2>
                </div>
                <div class="p-6">
                    @if(!empty($promocion->acciones) && is_array($promocion->acciones))
                        @foreach($promocion->acciones as $accion)
                            <div class="bg-purple-50 rounded-lg p-4 mb-3 last:mb-0">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @if(!empty($accion['type']))
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-purple-200 text-purple-800">{{ $accion['type'] }}</span>
                                    @endif
                                    @if(!empty($accion['subtype']))
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-pink-200 text-pink-800">{{ $accion['subtype'] }}</span>
                                    @endif
                                    @if(!empty($accion['label']))
                                        <span class="px-2 py-1 rounded text-xs font-bold bg-green-200 text-green-800">{{ $accion['label'] }}</span>
                                    @endif
                                </div>
                                <dl class="grid grid-cols-2 gap-2 text-xs">
                                    @if(!empty($accion['perEach']))
                                        <div>
                                            <dt class="text-gray-500">Compra</dt>
                                            <dd class="font-bold text-gray-800">{{ $accion['perEach'] }} unidades</dd>
                                        </div>
                                    @endif
                                    @if(!empty($accion['freeUnits']))
                                        <div>
                                            <dt class="text-gray-500">Gratis</dt>
                                            <dd class="font-bold text-gray-800">{{ $accion['freeUnits'] }} unidades</dd>
                                        </div>
                                    @endif
                                    @if(!empty($accion['applyTo']))
                                        <div>
                                            <dt class="text-gray-500">Aplica a</dt>
                                            <dd class="font-bold text-gray-800">{{ $accion['applyTo'] }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-400 text-sm">Sin acciones definidas</p>
                    @endif
                </div>
            </div>

            {{-- Condiciones --}}
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-filter text-blue-500 mr-2"></i>
                        Condiciones
                    </h2>
                </div>
                <div class="p-6">
                    @if(!empty($promocion->condiciones))
                        @php $cond = $promocion->condiciones; @endphp
                        @if(isset($cond['logicalOperator']))
                            <div class="mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    Operador: {{ ucfirst($cond['logicalOperator']) }}
                                </span>
                            </div>
                        @endif
                        @if(!empty($cond['children']))
                            @foreach($cond['children'] as $child)
                                @if(!empty($child['query']))
                                    <div class="bg-blue-50 rounded-lg p-3 mb-2 last:mb-0 text-sm">
                                        <span class="font-bold text-blue-800">{{ $child['query']['rule'] ?? '—' }}</span>
                                        <span class="text-gray-500 mx-1">{{ $child['query']['operator'] ?? '' }}</span>
                                        <span class="font-mono text-blue-600">"{{ $child['query']['value'] ?? '' }}"</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @else
                        <p class="text-gray-400 text-sm">Sin condiciones definidas</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sincronización info --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-sync-alt text-green-500 mr-2"></i>
                Datos de Sincronización
            </h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Última Sincronización</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1">
                        {{ $promocion->ultima_sincronizacion ? $promocion->ultima_sincronizacion->format('d/m/Y H:i:s') : 'Nunca' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Creado en BD</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1">{{ $promocion->created_at->format('d/m/Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Última actualización BD</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1">{{ $promocion->updated_at->format('d/m/Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">ID Interno Oppen</dt>
                    <dd class="text-sm font-bold text-gray-800 mt-1">{{ $promocion->datos_raw['internalId'] ?? '—' }}</dd>
                </div>
            </div>

            {{-- Datos crudos (colapsable) --}}
            <div x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <i class="fas fa-code"></i>
                    <span>Datos crudos de la API (JSON)</span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas text-xs"></i>
                </button>
                <div x-show="open" x-transition class="mt-3">
                    <pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto text-xs leading-relaxed max-h-96 overflow-y-auto">{{ json_encode($promocion->datos_raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Descripción HTML original --}}
    @if($promocion->descripcion)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-8">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-align-left text-gray-500 mr-2"></i>
                    Comunicación (HTML original)
                </h2>
            </div>
            <div class="p-6">
                <div class="prose max-w-none text-sm">{!! $promocion->descripcion !!}</div>
            </div>
        </div>
    @endif
</div>
@endsection
