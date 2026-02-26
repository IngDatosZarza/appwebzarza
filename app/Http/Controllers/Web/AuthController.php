<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

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
            // Buscar usuario por email usando PDO directo
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$request->email]);
            $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($request->password, $usuario['password'])) {
                // Redirigir específicamente al login con mensaje de error
                return redirect()->route('login')->withErrors([
                    'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'
                ])->withInput()->with('error', '❌ Credenciales incorrectas. Por favor, verifica tu email y contraseña.');
            }

            // Actualizar marca de tiempo de actividad
            $updateStmt = $pdo->prepare("UPDATE usuarios SET updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$usuario['id']]);

            // Regenerar la sesión para evitar fixation
            Session::regenerate();

            // Autenticar al usuario en el guard de Laravel para compatibilidad con Auth::check()
            $usuarioModel = Usuario::find($usuario['id']);
            if ($usuarioModel) {
                Auth::login($usuarioModel);
            }

            // Crear sesión manual
            Session::put('user_authenticated', true);
            Session::put('user_id', $usuario['id']);
            Session::put('user_email', $usuario['email']);
            Session::put('user_nombre', $usuario['nombres']);
            Session::put('user_apellido', $usuario['apellido_paterno']);
            Session::put('user_rol', $usuario['rol']);

            // Obtener puntos del usuario
            $puntosStmt = $pdo->prepare("SELECT saldo FROM puntos WHERE usuario_id = ?");
            $puntosStmt->execute([$usuario['id']]);
            $puntos = $puntosStmt->fetch(\PDO::FETCH_ASSOC);
            Session::put('user_puntos', $puntos['saldo'] ?? 0);

            // Redirigir según el rol del usuario
            if ($usuario['rol'] === 'admin') {
                return redirect('/admin/points')->with('success', '✅ ¡Bienvenido al Panel de Administración, ' . $usuario['nombres'] . '!');
            } else {
                return redirect()->intended('/')->with('success', '✅ ¡Bienvenido de vuelta, ' . $usuario['nombres'] . '! Tienes ' . number_format($puntos['saldo'] ?? 0) . ' puntos disponibles.');
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
                'required',
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
            'calle' => 'required|string|max:200',
            'numero' => 'required|string|max:20',
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
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.size' => 'El RFC debe tener 13 caracteres.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'estado.required' => 'El estado es obligatorio.',
            'municipio.required' => 'El municipio es obligatorio.',
            'colonia.required' => 'La colonia es obligatoria.',
            'calle.required' => 'La calle es obligatoria.',
            'numero.required' => 'El número es obligatorio.',
        ]);

        if ($validator->fails()) {
            error_log("=== ERRORES DE VALIDACIÓN ===");
            error_log(json_encode($validator->errors()->toArray()));
            return back()->withErrors($validator)->withInput();
        }

        error_log("=== VALIDACIÓN EXITOSA ===");

        try {
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            // VALIDACIÓN CON SISTEMA OPPEN
            // Verificar si el cliente ya existe en Oppen (simulación - implementar según API de Oppen)
            $clienteExisteEnOppen = $this->verificarClienteEnOppen($request->email, $request->telefono, $request->rfc);
            
            if ($clienteExisteEnOppen) {
                // El cliente existe en Oppen
                if ($clienteExisteEnOppen['tiene_club_zarza']) {
                    // Ya está registrado en Club Zarza
                    return back()->withErrors([
                        'email' => 'Ya estás registrado en Club Zarza. Por favor, inicia sesión.'
                    ])->withInput()->with('error', '❌ Este cliente ya pertenece a Club Zarza.');
                }
                
                // Cliente existe en Oppen pero solo para facturación - vamos a actualizarlo
                return $this->actualizarClienteOppen($request, $clienteExisteEnOppen['oppen_id'], $pdo);
            }
            
            // Cliente nuevo - crear en ambos sistemas
            $pdo->beginTransaction();

            // Crear usuario
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (
                    nombres, apellido_paterno, apellido_materno, 
                    email, telefono, fecha_nacimiento, rfc,
                    password, rol, club_zarza, created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cliente', true, NOW(), NOW())
                RETURNING id
            ");
            
            $stmt->execute([
                $request->nombres,
                $request->apellido_paterno,
                $request->apellido_materno,
                $request->email,
                $request->telefono,
                $request->fecha_nacimiento,
                strtoupper($request->rfc),
                Hash::make($request->password)
            ]);
            
            $userId = $stmt->fetchColumn();

            // Obtener datos del código postal
            $cpStmt = $pdo->prepare("SELECT * FROM codigos_postales WHERE id = ?");
            $cpStmt->execute([$request->codigo_postal_id]);
            $cpData = $cpStmt->fetch(\PDO::FETCH_ASSOC);

            // Crear dirección
            $dirStmt = $pdo->prepare("
                INSERT INTO direcciones (
                    usuario_id, calle, numero, 
                    codigo_postal_id, codigo_postal, estado, municipio, colonia,
                    pais, principal, created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'México', true, NOW(), NOW())
            ");
            $dirStmt->execute([
                $userId,
                $request->calle,
                $request->numero,
                $request->codigo_postal_id,
                $cpData['codigo_postal'],
                $cpData['estado'],
                $cpData['municipio'],
                $request->colonia
            ]);

            // Crear registro de puntos inicial
            $puntosStmt = $pdo->prepare("
                INSERT INTO puntos (usuario_id, saldo, updated_at)
                VALUES (?, 0, NOW())
            ");
            $puntosStmt->execute([$userId]);

            // Registrar en auditoría
            $auditStmt = $pdo->prepare("
                INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, cambios, fecha)
                VALUES ('usuarios', ?, 'create', ?, ?, NOW())
            ");
            $auditStmt->execute([
                $userId,
                $userId,
                json_encode([
                    'accion' => 'registro_usuario', 
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
            ]);

            $pdo->commit();

            error_log("=== USUARIO CREADO EXITOSAMENTE ===");
            error_log("Usuario ID: $userId");
            error_log("Email: " . $request->email);

            // Enviar email de verificación
            $this->enviarEmailVerificacion($userId, $request->email, $request->nombres);

            // Crear notificación de bienvenida
            try {
                $notificationController = new \App\Http\Controllers\Web\NotificationController();
                $notificationController->notifyWelcome($userId, $request->nombres);
            } catch (\Exception $e) {
                error_log("Error creating welcome notification: " . $e->getMessage());
            }

            error_log("=== REDIRIGIENDO A LOGIN ===");
            
            // NO iniciar sesión automáticamente - requiere verificación de email
            return redirect('/login')->with('success', '¡Cuenta creada exitosamente! Por favor, verifica tu correo electrónico antes de iniciar sesión.');

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
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
        // Por ahora, simularemos la búsqueda en una tabla local
        
        try {
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            // Buscar si existe un usuario con el mismo email, teléfono o RFC
            $stmt = $pdo->prepare("
                SELECT id, email, telefono, rfc, club_zarza, oppen_customer_id
                FROM usuarios
                WHERE email = ? OR telefono = ? OR rfc = ?
                LIMIT 1
            ");
            $stmt->execute([$email, $telefono, strtoupper($rfc)]);
            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($cliente) {
                return [
                    'existe' => true,
                    'tiene_club_zarza' => (bool)$cliente['club_zarza'],
                    'oppen_id' => $cliente['oppen_customer_id'] ?? $cliente['id']
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
    private function actualizarClienteOppen(Request $request, $oppenId, $pdo)
    {
        try {
            $pdo->beginTransaction();
            
            // Actualizar flag de club_zarza
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET club_zarza = true, 
                    password = ?,
                    updated_at = NOW()
                WHERE oppen_customer_id = ? OR id = ?
            ");
            $stmt->execute([
                Hash::make($request->password),
                $oppenId,
                $oppenId
            ]);
            
            $pdo->commit();
            
            return redirect('/login')->with('success', '¡Bienvenido a Club Zarza! Tu cuenta ha sido activada. Por favor, inicia sesión.');
            
        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            Log::error('Error actualizando cliente Oppen', ['error' => $e->getMessage()]);
            
            return back()->withErrors([
                'email' => 'Error al procesar tu registro. Por favor, contacta al soporte.'
            ])->withInput();
        }
    }

    /**
     * Enviar email de verificación al usuario
     */
    private function enviarEmailVerificacion($userId, $email, $nombre)
    {
        // TODO: Implementar envío de email real
        // Por ahora, solo registrar en log
        
        $token = bin2hex(random_bytes(32));
        
        // Guardar token en base de datos (temporal)
        try {
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            // Aquí deberías guardar el token en una tabla de verificaciones
            // Por ahora, lo registramos en el log
            
            Log::info('Email de verificación generado', [
                'user_id' => $userId,
                'email' => $email,
                'token' => $token,
                'verification_url' => url("/verify-email/{$userId}/{$token}")
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error guardando token de verificación', ['error' => $e->getMessage()]);
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
        Session::forget(['user_authenticated', 'user_id', 'user_email', 'user_nombre', 'user_apellido', 'user_rol', 'user_puntos']);

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
            'puntos' => Session::get('user_puntos'),
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
                'required',
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
            'calle' => 'required|string|max:200',
            'numero' => 'required|string|max:20',
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
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.regex' => 'El RFC no tiene un formato válido.',
            'rfc.size' => 'El RFC debe tener 13 caracteres.',
            'rfc.unique' => 'Este RFC ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'estado.required' => 'El estado es obligatorio.',
            'municipio.required' => 'El municipio es obligatorio.',
            'colonia.required' => 'La colonia es obligatoria.',
            'calle.required' => 'La calle es obligatoria.',
            'numero.required' => 'El número es obligatorio.',
        ]);

        if ($validator->fails()) {
            Log::warning("Errores de validación en registro por admin", [
                'errores' => $validator->errors()->toArray()
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            $pdo->beginTransaction();

            // Crear usuario
            $stmt = $pdo->prepare("
                INSERT INTO usuarios (
                    nombres, apellido_paterno, apellido_materno, 
                    email, telefono, fecha_nacimiento, rfc,
                    password, rol, club_zarza, created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cliente', true, NOW(), NOW())
                RETURNING id
            ");
            
            $stmt->execute([
                $request->nombres,
                $request->apellido_paterno,
                $request->apellido_materno,
                $request->email,
                $request->telefono,
                $request->fecha_nacimiento,
                strtoupper($request->rfc),
                Hash::make($request->password)
            ]);
            
            $userId = $stmt->fetchColumn();

            // Obtener datos del código postal
            $cpStmt = $pdo->prepare("SELECT * FROM codigos_postales WHERE id = ?");
            $cpStmt->execute([$request->codigo_postal_id]);
            $cpData = $cpStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$cpData) {
                throw new \Exception("Código postal no encontrado");
            }

            // Crear dirección
            $dirStmt = $pdo->prepare("
                INSERT INTO direcciones (
                    usuario_id, calle, numero, 
                    codigo_postal_id, codigo_postal, estado, municipio, colonia,
                    pais, principal, created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'México', true, NOW(), NOW())
            ");
            $dirStmt->execute([
                $userId,
                $request->calle,
                $request->numero,
                $request->codigo_postal_id,
                $cpData['codigo_postal'],
                $cpData['estado'],
                $cpData['municipio'],
                $request->colonia
            ]);

            // Crear registro de puntos inicial
            $puntosStmt = $pdo->prepare("
                INSERT INTO puntos (usuario_id, saldo, updated_at)
                VALUES (?, 0, NOW())
            ");
            $puntosStmt->execute([$userId]);

            // Registrar en auditoría
            $auditStmt = $pdo->prepare("
                INSERT INTO auditoria (tabla, registro_id, accion, usuario_id, cambios, fecha)
                VALUES ('usuarios', ?, 'create', ?, ?, NOW())
            ");
            $auditStmt->execute([
                $userId,
                Session::get('user_id'), // ID del admin que está creando el cliente
                json_encode([
                    'accion' => 'registro_cliente_por_admin',
                    'cliente_email' => $request->email,
                    'admin_id' => Session::get('user_id'),
                    'admin_email' => Session::get('user_email'),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
            ]);

            $pdo->commit();

            Log::info("Cliente creado exitosamente por admin", [
                'cliente_id' => $userId,
                'cliente_email' => $request->email,
                'admin_id' => Session::get('user_id')
            ]);

            return redirect()->route('admin.clients.create')->with('success', 
                '✅ Cliente registrado exitosamente. Email: ' . $request->email . ' - ID: ' . $userId);

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
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