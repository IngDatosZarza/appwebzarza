@extends('layouts.admin')

@section('title', 'Editar Administrador')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-user-edit text-indigo-600 mr-2"></i>
                    Editar Administrador
                </h1>
                <p class="text-gray-500 mt-1">{{ $administrador->nombre_completo }}</p>
            </div>
            <a href="{{ route('admin.usuarios.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form method="POST" action="{{ route('admin.usuarios.update', $administrador->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">Nombres *</label>
                    <input type="text" name="nombres" id="nombres" value="{{ old('nombres', $administrador->nombres) }}" required
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('nombres') border-red-500 @enderror">
                    @error('nombres')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno *</label>
                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $administrador->apellido_paterno) }}" required
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('apellido_paterno') border-red-500 @enderror">
                    @error('apellido_paterno')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno *</label>
                <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $administrador->apellido_materno) }}" required
                    class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('apellido_materno') border-red-500 @enderror">
                @error('apellido_materno')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $administrador->email) }}" required
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $administrador->telefono) }}" required
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('telefono') border-red-500 @enderror">
                    @error('telefono')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="sucursal_id" class="block text-sm font-medium text-gray-700 mb-1">Sucursal Asignada *</label>
                <select name="sucursal_id" id="sucursal_id" required
                    class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('sucursal_id') border-red-500 @enderror">
                    <option value="">Seleccionar sucursal...</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ old('sucursal_id', $administrador->sucursal_id) == $sucursal->id ? 'selected' : '' }}>
                            {{ $sucursal->nombre }} ({{ $sucursal->codigo }})
                        </option>
                    @endforeach
                </select>
                @error('sucursal_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="border-t pt-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-1"><i class="fas fa-lock mr-1"></i> Cambiar Contraseña</h3>
                <p class="text-xs text-gray-500 mb-3">Deja en blanco para mantener la contraseña actual</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                        <input type="password" name="password" id="password" minlength="10"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 @error('password') border-red-500 @enderror">
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Info box -->
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                <p><strong>Creado:</strong> {{ $administrador->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Último acceso:</strong> {{ $administrador->ultimo_acceso ? $administrador->ultimo_acceso->format('d/m/Y H:i') : 'Nunca' }}</p>
                <p><strong>Estado:</strong> {{ $administrador->activo ? 'Activo' : 'Inactivo' }}</p>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('admin.usuarios.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-white font-medium transition-all hover:shadow-lg" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
