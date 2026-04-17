<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Mostrar formulario de registro de ticket
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $sucursales = Sucursal::orderBy('nombre')->get();
        
        // Obtener últimas compras del usuario
        $ultimasCompras = Auth::user()->compras()
            ->with('sucursal')
            ->orderBy('fecha_compra', 'desc')
            ->limit(5)
            ->get();

        return view('tickets.create', compact('sucursales', 'ultimasCompras'));
    }

    /**
     * Registrar un nuevo ticket
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validator = Validator::make($request->all(), [
            'numero_ticket' => 'required|string|max:50|unique:compras,numero_ticket',
            'monto' => 'required|numeric|min:0.01|max:999999.99',
            'sucursal_id' => 'required|exists:sucursales,id',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'descripcion' => 'nullable|string|max:500',
            'fecha_compra' => 'nullable|date|before_or_equal:today',
        ], [
            'numero_ticket.required' => 'El número de ticket es obligatorio',
            'numero_ticket.unique' => 'Este número de ticket ya ha sido registrado',
            'monto.required' => 'El monto es obligatorio',
            'monto.min' => 'El monto debe ser mayor a $0.01',
            'monto.max' => 'El monto no puede ser mayor a $999,999.99',
            'sucursal_id.required' => 'Debe seleccionar una sucursal',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe',
            'metodo_pago.required' => 'Debe seleccionar un método de pago',
            'fecha_compra.before_or_equal' => 'La fecha no puede ser futura',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            $usuario = Auth::user();
            $monto = $request->monto;
            
            // Crear la compra/ticket
            $compra = Compra::create([
                'usuario_id' => $usuario->id,
                'sucursal_id' => $request->sucursal_id,
                'monto' => $monto,
                'numero_ticket' => $request->numero_ticket,
                'puntos_generados' => 0,
                'descripcion' => $request->descripcion ?: "Ticket #{$request->numero_ticket}",
                'metodo_pago' => $request->metodo_pago,
                'fecha_compra' => $request->fecha_compra ? $request->fecha_compra : now(),
                'creado_por' => $usuario->id,
            ]);

            DB::commit();

            return redirect()
                ->route('tickets.index')
                ->with('success', '¡Ticket registrado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Error al registrar el ticket: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar lista de tickets del usuario
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $tickets = Auth::user()->compras()
            ->with('sucursal')
            ->orderBy('fecha_compra', 'desc')
            ->paginate(10);

        // Estadísticas del usuario
        $estadisticas = [
            'total_tickets' => Auth::user()->compras()->count(),
            'monto_total' => Auth::user()->compras()->sum('monto'),
        ];

        return view('tickets.index', compact('tickets', 'estadisticas'));
    }

    /**
     * Mostrar detalles de un ticket específico
     */
    public function show($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $ticket = Auth::user()->compras()
            ->with(['sucursal', 'transaccionPuntos'])
            ->findOrFail($id);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Verificar si un número de ticket ya existe
     */
    public function checkTicket(Request $request)
    {
        $exists = Compra::where('numero_ticket', $request->numero_ticket)->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Este número de ticket ya ha sido registrado' : 'Número de ticket disponible'
        ]);
    }
}