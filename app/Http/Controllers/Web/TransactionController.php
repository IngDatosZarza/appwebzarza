<?php

namespace App\Http\Controllers\Web;

use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;
use App\Models\Compra;
use App\Models\Puntos;
use App\Models\TransaccionPuntos;
use App\Models\Cupon;
use App\Models\CuponAsignado;
use App\Models\Sucursal;
use App\Services\AuthService;

class TransactionController
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Alias para compatibilidad con rutas Laravel
     */
    public function index()
    {
        return $this->history();
    }

    public function showPurchaseForm()
    {
        return $this->purchaseForm();
    }

    public function storePurchase()
    {
        return $this->processPurchase();
    }

    public function showCoupons()
    {
        return $this->couponsForm();
    }

    public function redeemCoupon()
    {
        return $this->processCouponRedeem();
    }
    
    /**
     * Mostrar historial de transacciones del usuario
     */
    public function history()
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return redirect('/login');
        }
        
        try {
            // Obtener transacciones con información del registrador
            $transactions = TransaccionPuntos::select(
                    'transacciones_puntos.id',
                    'transacciones_puntos.tipo',
                    'transacciones_puntos.puntos',
                    'transacciones_puntos.descripcion',
                    'transacciones_puntos.created_at',
                    'usuarios.nombres as registrado_por_nombre',
                    'usuarios.apellido_paterno as registrado_por_apellido'
                )
                ->leftJoin('usuarios', 'transacciones_puntos.registrado_por', '=', 'usuarios.id')
                ->where('transacciones_puntos.usuario_id', $user->id)
                ->orderBy('transacciones_puntos.created_at', 'DESC')
                ->limit(50)
                ->get()
                ->toArray();
            
            // Obtener saldo actual
            $puntos = Puntos::where('usuario_id', $user->id)->first();
            $currentBalance = $puntos ? $puntos->saldo : 0;
            
            return view('transactions.history', compact('transactions', 'currentBalance'));
            
        } catch (Exception $e) {
            return $this->showError('Error al obtener historial: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar formulario de registro de compra
     */
    public function purchaseForm()
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return redirect('/login');
        }
        
        try {
            // Obtener sucursales
            $branches = Sucursal::select('id', 'codigo', 'nombre')
                ->orderBy('nombre')
                ->get()
                ->toArray();
            
            return view('transactions.purchase-form', compact('branches'));
            
        } catch (Exception $e) {
            return $this->showError('Error al cargar formulario: ' . $e->getMessage());
        }
    }
    
    /**
     * Procesar registro de compra
     */
    public function processPurchase()
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || request()->method() !== 'POST') {
            return redirect('/login');
        }
        
        // Incluir el controlador de notificaciones
        $notificationController = new NotificationController();
        
        $errors = [];
        $amount = floatval(request()->input('amount', 0));
        $branchId = intval(request()->input('branch_id', 0));
        $description = trim(request()->input('description', ''));
        
        // Validaciones
        if ($amount <= 0) {
            $errors[] = 'El monto debe ser mayor a 0';
        }
        if ($branchId <= 0) {
            $errors[] = 'Debe seleccionar una sucursal';
        }
        if (empty($description)) {
            $description = 'Compra registrada';
        }
        
        if (!empty($errors)) {
            return redirect('/purchase')->with('errors', $errors);
        }
        
        try {
            DB::beginTransaction();
            
            // Calcular puntos (1 punto por cada peso gastado)
            $pointsGenerated = floor($amount);
            
            // Registrar compra
            Compra::create([
                'usuario_id' => $user->id,
                'sucursal_id' => $branchId,
                'monto' => $amount,
                'puntos_generados' => $pointsGenerated,
                'creado_por' => $user->id
            ]);
            
            // Registrar transacción de puntos
            TransaccionPuntos::create([
                'usuario_id' => $user->id,
                'tipo' => 'compra',
                'puntos' => $pointsGenerated,
                'descripcion' => $description,
                'registrado_por' => $user->id
            ]);
            
            // Actualizar saldo de puntos
            Puntos::where('usuario_id', $user->id)->increment('saldo', $pointsGenerated);
            
            DB::commit();
            
            // Crear notificación de compra
            $notificationController->notifyPurchase($user->id, $amount, $pointsGenerated);
            
            return redirect('/transactions')->with('success', "Compra registrada exitosamente. Has ganado $pointsGenerated puntos.");
            
        } catch (Exception $e) {
            DB::rollBack();
            return redirect('/purchase')->with('errors', ['Error al procesar compra: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Mostrar cupones disponibles para canje
     */
    public function couponsForm()
    {
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            return redirect('/login');
        }
        
        try {
            // Obtener saldo actual
            $puntos = Puntos::where('usuario_id', $user->id)->first();
            $currentBalance = $puntos ? $puntos->saldo : 0;
            
            // Obtener cupones disponibles
            $coupons = Cupon::select('id', 'nombre', 'descripcion', 'puntos_requeridos', 'fecha_inicio', 'fecha_fin')
                ->whereRaw('"activo" = true')
                ->whereDate('fecha_inicio', '<=', DB::raw('CURRENT_DATE'))
                ->whereDate('fecha_fin', '>=', DB::raw('CURRENT_DATE'))
                ->orderBy('puntos_requeridos', 'ASC')
                ->get()
                ->toArray();
            
            // Obtener cupones ya asignados al usuario
            $assignedCoupons = CuponAsignado::select('cupon_id', 'estado', 'codigo_qr', 'created_at')
                ->where('usuario_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->get()
                ->toArray();
            
            return view('transactions.coupons', compact('currentBalance', 'coupons', 'assignedCoupons'));
            
        } catch (Exception $e) {
            return $this->showError('Error al cargar cupones: ' . $e->getMessage());
        }
    }
    
    /**
     * Procesar canje de cupón
     */
    public function processCouponRedeem()
    {
        $user = $this->authService->getCurrentUser();
        if (!$user || request()->method() !== 'POST') {
            return redirect('/login');
        }
        
        // Incluir el controlador de notificaciones
        $notificationController = new NotificationController();
        
        $couponId = intval(request()->input('coupon_id', 0));
        
        if ($couponId <= 0) {
            return redirect('/coupons')->with('errors', ['Cupón inválido']);
        }
        
        try {
            DB::beginTransaction();
            
            // Verificar cupón y puntos requeridos
            $coupon = Cupon::select('nombre', 'descripcion', 'puntos_requeridos')
                ->where('id', $couponId)
                ->whereRaw('"activo" = true')
                ->whereDate('fecha_inicio', '<=', DB::raw('CURRENT_DATE'))
                ->whereDate('fecha_fin', '>=', DB::raw('CURRENT_DATE'))
                ->first();
            
            if (!$coupon) {
                throw new Exception('Cupón no disponible');
            }
            
            // Verificar saldo del usuario
            $puntos = Puntos::where('usuario_id', $user->id)->first();
            $currentBalance = $puntos ? $puntos->saldo : 0;
            
            if ($currentBalance < $coupon->puntos_requeridos) {
                throw new Exception('Puntos insuficientes para este cupón');
            }
            
            // Generar código QR único
            $qrCode = 'QR-' . uniqid() . '-' . $couponId;
            
            // Asignar cupón al usuario
            CuponAsignado::create([
                'usuario_id' => $user->id,
                'cupon_id' => $couponId,
                'estado' => 'pendiente',
                'codigo_qr' => $qrCode,
                'asignado_por' => $user->id
            ]);
            
            // Registrar transacción de débito de puntos
            TransaccionPuntos::create([
                'usuario_id' => $user->id,
                'tipo' => 'canje',
                'puntos' => $coupon->puntos_requeridos,
                'descripcion' => 'Canje de cupón: ' . $coupon->nombre,
                'registrado_por' => $user->id
            ]);
            
            // Actualizar saldo de puntos
            Puntos::where('usuario_id', $user->id)->decrement('saldo', $coupon->puntos_requeridos);
            
            DB::commit();
            
            // Crear notificación de canje
            $notificationController->notifyCouponRedeemed($user->id, $coupon->nombre, $coupon->puntos_requeridos, $qrCode);
            
            return redirect('/coupons')->with('success', "Cupón canjeado exitosamente. Código: $qrCode");
            
        } catch (Exception $e) {
            DB::rollBack();
            return redirect('/coupons')->with('errors', ['Error al canjear cupón: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Panel de administración de puntos (solo para admins)
     */
    public function adminPanel()
    {
        // Verificar autenticación usando sesiones de Laravel
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return redirect()->route('login')->with('error', 'Acceso denegado. Se requieren permisos de administrador.');
        }
        
        try {
            // Estadísticas generales
            $stats = [];
            
            // Total usuarios
            $stats['total_users'] = Usuario::where('rol', 'cliente')->count();
            
            // Total puntos en circulación
            $stats['total_points'] = Puntos::sum('saldo') ?: 0;
            
            // Transacciones del mes
            $stats['monthly_transactions'] = TransaccionPuntos::where('created_at', '>=', DB::raw("DATE_TRUNC('month', CURRENT_DATE)"))->count();
            
            // Cupones canjeados este mes
            $stats['monthly_coupons'] = CuponAsignado::where('created_at', '>=', DB::raw("DATE_TRUNC('month', CURRENT_DATE)"))->count();
            
            // Últimas transacciones
            $recentTransactions = TransaccionPuntos::select(
                    'transacciones_puntos.id',
                    'transacciones_puntos.tipo',
                    'transacciones_puntos.puntos',
                    'transacciones_puntos.descripcion',
                    'transacciones_puntos.created_at',
                    'usuarios.nombres',
                    'usuarios.apellido_paterno',
                    'usuarios.email'
                )
                ->join('usuarios', 'transacciones_puntos.usuario_id', '=', 'usuarios.id')
                ->orderBy('transacciones_puntos.created_at', 'DESC')
                ->limit(20)
                ->get()
                ->toArray();
            
            // Top usuarios por puntos
            $topUsers = Usuario::select(
                    'usuarios.nombres',
                    'usuarios.apellido_paterno',
                    'usuarios.email',
                    'puntos.saldo'
                )
                ->join('puntos', 'usuarios.id', '=', 'puntos.usuario_id')
                ->where('usuarios.rol', 'cliente')
                ->orderBy('puntos.saldo', 'DESC')
                ->limit(10)
                ->get()
                ->toArray();
            
            // Retornar vista usando Laravel Blade
            return view('admin.points-panel', compact('stats', 'recentTransactions', 'topUsers'));
            
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Error al cargar panel de administración: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostrar página de error
     */
    private function showError($message)
    {
        return view('errors.500', ['error' => $message]);
    }
}
?>
