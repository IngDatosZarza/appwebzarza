<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\CodigoPostal;
use App\Models\Direccion;
use App\Models\Usuario;
use App\Services\OppenApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminClienteRegistroController extends Controller
{
    public function showForm()
    {
        return view('admin.clientes.registrar');
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuarios',
            'email_confirmation' => 'required|email|same:email',
            'telefono' => [
                'required',
                'string',
                'regex:/^\+52[0-9]{10}$/',
                'unique:usuarios'
            ],
            'fecha_nacimiento' => [
                'required',
                'date',
                'before:-18 years',
            ],
            'rfc' => [
                'nullable',
                'string',
                'regex:/^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/',
                'size:13',
                'unique:usuarios'
            ],
            'password' => 'required|string|min:8|confirmed',
            'estado' => 'required|string',
            'municipio' => 'required|string',
            'codigo_postal_id' => 'required|exists:codigos_postales,id',
            'colonia' => 'required|string',
            'calle' => 'nullable|string|max:200',
            'numero' => 'nullable|string|max:20',
            'genero' => 'nullable|in:masculino,femenino,otro',
            'promo_email' => 'nullable|boolean',
            'promo_whatsapp' => 'nullable|boolean',
        ], [
            'nombres.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'email_confirmation.same' => 'Los correos electrónicos no coinciden.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe tener formato: +52 seguido de 10 dígitos.',
            'telefono.unique' => 'Este número de teléfono ya está registrado.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before' => 'El cliente debe ser mayor de 18 años.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.size' => 'El RFC debe tener 13 caracteres.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'estado.required' => 'El estado es obligatorio.',
            'municipio.required' => 'El municipio es obligatorio.',
            'colonia.required' => 'La colonia es obligatoria.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $oppenService = new OppenApiService();

            $rfcAutoGenerado = OppenApiService::calcularRFC(
                $request->nombres,
                $request->apellido_paterno,
                $request->apellido_materno,
                $request->fecha_nacimiento
            );

            $cpData = CodigoPostal::find($request->codigo_postal_id);
            if (!$cpData) {
                throw new \Exception("Código postal no encontrado");
            }

            // Oppen integration
            $datosCliente = [
                'nombres'          => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email'            => $request->email,
                'telefono'         => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'rfc'              => $rfcAutoGenerado,
                'genero'           => $request->genero,
                'estado'           => $cpData->estado,
                'municipio'        => $cpData->municipio,
                'colonia'          => $request->colonia,
                'calle'            => $request->calle ?? 'Sin especificar',
                'promo_email'      => (bool)($request->promo_email ?? false),
                'promo_whatsapp'   => (bool)($request->promo_whatsapp ?? false),
            ];

            $oppenCustomerCode = null;
            $clienteEnOppen = $oppenService->verificarClienteExistente($request->email, $request->telefono, $rfcAutoGenerado);

            if ($clienteEnOppen) {
                $oppenCustomerCode = $clienteEnOppen['code'];
            } else {
                $resultadoOppen = $oppenService->crearCliente($datosCliente);
                if ($resultadoOppen['success']) {
                    $oppenCustomerCode = $resultadoOppen['code'];
                } else {
                    Log::warning('Admin nuevo panel: No se pudo crear cliente en Oppen', [
                        'email' => $request->email,
                        'error' => $resultadoOppen['error'] ?? 'Error desconocido',
                    ]);
                }
            }

            $userAgent = $request->userAgent();
            $dispositivo = 'desktop';
            if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $userAgent)) {
                $dispositivo = 'mobile';
            } elseif (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                $dispositivo = 'tablet';
            }

            DB::beginTransaction();

            $promoEmail  = (bool)($request->promo_email  ?? false);
            $promoWa     = (bool)($request->promo_whatsapp ?? false);
            $origenReg   = $request->campana_id ? 'campana' : 'admin_sucursal';
            $sucursalId  = $admin->esAdminSucursal() ? $admin->sucursal_id : null;

            // Insertar con booleanos nativos de PostgreSQL usando DB::statement
            DB::statement("
                INSERT INTO usuarios (
                    nombres, apellido_paterno, apellido_materno, email, telefono,
                    fecha_nacimiento, rfc, password, genero, rol,
                    club_zarza, oppen_customer_id, origen_registro, dispositivo_registro,
                    registrado_por_admin_id, registrado_por_administrador_id, sucursal_registro_id,
                    campana_id, user_agent, ip_registro,
                    promo_email, promo_whatsapp,
                    created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    TRUE, ?, ?, ?,
                    NULL, ?, ?,
                    ?, ?, ?,
                    " . ($promoEmail  ? 'TRUE' : 'FALSE') . ",
                    " . ($promoWa     ? 'TRUE' : 'FALSE') . ",
                    NOW(), NOW()
                )
            ", [
                $request->nombres, $request->apellido_paterno, $request->apellido_materno,
                $request->email, $request->telefono,
                $request->fecha_nacimiento, $rfcAutoGenerado, Hash::make($request->password),
                $request->genero, 'cliente',
                $oppenCustomerCode, $origenReg, $dispositivo,
                $admin->id, $sucursalId,
                $request->campana_id, $userAgent, $request->ip(),
            ]);

            $usuario = Usuario::where('email', $request->email)->first();

            Direccion::create([
                'usuario_id' => $usuario->id,
                'calle' => $request->calle ?? 'Sin especificar',
                'numero' => $request->numero ?? 'S/N',
                'codigo_postal_id' => $request->codigo_postal_id,
                'codigo_postal' => $cpData->codigo_postal,
                'estado' => $cpData->estado,
                'municipio' => $cpData->municipio,
                'colonia' => $request->colonia,
                'pais' => 'México',
                'principal' => 't'
            ]);

            Auditoria::create([
                'tabla' => 'usuarios',
                'registro_id' => $usuario->id,
                'accion' => 'create',
                'usuario_id' => $usuario->id,
                'cambios' => json_encode([
                    'accion' => 'registro_cliente_por_admin_panel_nuevo',
                    'cliente_email' => $request->email,
                    'rfc_autogenerado' => $rfcAutoGenerado,
                    'oppen_customer_code' => $oppenCustomerCode,
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'admin_rol' => $admin->rol,
                    'sucursal_id' => $admin->sucursal_id,
                    'ip' => $request->ip(),
                ]),
                'fecha' => now(),
            ]);

            DB::commit();

            Log::info("Cliente creado por admin (panel nuevo)", [
                'cliente_id' => $usuario->id,
                'cliente_email' => $request->email,
                'admin_id' => $admin->id,
                'admin_rol' => $admin->rol,
                'sucursal_id' => $admin->sucursal_id,
            ]);

            // Enviar correo de bienvenida
            try {
                Mail::to($usuario->email)->send(new \App\Mail\WelcomeMail($usuario));
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar correo de bienvenida', [
                    'cliente_id' => $usuario->id,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('admin.clientes.registrar')
                ->with('success', "Cliente registrado exitosamente. Email: {$request->email} - ID: {$usuario->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en registro de cliente por admin (panel nuevo)', [
                'exception' => $e->getMessage(),
                'admin_id' => $admin->id,
            ]);
            return back()->withErrors([
                'email' => 'Error al procesar el registro: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
