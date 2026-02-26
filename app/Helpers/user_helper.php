<?php
/**
 * Helper para funciones de usuario en vistas de Laravel
 */

if (!function_exists('isAuthenticated')) {
    function isAuthenticated() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isAuthenticated()) return null;
        
        return (object) [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? '',
            'nombre' => $_SESSION['user_nombre'] ?? '',
            'rol' => $_SESSION['user_rol'] ?? 'cliente',
            'puntos' => $_SESSION['user_puntos'] ?? 0,
        ];
    }
}

if (!function_exists('requireAuth')) {
    function requireAuth() {
        if (!isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        $user = getCurrentUser();
        return $user && $user->rol === 'admin';
    }
}

if (!function_exists('isClient')) {
    function isClient() {
        $user = getCurrentUser();
        return $user && $user->rol === 'cliente';
    }
}

// Inicializar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}