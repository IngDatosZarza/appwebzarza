<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUsuariosController extends Controller
{
    public function index(Request $request)
    {
        $query = Administrador::adminsSucursal()->with('sucursal');

        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->sucursal_id);
        }

        if ($request->filled('activo')) {
            $esActivo = $request->activo === '1' ? 'TRUE' : 'FALSE';
            $query->whereRaw("\"activo\" = {$esActivo}");
        }

        $administradores = $query->withCount('clientesRegistrados')->orderBy('created_at', 'desc')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();
        $totalClientes = Usuario::clientes()->count();

        return view('admin.usuarios.index', compact('administradores', 'sucursales', 'totalClientes'));
    }

    public function create()
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        return view('admin.usuarios.create', compact('sucursales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:administradores',
            'telefono' => ['required', 'string', 'max:15', 'unique:administradores'],
            'sucursal_id' => 'required|exists:sucursales,id',
            'password' => 'required|string|min:10|confirmed',
        ], [
            'nombres.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.unique' => 'Este teléfono ya está registrado.',
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 10 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear usando DB::statement para evitar problemas con tipos booleanos en PostgreSQL
        $adminId = DB::table('administradores')->insertGetId([
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'rol' => 'admin_sucursal',
            'sucursal_id' => $request->sucursal_id,
            'activo' => DB::raw('TRUE'),
            'intentos_fallidos' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = Administrador::find($adminId);

        Log::info('Administrador de sucursal creado', [
            'admin_id' => $admin->id,
            'email' => $admin->email,
            'sucursal_id' => $admin->sucursal_id,
            'creado_por' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Administrador {$admin->nombre_completo} creado exitosamente.");
    }

    public function edit(int $id)
    {
        $administrador = Administrador::adminsSucursal()->findOrFail($id);
        $sucursales = Sucursal::orderBy('nombre')->get();
        return view('admin.usuarios.edit', compact('administrador', 'sucursales'));
    }

    public function update(Request $request, int $id)
    {
        $administrador = Administrador::adminsSucursal()->findOrFail($id);

        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('administradores')->ignore($administrador->id)],
            'telefono' => ['required', 'string', 'max:15', Rule::unique('administradores')->ignore($administrador->id)],
            'sucursal_id' => 'required|exists:sucursales,id',
            'password' => 'nullable|string|min:10|confirmed',
        ]);

        $datos = $request->only(['nombres', 'apellido_paterno', 'apellido_materno', 'email', 'telefono', 'sucursal_id']);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        DB::table('administradores')
            ->where('id', $id)
            ->update(array_merge($datos, ['updated_at' => now()]));

        $administrador->refresh();

        Log::info('Administrador de sucursal actualizado', [
            'admin_id' => $administrador->id,
            'actualizado_por' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Administrador {$administrador->nombre_completo} actualizado exitosamente.");
    }

    public function toggleActive(int $id)
    {
        $administrador = Administrador::adminsSucursal()->findOrFail($id);
        $nuevoEstado = !$administrador->activo;
        \Illuminate\Support\Facades\DB::statement(
            'UPDATE administradores SET activo = ?, updated_at = NOW() WHERE id = ?',
            [$nuevoEstado ? 'TRUE' : 'FALSE', $id]
        );
        $administrador->refresh();

        $estado = $administrador->activo ? 'activado' : 'desactivado';

        Log::info("Administrador {$estado}", [
            'admin_id' => $administrador->id,
            'por' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Administrador {$administrador->nombre_completo} {$estado}.");
    }

    public function resetPassword(int $id)
    {
        $administrador = Administrador::adminsSucursal()->findOrFail($id);

        $newPassword = Str::random(12);
        $administrador->update([
            'password' => $newPassword,
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);

        Log::info('Contraseña restablecida para administrador', [
            'admin_id' => $administrador->id,
            'por' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Contraseña restablecida para {$administrador->nombre_completo}. Nueva contraseña temporal: {$newPassword}");
    }
}
