<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QaAccessMiddleware
{
    /**
     * Clave de sesión donde se guarda el acceso QA.
     */
    private const SESSION_KEY = 'qa_access_granted';

    /**
     * Nombre de la variable de entorno con la contraseña QA.
     */
    private const ENV_KEY = 'QA_ACCESS_PASSWORD';

    public function handle(Request $request, Closure $next): Response
    {
        // Si QA_ACCESS_PASSWORD no está definido, el modo QA está inactivo
        $qaPassword = env(self::ENV_KEY);
        if (empty($qaPassword)) {
            return $next($request);
        }

        // Permitir la ruta de autenticación QA sin verificación
        if ($request->is('qa-access*')) {
            return $next($request);
        }

        // Verificar si ya tiene acceso en sesión
        if ($request->session()->get(self::SESSION_KEY) === true) {
            return $next($request);
        }

        // Verificar si viene con el token correcto en la URL (?qa_token=xxx)
        if ($request->query('qa_token') === $qaPassword) {
            $request->session()->put(self::SESSION_KEY, true);
            return redirect($request->url());
        }

        // Mostrar pantalla de acceso QA
        return response()->view('qa.access', [
            'redirect' => $request->fullUrl(),
        ], 403);
    }
}
