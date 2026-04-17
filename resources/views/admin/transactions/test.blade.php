@extends('layouts.app')

@section('title', 'Transacciones - Test')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Test - Transacciones</h1>
    
    <p>Usuario autenticado: {{ Session::get('user_authenticated') ? 'Sí' : 'No' }}</p>
    <p>Rol de usuario: {{ Session::get('user_rol') }}</p>
    <p>Total transacciones: {{ count($transacciones) }}</p>
    
    <div class="bg-white p-6 rounded-lg shadow mt-6">
        <h2 class="text-lg font-semibold mb-4">Lista de Transacciones</h2>
        
        @if(count($transacciones) > 0)
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Usuario</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transacciones as $transaccion)
                        <tr>
                            <td class="px-4 py-2">#{{ $transaccion['id'] }}</td>
                            <td class="px-4 py-2">{{ $transaccion['usuario_nombre'] }}</td>
                            <td class="px-4 py-2">{{ $transaccion['tipo_descripcion'] }}</td>
                            <td class="px-4 py-2">{{ $transaccion['puntos'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-600">No hay transacciones disponibles.</p>
        @endif
    </div>
</div>
@endsection