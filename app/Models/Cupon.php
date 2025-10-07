<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'puntos_requeridos',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
            'puntos_requeridos' => 'integer',
        ];
    }

    // Relaciones
    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por');
    }

    public function cuponesAsignados()
    {
        return $this->hasMany(CuponAsignado::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVigentes($query)
    {
        $ahora = Carbon::now()->toDateString();
        return $query->where('fecha_inicio', '<=', $ahora)
                    ->where('fecha_fin', '>=', $ahora);
    }

    public function scopeDisponibles($query)
    {
        return $query->activos()->vigentes();
    }

    public function scopePorPuntosRequeridos($query, $puntos)
    {
        return $query->where('puntos_requeridos', '<=', $puntos);
    }

    // Métodos
    public function estaVigente()
    {
        $ahora = Carbon::now()->toDate();
        return $this->fecha_inicio <= $ahora && $this->fecha_fin >= $ahora;
    }

    public function estaDisponible()
    {
        return $this->activo && $this->estaVigente();
    }

    public function puedeSerCanjeadoPor($usuario)
    {
        return $this->estaDisponible() && 
               $usuario->puntos && 
               $usuario->puntos->saldo >= $this->puntos_requeridos;
    }
}