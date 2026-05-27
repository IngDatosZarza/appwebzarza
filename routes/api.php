<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CodigoPostalController;
use App\Http\Controllers\Api\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas
Route::prefix('v1')->group(function () {
    
    // Autenticación
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Cupones disponibles (público)
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::get('/coupons/{id}', [CouponController::class, 'show']);
    
    // Estadísticas públicas
    Route::get('/stats/purchases', [PurchaseController::class, 'stats']);
    
    // Ubicación (público - para guardar ubicación antes de autenticarse)
    Route::post('/location', [LocationController::class, 'store']);
});

// Rutas públicas de códigos postales (sin versión para el formulario de registro)
Route::prefix('codigos-postales')->group(function () {
    Route::get('/estados', [CodigoPostalController::class, 'getEstados']);
    Route::get('/municipios', [CodigoPostalController::class, 'getMunicipios']);
    Route::get('/colonias', [CodigoPostalController::class, 'getColonias']);
    Route::get('/buscar', [CodigoPostalController::class, 'buscarPorCP']);
});

// Rutas protegidas (requieren autenticación)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Perfil de usuario
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Ubicaciones del usuario
    Route::get('/location', [LocationController::class, 'show']); // Última ubicación
    Route::get('/locations', [LocationController::class, 'index']); // Historial
    
    // Compras
    Route::apiResource('purchases', PurchaseController::class)->only(['index', 'show', 'store']);
    
    // Cupones del usuario
    Route::get('/user/{userId}/coupons', [CouponController::class, 'userCoupons']);
    Route::post('/coupons/redeem', [CouponController::class, 'redeem']);
    Route::post('/coupons/{cuponAsignadoId}/use', [CouponController::class, 'useCoupon']);
    
});

// Rutas administrativas (solo admin)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    
    // Gestión de cupones
    Route::apiResource('coupons', CouponController::class)->except(['index', 'show']);
    
    // Gestión de usuarios
    Route::get('/users', function(Request $request) {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Usuario::with('puntos')->paginate(15)
        ]);
    });
    
    // Reportes administrativos
    Route::get('/reports/dashboard', function() {
        $stats = [
            'total_users' => \App\Models\Usuario::count(),
            'total_purchases' => \App\Models\Compra::count(),
            'total_points' => \App\Models\TransaccionPunto::where('tipo', 'ganancia')->sum('puntos'),
            'active_coupons' => \App\Models\Cupon::where('activo', true)->count(),
            'redeemed_coupons' => \App\Models\CuponAsignado::where('estado', 'utilizado')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    });
    
    Route::get('/reports/sales', [PurchaseController::class, 'stats']);
    Route::get('/reports/locations', [LocationController::class, 'stats']); // Estadísticas de ubicación
    
});

// Ruta de prueba de API
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'API Sistema de Puntos de Fidelidad funcionando correctamente',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Manejo de rutas no encontradas
Route::fallback(function(){
    return response()->json([
        'success' => false,
        'message' => 'Endpoint no encontrado'
    ], 404);
});