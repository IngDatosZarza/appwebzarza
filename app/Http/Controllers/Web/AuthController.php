<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Direccion;
use App\Models\CodigoPostal;
use App\Models\Auditoria;
use App\Services\OppenApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Procesar login del usuario
     */
    public function login(Request $request)
    {
        // Laravel maneja CSRF automáticamente a través del middleware VerifyCsrfToken
        // No necesitamos verificación manual aquí
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Laravel maneja las sesiones automáticamente
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Buscar usuario por email usando Eloquent
            $usuario = Usuario::where('email', $request->email)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
                ])->withInput()->with('error', '❌ Credenciales incorrectas. Por favor, verifica tu email y contraseña.');
            }

            // Actualizar marca de tiempo de actividad
            $usuario->touch();

            // Regenerar la sesión para evitar fixation
            Session::regenerate();

            // Autenticar al usuario en el guard de Laravel
            Auth::login($usuario);

            // Crear sesión manual para compatibilidad
            Session::put('user_authenticated', true);
            Session::put('user_id', $usuario->id);
            Session::put('user_email', $usuario->email);
            Session::put('user_nombre', $usuario->nombres);
            Session::put('user_apellido', $usuario->apellido_paterno);
            Session::put('user_rol', $usuario->rol);

            // Redirigir según el rol del usuario
            if ($usuario->rol === 'admin') {
                return redirect()->route('admin.coupons.index')->with('success', '✅ ¡Bienvenido al Panel de Administración, ' . $usuario->nombres . '!');
            } else {
                return redirect()->intended('/')->with('success', '✅ ¡Bienvenido de vuelta, ' . $usuario->nombres . '!');
            }

        } catch (\Exception $e) {
            Log::error('Error en login de usuario', [
                'email' => $request->email,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('login')->withErrors([
                'email' => 'Error interno del servidor. Por favor, inténtalo más tarde.'
            ])->withInput()->with('error', '❌ Ocurrió un error interno. Por favor, inténtalo nuevamente.');
        }
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegister()
    {
        // Laravel maneja las sesiones automáticamente
        return view('auth.register');
    }

    /**
     * Procesar registro del usuario
     */
    public function register(Request $request)
    {
        // LOG DE DEPURACIÓN
        error_log("=== INICIO REGISTRO ===");
        error_log("Método: " . $request->method());
        error_log("URL: " . $request->url());
        error_log("IP: " . $request->ip());
        error_log("Datos recibidos: " . json_encode($request->except(['password', 'password_confirmation', '_token'])));
        
        // Laravel maneja CSRF automáticamente a través del middleware VerifyCsrfToken
        // No necesitamos verificación manual aquí
        
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
            
            // Datos de dirección
            'estado' => 'required|string',
            'municipio' => 'required|string',
            'codigo_postal_id' => 'required|exists:codigos_postales,id',
            'colonia' => 'required|string',
            'calle' => 'nullable|string|max:200',
            'numero' => 'nullable|string|max:20',

            // Campos opcionales
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
            'fecha_nacimiento.before' => 'Debes ser mayor de 18 años para registrarte.',
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
            error_log("=== ERRORES DE VALIDACIÓN ===");
            error_log(json_encode($validator->errors()->toArray()));
            return back()->withErrors($validator)->withInput();
        }

        error_log("=== VALIDACIÓN EXITOSA ===");

        try {
            $oppenService = new OppenApiService();

            // Auto-generar RFC a partir de nombre, apellidos y fecha de nacimiento
            $rfcAutoGenerado = OppenApiService::calcularRFC(
                $request->nombres,
                $request->apellido_paterno,
                $request->apellido_materno,
                $request->fecha_nacimiento
            );

            error_log("RFC Auto-generado: {$rfcAutoGenerado}");

            // Obtener datos del código postal para dirección
            $cpData = CodigoPostal::find($request->codigo_postal_id);

            // Preparar datos para la API Oppen y la BD local
            $datosCliente = [
                'nombres'          => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email'            => $request->email,
                'telefono'         => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'rfc'              => $rfcAutoGenerado,
                'genero'           => $request->genero,
                'estado'           => $cpData->estado ?? $request->estado,
                'municipio'        => $cpData->municipio ?? $request->municipio,
                'colonia'          => $request->colonia,
                'calle'            => $request->calle ?? 'Sin especificar',
                'promo_email'      => $request->boolean('promo_email'),
                'promo_whatsapp'   => $request->boolean('promo_whatsapp'),
            ];

            // 1) Verificar si el cliente ya existe en el ERP Oppen
            $clienteEnOppen = $oppenService->verificarClienteExistente(
                $request->email,
                $request->telefono,
                $rfcAutoGenerado
            );

            // También verificar en la BD local
            $clienteLocal = Usuario::where('email', $request->email)
                ->orWhere('telefono', $request->telefono)
                ->first();

            if ($clienteLocal && $clienteLocal->club_zarza) {
                return back()->withErrors([
                    'email' => 'Ya estás registrado en Club Zarza. Por favor, inicia sesión.'
                ])->withInput()->with('error', '❌ Este cliente ya pertenece a Club Zarza.');
            }

            $oppenCustomerCode = null;

            if ($clienteEnOppen) {
                // Cliente ya existe en Oppen, usar su código
                $oppenCustomerCode = $clienteEnOppen['code'];
                error_log("Cliente encontrado en Oppen: {$oppenCustomerCode} (por {$clienteEnOppen['por']})");
            } else {
                // 2) Crear cliente nuevo en la API Oppen
                $resultadoOppen = $oppenService->crearCliente($datosCliente);

                if ($resultadoOppen['success']) {
                    $oppenCustomerCode = $resultadoOppen['code'];
                    error_log("Cliente creado en Oppen: {$oppenCustomerCode}");
                } else {
                    // Registrar el error pero continuar con el registro local
                    Log::warning('No se pudo crear cliente en Oppen, se continuará solo con registro local', [
                        'email' => $request->email,
                        'error' => $resultadoOppen['error'] ?? 'Error desconocido',
                    ]);
                    error_log("Error creando cliente en Oppen: " . ($resultadoOppen['error'] ?? 'Error desconocido'));
                }
            }

            // 3) Crear usuario en la base de datos local
            DB::beginTransaction();

            // Detectar tipo de dispositivo basado en User-Agent
            $userAgent = $request->userAgent();
            $dispositivo = 'desktop'; // Por defecto
            if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $userAgent)) {
                $dispositivo = 'mobile';
            } elseif (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                $dispositivo = 'tablet';
            }

            $usuario = Usuario::create([
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'rfc' => $rfcAutoGenerado,
                'password' => Hash::make($request->password),
                'genero' => $request->genero,
                'promo_email' => $request->boolean('promo_email') ? 't' : 'f',
                'promo_whatsapp' => $request->boolean('promo_whatsapp') ? 't' : 'f',
                'rol' => 'cliente',
                'club_zarza' => 't',
                'oppen_customer_id' => $oppenCustomerCode,
                // Campos de tracking
                'origen_registro' => 'autoregistro',
                'dispositivo_registro' => $dispositivo,
                'user_agent' => $userAgent,
                'ip_registro' => $request->ip(),
            ]);

            // Crear dirección (usar valores por defecto si no se proporcionan calle/numero)
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

            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'usuarios',
                'registro_id' => $usuario->id,
                'accion' => 'create',
                'usuario_id' => $usuario->id,
                'cambios' => json_encode([
                    'accion' => 'registro_usuario',
                    'email' => $request->email,
                    'rfc_autogenerado' => $rfcAutoGenerado,
                    'oppen_customer_code' => $oppenCustomerCode,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
            ]);

            DB::commit();

            error_log("=== USUARIO CREADO EXITOSAMENTE ===");
            error_log("Usuario ID: {$usuario->id}");
            error_log("Email: " . $request->email);
            error_log("RFC: {$rfcAutoGenerado}");
            error_log("Oppen Code: " . ($oppenCustomerCode ?? 'N/A'));

            // Enviar correo de bienvenida
            try {
                Mail::to($usuario->email)->send(new WelcomeMail($usuario));
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar el correo de bienvenida', [
                    'user_id' => $usuario->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Crear notificación de bienvenida
            try {
                $notificationController = new \App\Http\Controllers\Web\NotificationController();
                $notificationController->notifyWelcome($usuario->id, $request->nombres);
            } catch (\Exception $e) {
                error_log("Error creating welcome notification: " . $e->getMessage());
            }

            error_log("=== REDIRIGIENDO A LOGIN ===");
            
            return redirect('/login')->with('success', '¡Cuenta creada exitosamente! Por favor, verifica tu correo electrónico antes de iniciar sesión.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en registro de usuario', [
                'email' => $request->email,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->withErrors([
                'email' => 'Error al crear la cuenta. Por favor, inténtalo más tarde.'
            ])->withInput();
        }
    }

    /**
     * Verificar si un cliente existe en el sistema Oppen
     * 
     * @param string $email
     * @param string $telefono
     * @param string $rfc
     * @return array|null
     */
    private function verificarClienteEnOppen($email, $telefono, $rfc)
    {
        // TODO: Implementar integración real con API de Oppen
        // Por ahora, simularemos la búsqueda usando Eloquent
        
        try {
            // Buscar si existe un usuario con el mismo email, teléfono o RFC
            $cliente = Usuario::where('email', $email)
                ->orWhere('telefono', $telefono)
                ->orWhere('rfc', strtoupper($rfc))
                ->first();
            
            if ($cliente) {
                return [
                    'existe' => true,
                    'tiene_club_zarza' => (bool)$cliente->club_zarza,
                    'oppen_id' => $cliente->oppen_customer_id ?? $cliente->id
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error verificando cliente en Oppen', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Actualizar cliente existente en Oppen para agregarlo a Club Zarza
     */
    private function actualizarClienteOppen(Request $request, $oppenId)
    {
        try {
            DB::beginTransaction();
            
            // Actualizar flag de club_zarza
            Usuario::where('oppen_customer_id', $oppenId)
                ->orWhere('id', $oppenId)
                ->update([
                    'club_zarza' => 't',
                    'password' => Hash::make($request->password)
                ]);
            
            DB::commit();
            
            return redirect('/login')->with('success', '¡Bienvenido a Club Zarza! Tu cuenta ha sido activada. Por favor, inicia sesión.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error actualizando cliente Oppen', ['error' => $e->getMessage()]);
            
            return back()->withErrors([
                'email' => 'Error al procesar tu registro. Por favor, contacta al soporte.'
            ])->withInput();
        }
    }

    /**
     * Cerrar sesión del usuario
     */
    public function logout(Request $request)
    {
        // Cerrar sesión del guard de Laravel si está autenticado
        if (Auth::check()) {
            Auth::logout();
        }

        // Invalidar y regenerar la sesión para limpiar datos
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Limpiar variables personalizadas
        Session::forget(['user_authenticated', 'user_id', 'user_email', 'user_nombre', 'user_apellido', 'user_rol']);

        return redirect('/')->with('success', '✅ Has cerrado sesión correctamente. ¡Hasta pronto!');
    }

    /**
     * Middleware personalizado para verificar autenticación
     */
    public static function checkAuth()
    {
        return Session::get('user_authenticated', false);
    }

    /**
     * Obtener datos del usuario autenticado
     */
    public static function user()
    {
        if (!self::checkAuth()) {
            return null;
        }

        return (object) [
            'id' => Session::get('user_id'),
            'email' => Session::get('user_email'),
            'nombre' => Session::get('user_nombre'),
            'rol' => Session::get('user_rol'),
        ];
    }

    /**
     * Mostrar formulario de registro de cliente (Admin)
     */
    public function showAdminClientRegister()
    {
        // Verificar que el usuario autenticado sea admin
        if (Session::get('user_rol') !== 'admin') {
            return redirect()->route('dashboard')->with('error', '❌ No tienes permisos para acceder a esta sección.');
        }

        return view('admin.clients.create');
    }

    /**
     * Registrar cliente desde el panel de administración
     */
    public function adminRegisterClient(Request $request)
    {
        // Verificar que el usuario autenticado sea admin
        if (Session::get('user_rol') !== 'admin') {
            return redirect()->route('dashboard')->with('error', '❌ No tienes permisos para realizar esta acción.');
        }

        // LOG DE DEPURACIÓN
        Log::info("=== INICIO REGISTRO DE CLIENTE POR ADMIN ===", [
            'admin_id' => Session::get('user_id'),
            'admin_email' => Session::get('user_email'),
            'datos_cliente' => $request->except(['password', 'password_confirmation', '_token'])
        ]);
        
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
            
            // Datos de dirección
            'estado' => 'required|string',
            'municipio' => 'required|string',
            'codigo_postal_id' => 'required|exists:codigos_postales,id',
            'colonia' => 'required|string',
            'calle' => 'nullable|string|max:200',
            'numero' => 'nullable|string|max:20',

            // Campos opcionales
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
            Log::warning("Errores de validación en registro por admin", [
                'errores' => $validator->errors()->toArray()
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            $oppenService = new OppenApiService();

            // Auto-generar RFC
            $rfcAutoGenerado = OppenApiService::calcularRFC(
                $request->nombres,
                $request->apellido_paterno,
                $request->apellido_materno,
                $request->fecha_nacimiento
            );

            // Obtener datos del código postal
            $cpDataAdmin = CodigoPostal::find($request->codigo_postal_id);

            if (!$cpDataAdmin) {
                throw new \Exception("Código postal no encontrado");
            }

            // Preparar datos para API Oppen (incluyendo preferencias de marketing)
            $datosCliente = [
                'nombres'          => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email'            => $request->email,
                'telefono'         => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'rfc'              => $rfcAutoGenerado,
                'genero'           => $request->genero,
                'estado'           => $cpDataAdmin->estado,
                'municipio'        => $cpDataAdmin->municipio,
                'colonia'          => $request->colonia,
                'calle'            => $request->calle ?? 'Sin especificar',
                'promo_email'      => (bool)($request->promo_email ?? false),
                'promo_whatsapp'   => (bool)($request->promo_whatsapp ?? false),
                // Nota: campana_id es solo para tracking interno, no se envía a Oppen
            ];

            // Crear cliente en API Oppen
            $oppenCustomerCode = null;
            $clienteEnOppen = $oppenService->verificarClienteExistente($request->email, $request->telefono, $rfcAutoGenerado);

            if ($clienteEnOppen) {
                $oppenCustomerCode = $clienteEnOppen['code'];
            } else {
                $resultadoOppen = $oppenService->crearCliente($datosCliente);
                if ($resultadoOppen['success']) {
                    $oppenCustomerCode = $resultadoOppen['code'];
                } else {
                    Log::warning('Admin: No se pudo crear cliente en Oppen', [
                        'email' => $request->email,
                        'error' => $resultadoOppen['error'] ?? 'Error desconocido',
                    ]);
                }
            }

            // Detectar tipo de dispositivo basado en User-Agent
            $userAgent = $request->userAgent();
            $dispositivo = 'desktop'; // Por defecto
            if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|iemobile/i', $userAgent)) {
                $dispositivo = 'mobile';
            } elseif (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
                $dispositivo = 'tablet';
            }

            DB::beginTransaction();

            // Preparar datos de usuario con tracking
            $datosUsuario = [
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'rfc' => $rfcAutoGenerado,
                'password' => Hash::make($request->password),
                'genero' => $request->genero,
                'rol' => 'cliente',
                'club_zarza' => 't',
                'oppen_customer_id' => $oppenCustomerCode,
                // Campos de tracking
                'origen_registro' => $request->campana_id ? 'campana' : 'admin_sucursal',
                'dispositivo_registro' => $dispositivo,
                'registrado_por_admin_id' => Session::get('user_id'),
                'campana_id' => $request->campana_id,
                'user_agent' => $userAgent,
                'ip_registro' => $request->ip(),
            ];

            // Crear usuario
            $usuario = Usuario::create($datosUsuario);

            // Crear dirección (usar valores por defecto si no se proporcionan calle/numero)
            Direccion::create([
                'usuario_id' => $usuario->id,
                'calle' => $request->calle ?? 'Sin especificar',
                'numero' => $request->numero ?? 'S/N',
                'codigo_postal_id' => $request->codigo_postal_id,
                'codigo_postal' => $cpDataAdmin->codigo_postal,
                'estado' => $cpDataAdmin->estado,
                'municipio' => $cpDataAdmin->municipio,
                'colonia' => $request->colonia,
                'pais' => 'México',
                'principal' => 't'
            ]);

            // Registrar en auditoría
            Auditoria::create([
                'tabla' => 'usuarios',
                'registro_id' => $usuario->id,
                'accion' => 'create',
                'usuario_id' => Session::get('user_id'),
                'cambios' => json_encode([
                    'accion' => 'registro_cliente_por_admin',
                    'cliente_email' => $request->email,
                    'rfc_autogenerado' => $rfcAutoGenerado,
                    'oppen_customer_code' => $oppenCustomerCode,
                    'admin_id' => Session::get('user_id'),
                    'admin_email' => Session::get('user_email'),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
            ]);

            DB::commit();

            Log::info("Cliente creado exitosamente por admin", [
                'cliente_id' => $usuario->id,
                'cliente_email' => $request->email,
                'rfc' => $rfcAutoGenerado,
                'oppen_code' => $oppenCustomerCode,
                'admin_id' => Session::get('user_id')
            ]);

            // Enviar correo de bienvenida
            try {
                Mail::to($usuario->email)->send(new WelcomeMail($usuario));
                Log::info('Correo de bienvenida enviado exitosamente', [
                    'cliente_id' => $usuario->id,
                    'email' => $usuario->email
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar el correo de bienvenida', [
                    'cliente_id' => $usuario->id,
                    'email' => $usuario->email,
                    'error' => $e->getMessage()
                ]);
            }

            return redirect()->route('admin.clients.create')->with('success', 
                '✅ Cliente registrado exitosamente. Email: ' . $request->email . ' - ID: ' . $usuario->id . '. Se ha enviado un correo de bienvenida.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en registro de cliente por admin', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Session::get('user_id')
            ]);
            
            return back()->withErrors([
                'email' => 'Error al procesar el registro del cliente: ' . $e->getMessage()
            ])->withInput();
        }
    }
}