<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Tiempo de expiración del token en minutos */
    private const TOKEN_EXPIRY_MINUTES = 60;

    // -------------------------------------------------------------------------
    // Paso 1 — Mostrar formulario "¿Olvidaste tu contraseña?"
    // -------------------------------------------------------------------------
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // -------------------------------------------------------------------------
    // Paso 2 — Procesar el email y enviar el enlace de restablecimiento
    // -------------------------------------------------------------------------
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'Ingresa un correo electrónico válido.',
        ]);

        // Siempre respondemos con el mismo mensaje para no revelar si el email existe
        $genericMessage = 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña en los próximos minutos.';

        try {
            $usuario = Usuario::where('email', $request->email)->first();

            if ($usuario) {
                // Generar token seguro
                $token = Str::random(64);

                // Guardar (o reemplazar) el token en la tabla password_reset_tokens
                DB::table('password_reset_tokens')->upsert(
                    [
                        'email'      => $usuario->email,
                        'token'      => Hash::make($token),
                        'created_at' => now(),
                    ],
                    ['email'],          // clave única para upsert
                    ['token', 'created_at']
                );

                $resetUrl = url('/restablecer-contrasena/' . $token . '?email=' . urlencode($usuario->email));

                Mail::to($usuario->email)->send(new PasswordResetMail($usuario, $resetUrl));

                Log::info('Password reset email sent', ['email' => $usuario->email]);
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar correo de restablecimiento', [
                'email'     => $request->email,
                'exception' => $e->getMessage(),
            ]);
        }

        return back()->with('success', $genericMessage);
    }

    // -------------------------------------------------------------------------
    // Paso 3 — Mostrar formulario para ingresar la nueva contraseña
    // -------------------------------------------------------------------------
    public function showResetPassword(Request $request, string $token)
    {
        $email = $request->query('email', '');

        return view('auth.reset-password', compact('token', 'email'));
    }

    // -------------------------------------------------------------------------
    // Paso 4 — Procesar la nueva contraseña
    // -------------------------------------------------------------------------
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required|string',
            'email'                 => 'required|email',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Buscar el registro del token
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->with('error', '❌ El enlace de restablecimiento no es válido o ya fue utilizado.');
        }

        // Verificar que no haya expirado
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(self::TOKEN_EXPIRY_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->with('error', '⏰ El enlace de restablecimiento ha expirado. Solicita uno nuevo.');
        }

        // Actualizar contraseña
        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return back()->with('error', '❌ No se encontró una cuenta con ese correo electrónico.');
        }

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        // Eliminar el token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Log::info('Password reset successful', ['email' => $request->email]);

        return redirect()->route('login')
            ->with('success', '✅ Tu contraseña ha sido restablecida. Ahora puedes iniciar sesión.');
    }
}
