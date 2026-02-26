<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

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

// Rutas públicas
Route::get('/cupones', [DashboardController::class, 'coupons'])->name('coupons.index');

// Rutas que requieren autenticación
Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil del usuario
    Route::get('/perfil', [DashboardController::class, 'profile'])->name('profile.show');
    Route::put('/perfil', [DashboardController::class, 'updateProfile'])->name('profile.update');
    
    // Cupones del usuario
    Route::get('/mis-cupones', [DashboardController::class, 'myCoupons'])->name('coupons.my');
    Route::post('/cupones/canjear', [DashboardController::class, 'redeemCoupon'])->name('coupons.redeem');
    
    // Compras
    Route::get('/compras', [DashboardController::class, 'purchases'])->name('purchases.index');
    
    // Puntos
    Route::get('/puntos/historial', [DashboardController::class, 'pointsHistory'])->name('points.history');
    
});

// Rutas de autenticación (temporales)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/logout', function () {
    return redirect()->route('dashboard')->with('success', 'Sesión cerrada exitosamente');
})->name('logout');