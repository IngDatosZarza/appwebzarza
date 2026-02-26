<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CouponValidationController extends Controller
{
    /**
     * Mostrar formulario de validación de cupones (solo para admins)
     */
    public function showValidationForm()
    {
        // Verificar si es admin
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para validar cupones');
        }
        
        return view('admin.coupons.validate');
    }
    
    /**
     * Validar un código QR de cupón o código de cupón
     */
    public function validateCoupon(Request $request)
    {
        // Verificar si es admin
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $codigo_input = $request->input('codigo_qr');
        
        if (empty($codigo_input)) {
            return response()->json(['error' => 'Código requerido'], 400);
        }
        
        try {
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            // Buscar el cupón asignado por codigo_qr o por codigo del cupon
            // Si el código no tiene guión, buscar por codigo del cupon
            // Si tiene guión (ej: BANDERILLAS20-A3F9B), buscar por codigo_qr
            $stmt = $pdo->prepare("
                SELECT 
                    ca.*,
                    c.nombre as cupon_nombre,
                    c.codigo as cupon_codigo,
                    c.descripcion as cupon_descripcion,
                    c.puntos_requeridos,
                    u.nombres || ' ' || u.apellido_paterno as cliente_nombre,
                    u.email as cliente_email
                FROM cupones_asignados ca
                INNER JOIN cupones c ON ca.cupon_id = c.id
                INNER JOIN usuarios u ON ca.usuario_id = u.id
                WHERE ca.codigo_qr = ? OR c.codigo = ?
                ORDER BY ca.created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([$codigo_input, $codigo_input]);
            $cupon = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$cupon) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Código no válido o no encontrado. Verifica el código e intenta nuevamente.'
                ]);
            }
            
            // Verificar estado
            switch ($cupon['estado']) {
                case 'asignado':
                    return response()->json([
                        'valid' => true,
                        'status' => 'available',
                        'message' => 'Cupón válido y disponible para usar',
                        'data' => [
                            'cupon_nombre' => $cupon['cupon_nombre'],
                            'cupon_codigo' => $cupon['cupon_codigo'],
                            'cupon_descripcion' => $cupon['cupon_descripcion'],
                            'puntos_utilizados' => $cupon['puntos_requeridos'],
                            'cliente_nombre' => $cupon['cliente_nombre'],
                            'cliente_email' => $cupon['cliente_email'],
                            'fecha_canje' => date('d/m/Y H:i', strtotime($cupon['created_at'])),
                            'codigo_qr' => $cupon['codigo_qr']
                        ]
                    ]);
                    
                case 'usado':
                    return response()->json([
                        'valid' => false,
                        'status' => 'used',
                        'message' => 'Este cupón ya fue utilizado',
                        'data' => [
                            'cupon_nombre' => $cupon['cupon_nombre'],
                            'cupon_codigo' => $cupon['cupon_codigo'],
                            'cliente_nombre' => $cupon['cliente_nombre'],
                            'fecha_uso' => $cupon['fecha_uso'] ? date('d/m/Y H:i', strtotime($cupon['fecha_uso'])) : 'No disponible'
                        ]
                    ]);
                    
                case 'vencido':
                    return response()->json([
                        'valid' => false,
                        'status' => 'expired',
                        'message' => 'Este cupón ha vencido'
                    ]);
                
                case 'bloqueado':
                    return response()->json([
                        'valid' => false,
                        'status' => 'blocked',
                        'message' => 'Este cupón ha sido bloqueado'
                    ]);
                    
                default:
                    return response()->json([
                        'valid' => false,
                        'status' => 'invalid',
                        'message' => 'Estado de cupón no válido'
                    ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al validar cupón: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Marcar cupón como usado
     */
    public function markAsUsed(Request $request)
    {
        // Verificar si es admin
        if (!Session::get('user_authenticated', false) || Session::get('user_rol') !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        $codigo_qr = $request->input('codigo_qr');
        
        if (empty($codigo_qr)) {
            return response()->json(['error' => 'Código QR requerido'], 400);
        }
        
        try {
            $pdo = new \PDO('pgsql:host=localhost;port=5432;dbname=postgres', 'appwebuser', 'appwebpass');
            $pdo->exec('SET search_path TO appweb, public');
            
            $pdo->beginTransaction();
            
            // Obtener el ID del admin que valida
            $admin_id = Session::get('user_id');
            
            // Verificar que el cupón existe y está disponible
            $stmt = $pdo->prepare("
                SELECT id, estado FROM cupones_asignados 
                WHERE codigo_qr = ?
            ");
            $stmt->execute([$codigo_qr]);
            $cupon = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$cupon) {
                return response()->json(['error' => 'Cupón no encontrado'], 404);
            }
            
            if ($cupon['estado'] !== 'asignado') {
                return response()->json(['error' => 'El cupón no está disponible para usar'], 400);
            }
            
            // Marcar como usado con el admin que lo validó
            $stmt = $pdo->prepare("
                UPDATE cupones_asignados 
                SET estado = 'usado', 
                    fecha_uso = NOW(), 
                    validado_por = ?,
                    updated_at = NOW()
                WHERE codigo_qr = ?
            ");
            $stmt->execute([$admin_id, $codigo_qr]);
            
            $pdo->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cupón marcado como usado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            if (isset($pdo)) $pdo->rollBack();
            return response()->json([
                'error' => 'Error al marcar cupón como usado: ' . $e->getMessage()
            ], 500);
        }
    }
}