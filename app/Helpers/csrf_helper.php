<?php
/**
 * Helpers personalizados para el manejo de CSRF
 */

if (!function_exists('csrf_token')) {
    /**
     * Generar token CSRF - Compatible con Laravel
     */
    function csrf_token() {
        // Intentar usar Laravel session primero
        if (function_exists('session')) {
            try {
                $sessionToken = session('_token');
                if (!$sessionToken) {
                    $sessionToken = bin2hex(random_bytes(32));
                    session(['_token' => $sessionToken]);
                }
                return $sessionToken;
            } catch (Exception $e) {
                // Fall through to PHP session
            }
        }
        
        // Fallback a sesión PHP nativa
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generar campo oculto con token CSRF
     */
    function csrf_field() {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_check')) {
    /**
     * Verificar token CSRF - Compatible con Laravel
     */
    function csrf_check($token) {
        // Intentar usar Laravel session primero
        if (function_exists('session')) {
            try {
                $sessionToken = session('_token');
                if ($sessionToken && hash_equals($sessionToken, $token)) {
                    return true;
                }
            } catch (Exception $e) {
                // Fall through to PHP session
            }
        }
        
        // Fallback a sesión PHP nativa
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }
}

if (!function_exists('old')) {
    /**
     * Obtener valores anteriores del formulario
     */
    function old($key, $default = '') {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('set_old_input')) {
    /**
     * Guardar valores antiguos del formulario
     */
    function set_old_input($data) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['_old_input'] = $data;
    }
}

if (!function_exists('get_errors')) {
    /**
     * Obtener errores de validación
     */
    function get_errors() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['errors'] ?? [];
    }
}

if (!function_exists('has_error')) {
    /**
     * Verificar si existe un error para un campo específico
     */
    function has_error($field) {
        $errors = get_errors();
        return isset($errors[$field]);
    }
}

if (!function_exists('get_error')) {
    /**
     * Obtener el primer error de un campo específico
     */
    function get_error($field) {
        $errors = get_errors();
        if (isset($errors[$field])) {
            return is_array($errors[$field]) ? $errors[$field][0] : $errors[$field];
        }
        return '';
    }
}

if (!function_exists('set_errors')) {
    /**
     * Guardar errores de validación
     */
    function set_errors($errors) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['errors'] = $errors;
    }
}

// Inicializar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}