<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TransactionController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;
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
Route::get('/cupones', [DashboardController::class, 'coupons'])->name('coupons.index');

// Rutas que requieren autenticación
Route::middleware('custom.auth')->group(function () {
    
    // Perfil del usuario
    Route::get('/perfil', [DashboardController::class, 'profile'])->name('profile.show');
    Route::put('/perfil', [DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Cupones del usuario
    Route::get('/mis-cupones', [DashboardController::class, 'myCoupons'])->name('coupons.my');
    Route::post('/cupones/canjear', [DashboardController::class, 'redeemCoupon'])->name('coupons.redeem');
    Route::post('/cupones/obtener', [DashboardController::class, 'assignCouponWithGamification'])->name('coupons.assign');
    Route::post('/cupones/verificar-desbloqueos', [DashboardController::class, 'unlockMyBlockedCoupons'])->name('coupons.unlock');
    
    // Compras
    Route::get('/compras', [DashboardController::class, 'purchases'])->name('purchases.index');
    
    // Puntos
    Route::get('/puntos/historial', [DashboardController::class, 'pointsHistory'])->name('points.history');
    
    // Rutas de administración (solo para admins)
    Route::get('/admin/points', [TransactionController::class, 'adminPanel'])->name('admin.points');
    
});

// Rutas de autenticación
Route::get('/login', [\App\Http\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Web\AuthController::class, 'login'])->name('login.post');

Route::get('/register', [\App\Http\Controllers\Web\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [\App\Http\Controllers\Web\AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [\App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout');

// Rutas de transacciones
Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::get('/purchase', [TransactionController::class, 'showPurchaseForm'])->name('purchase.form');
Route::post('/purchase', [TransactionController::class, 'storePurchase'])->name('purchase.store');

// Rutas de tickets (requieren autenticación)
Route::middleware('custom.auth')->group(function () {
    Route::get('/tickets', [\App\Http\Controllers\Web\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\Web\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\Web\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [\App\Http\Controllers\Web\TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/check-ticket', [\App\Http\Controllers\Web\TicketController::class, 'checkTicket'])->name('tickets.check');
    Route::get('/tickets/calculate-points', [\App\Http\Controllers\Web\TicketController::class, 'calculatePoints'])->name('tickets.calculate');
});

// Rutas de cupones para clientes
Route::get('/cupones', [\App\Http\Controllers\Web\CouponsController::class, 'myCoupons'])->name('coupons.index');
Route::post('/cupones/{id}/canjear', [\App\Http\Controllers\Web\CouponsController::class, 'redeem'])->name('coupons.redeem');
Route::get('/cupones/{id}', [\App\Http\Controllers\Web\CouponsController::class, 'show'])->name('coupons.show');

// Rutas de administración de cupones (requieren autenticación)
Route::middleware(['custom.auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/cupones', [\App\Http\Controllers\Web\CouponsController::class, 'index'])->name('admin.coupons.index');
    Route::get('/cupones/crear', [\App\Http\Controllers\Web\CouponsController::class, 'create'])->name('admin.coupons.create');
    Route::post('/cupones', [\App\Http\Controllers\Web\CouponsController::class, 'store'])->name('admin.coupons.store');
    Route::get('/cupones/{id}/editar', [\App\Http\Controllers\Web\CouponsController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('/cupones/{id}', [\App\Http\Controllers\Web\CouponsController::class, 'update'])->name('admin.coupons.update');
    Route::delete('/cupones/{id}', [\App\Http\Controllers\Web\CouponsController::class, 'destroy'])->name('admin.coupons.destroy');
    Route::post('/cupones/{id}/asignar', [\App\Http\Controllers\Web\CouponsController::class, 'assign'])->name('admin.coupons.assign');
    Route::get('/cupones/{id}/asignaciones', [\App\Http\Controllers\Web\CouponsController::class, 'getAssignments'])->name('admin.coupons.assignments');
    
    // Rutas de transacciones de puntos
    Route::get('/transacciones', [\App\Http\Controllers\Web\DashboardController::class, 'showTransactions'])->name('admin.transactions');
    Route::get('/transacciones/exportar', [\App\Http\Controllers\Web\DashboardController::class, 'exportTransactions'])->name('admin.transactions.export');
    
    // Rutas de validación de cupones QR
    Route::get('/validar-cupones', [\App\Http\Controllers\Web\CouponValidationController::class, 'showValidationForm'])->name('admin.coupons.validate');
    Route::post('/cupones/validar', [\App\Http\Controllers\Web\CouponValidationController::class, 'validateCoupon'])->name('admin.coupons.validate.check');
    Route::post('/cupones/marcar-usado', [\App\Http\Controllers\Web\CouponValidationController::class, 'markAsUsed'])->name('admin.coupons.mark-used');
    
    // Rutas de gestión de clientes
    Route::get('/clientes/registrar', [\App\Http\Controllers\Web\AuthController::class, 'showAdminClientRegister'])->name('admin.clients.create');
    Route::post('/clientes/registrar', [\App\Http\Controllers\Web\AuthController::class, 'adminRegisterClient'])->name('admin.clients.store');
});

// Ruta para generar códigos QR (accesible públicamente)
Route::get('/qr/cupon/{codigo_qr}', [\App\Http\Controllers\Web\QrCodeController::class, 'generateCouponQr'])->name('qr.coupon');

// Rutas de notificaciones
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/notifications/api', [NotificationController::class, 'getNotifications'])->name('notifications.api');
Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

