<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\BranchesController;
use App\Http\Controllers\Web\DireccionController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminCouponsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUsuariosController;
use App\Http\Controllers\Admin\AdminSucursalController;
use App\Http\Controllers\Admin\AdminClientesController;
use App\Http\Controllers\Admin\AdminClienteRegistroController;
use App\Http\Controllers\Admin\AdminPromosOppenController;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Página principal
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Sucursales (público)
Route::get('/sucursales', [BranchesController::class, 'index'])->name('branches.index');

// Guía de acceso (pública)
Route::get('/guia', function () {
    return view('guia-acceso');
});

// Ruta de prueba de perfil (temporal)
Route::get('/test-profile', function () {
    if (!Session::get('user_authenticated', false)) {
        return 'Usuario no autenticado';
    }
    return 'Usuario autenticado: ' . Session::get('user_nombre', 'N/A') . ' - ID: ' . Session::get('user_id', 'N/A');
});

// Rutas públicas
// (Cupones/Promociones se manejan más abajo con CouponsController)

// Rutas que requieren autenticación
Route::middleware('custom.auth')->group(function () {
    
    // Perfil del usuario
    Route::get('/perfil', [DashboardController::class, 'profile'])->name('profile.show');
    Route::put('/perfil', [DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Dirección del usuario
    Route::get('/api/direccion/principal', [DireccionController::class, 'getDireccionPrincipal'])->name('direccion.principal');
    Route::put('/perfil/direccion', [DireccionController::class, 'updateDireccionPrincipal'])->name('direccion.update');
    
    // Cupones del usuario (legacy - desactivado, ahora son promos Oppen)
    // Route::get('/mis-cupones', [DashboardController::class, 'myCoupons'])->name('coupons.my');
    // Route::post('/cupones/canjear', [DashboardController::class, 'redeemCoupon'])->name('coupons.redeem');
    // Route::post('/cupones/obtener', [DashboardController::class, 'assignCouponWithGamification'])->name('coupons.assign');
    // Route::post('/cupones/verificar-desbloqueos', [DashboardController::class, 'unlockMyBlockedCoupons'])->name('coupons.unlock');
    
    // Compras
    Route::get('/compras', [DashboardController::class, 'purchases'])->name('purchases.index');
    
});

// Rutas de autenticación
Route::get('/login', [\App\Http\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Web\AuthController::class, 'login'])->name('login.post');

Route::get('/register', [\App\Http\Controllers\Web\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [\App\Http\Controllers\Web\AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [\App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout');
Route::get('/logout', function () {
    return redirect('/');
});

// Rutas de recuperación de contraseña
Route::get('/olvide-contrasena', [\App\Http\Controllers\Web\PasswordResetController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/olvide-contrasena', [\App\Http\Controllers\Web\PasswordResetController::class, 'sendResetLink'])->name('password.send-link');
Route::get('/restablecer-contrasena/{token}', [\App\Http\Controllers\Web\PasswordResetController::class, 'showResetPassword'])->name('password.reset.form');
Route::post('/restablecer-contrasena', [\App\Http\Controllers\Web\PasswordResetController::class, 'resetPassword'])->name('password.reset');

// Rutas de transacciones
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::get('/purchase', [TransactionController::class, 'showPurchaseForm'])->name('purchase.form');
Route::post('/purchase', [TransactionController::class, 'storePurchase'])->name('purchase.store');

// Rutas de tickets (deshabilitadas - no incluidas en primera etapa)
// Route::middleware('custom.auth')->group(function () {
//     Route::get('/tickets', [\App\Http\Controllers\Web\TicketController::class, 'index'])->name('tickets.index');
//     Route::get('/tickets/create', [\App\Http\Controllers\Web\TicketController::class, 'create'])->name('tickets.create');
//     Route::post('/tickets', [\App\Http\Controllers\Web\TicketController::class, 'store'])->name('tickets.store');
//     Route::get('/tickets/{id}', [\App\Http\Controllers\Web\TicketController::class, 'show'])->name('tickets.show');
//     Route::get('/tickets/check-ticket', [\App\Http\Controllers\Web\TicketController::class, 'checkTicket'])->name('tickets.check');
// });

// Rutas de promociones para clientes (antes cupones)
Route::get('/cupones', [\App\Http\Controllers\Web\CouponsController::class, 'myCoupons'])->name('coupons.index');
// Rutas legacy de canje desactivadas (ahora promos se aplican en POS via Oppen)
// Route::post('/cupones/{id}/canjear', [\App\Http\Controllers\Web\CouponsController::class, 'redeem'])->name('coupons.redeem');
// Route::get('/cupones/{id}', [\App\Http\Controllers\Web\CouponsController::class, 'show'])->name('coupons.show');

// Rutas de administración de cupones LEGACY (desactivado - ahora se usan Promociones Oppen)
// Route::middleware(['custom.auth', 'admin'])->prefix('admin-legacy')->group(function () {
//     Route::get('/cupones', [\App\Http\Controllers\Web\CouponsController::class, 'index'])->name('legacy.admin.coupons.index');
//     Route::get('/clientes/registrar', [\App\Http\Controllers\Web\AuthController::class, 'showAdminClientRegister'])->name('legacy.admin.clients.create');
//     Route::post('/clientes/registrar', [\App\Http\Controllers\Web\AuthController::class, 'adminRegisterClient'])->name('legacy.admin.clients.store');
// });

// Rutas de código QR - cupones y usuario
Route::get('/qr/cupon/{codigo_qr}', [\App\Http\Controllers\Web\QrCodeController::class, 'generateCouponQr'])->name('qr.coupon');
Route::get('/qr/usuario/{qr_codigo}', [\App\Http\Controllers\Web\QrCodeController::class, 'generateUserQr'])->name('qr.usuario');

// Mi Tarjeta QR (requiere autenticación del cliente)
Route::middleware('custom.auth')->group(function () {
    Route::get('/mi-tarjeta', [\App\Http\Controllers\Web\DashboardController::class, 'miTarjeta'])->name('client.mi-tarjeta');
});

// Escaneo de QR de cliente en sucursal (legacy - requiere admin session)
Route::middleware(['custom.auth', 'admin'])->prefix('admin-legacy')->group(function () {
    Route::get('/escanear-cliente', [\App\Http\Controllers\Web\QrCodeController::class, 'scanUserQr'])->name('admin.qr.scan');
});

// Rutas de notificaciones
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/api', [NotificationController::class, 'getNotifications'])->name('notifications.api');
Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

// ====================================================================
// NUEVO PANEL DE ADMINISTRACIÓN (tabla separada 'administradores')
// ====================================================================

// Auth admin (login/logout separado del login de clientes)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

// Rutas protegidas del panel admin (guard 'admin')
Route::middleware('admin.auth')->prefix('admin')->group(function () {

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Dashboard (redirige según rol)
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Registro de clientes (accesible por superadmin y admin_sucursal)
    Route::get('/clientes/registrar', [AdminClienteRegistroController::class, 'showForm'])->name('admin.clientes.registrar');
    Route::post('/clientes/registrar', [AdminClienteRegistroController::class, 'store'])->name('admin.clientes.registrar.store');

    // Cupones legacy (desactivado - ahora se usan Promociones Oppen)
    // Route::get('/cupones', [AdminCouponsController::class, 'index'])->name('admin.coupons.index');
    // Route::get('/cupones/crear', [AdminCouponsController::class, 'create'])->name('admin.coupons.create');
    // Route::post('/cupones', [AdminCouponsController::class, 'store'])->name('admin.coupons.store');
    // Route::get('/cupones/{id}/editar', [AdminCouponsController::class, 'edit'])->name('admin.coupons.edit');
    // Route::put('/cupones/{id}', [AdminCouponsController::class, 'update'])->name('admin.coupons.update');
    // Route::delete('/cupones/{id}', [AdminCouponsController::class, 'destroy'])->name('admin.coupons.destroy');
    // Route::post('/cupones/{id}/asignar', [AdminCouponsController::class, 'assign'])->name('admin.coupons.assign');
    // Route::get('/cupones/{id}/asignaciones', [AdminCouponsController::class, 'getAssignments'])->name('admin.coupons.assignments');
    // Route::get('/validar-cupones', [AdminCouponsController::class, 'showValidationForm'])->name('admin.coupons.validate');
    // Route::post('/cupones/validar', [AdminCouponsController::class, 'validateCoupon'])->name('admin.coupons.validate.check');
    // Route::post('/cupones/marcar-usado', [AdminCouponsController::class, 'markAsUsed'])->name('admin.coupons.mark-used');

    // Promociones Oppen (sincronización API)
    Route::get('/promociones-oppen', [AdminPromosOppenController::class, 'index'])->name('admin.promos-oppen.index');
    Route::get('/promociones-oppen/{id}', [AdminPromosOppenController::class, 'show'])->name('admin.promos-oppen.show');
    Route::post('/promociones-oppen/sync', [AdminPromosOppenController::class, 'sync'])->name('admin.promos-oppen.sync');

    // --- Rutas exclusivas de Admin Sucursal ---
    Route::middleware('admin.sucursal')->group(function () {
        Route::get('/mi-sucursal/clientes', [AdminSucursalController::class, 'misClientes'])->name('admin.mi-sucursal.clientes');
    });

    // --- Rutas exclusivas de Superadmin ---
    Route::middleware('superadmin')->group(function () {
        // CRUD Administradores de sucursal
        Route::get('/administradores', [AdminUsuariosController::class, 'index'])->name('admin.usuarios.index');
        Route::get('/administradores/crear', [AdminUsuariosController::class, 'create'])->name('admin.usuarios.create');
        Route::post('/administradores', [AdminUsuariosController::class, 'store'])->name('admin.usuarios.store');
        Route::get('/administradores/{id}/editar', [AdminUsuariosController::class, 'edit'])->name('admin.usuarios.edit');
        Route::put('/administradores/{id}', [AdminUsuariosController::class, 'update'])->name('admin.usuarios.update');
        Route::patch('/administradores/{id}/toggle', [AdminUsuariosController::class, 'toggleActive'])->name('admin.usuarios.toggle');
        Route::patch('/administradores/{id}/reset-password', [AdminUsuariosController::class, 'resetPassword'])->name('admin.usuarios.reset-password');

        // Ver todos los clientes
        Route::get('/clientes', [AdminClientesController::class, 'index'])->name('admin.clientes.index');
    });
});

// ====================================================================
// RUTAS DE ACCESO QA (sin middleware de QA para evitar loop)
// ====================================================================
Route::withoutMiddleware(\App\Http\Middleware\QaAccessMiddleware::class)->group(function () {

    Route::post('/qa-access/verify', function (\Illuminate\Http\Request $request) {
        $qaPassword = env('QA_ACCESS_PASSWORD');

        if (empty($qaPassword)) {
            return redirect('/');
        }

        if ($request->input('password') === $qaPassword) {
            $request->session()->put('qa_access_granted', true);
            return redirect($request->input('redirect', '/'));
        }

        return back()->with('error', 'Contraseña incorrecta');
    })->name('qa.access.verify');

    Route::get('/qa-access/logout', function (\Illuminate\Http\Request $request) {
        $request->session()->forget('qa_access_granted');
        return redirect('/');
    })->name('qa.access.logout');

});

// ====================================================================
// RUTAS DE PROPUESTAS DE DISEÑO (solo preview – no afectan el sistema)
// ====================================================================
Route::get('/propuesta/1', function () {
    return view('proposals.proposal-1');
})->name('proposal.1');

Route::get('/propuesta/2', function () {
    return view('proposals.proposal-2');
})->name('proposal.2');

