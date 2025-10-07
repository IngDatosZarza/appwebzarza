<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaccionPuntos extends Model
{
    use HasFactory;

    protected $table = 'transacciones_puntos';
    
    // Solo tiene created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'tipo',
        'puntos',
        'descripcion',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'puntos' => 'integer',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    // Scopes
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeIngresos($query)
    {
        return $query->where('puntos', '>', 0);
    }

    public function scopeEgresos($query)
    {
        return $query->where('puntos', '<', 0);
    }

    public function scopePorRangoFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    // Accessors
    public function getTipoTransaccionAttribute()
    {
        return $this->puntos > 0 ? 'ingreso' : 'egreso';
    }

    public function getPuntosAbsolutosAttribute()
    {
        return abs($this->puntos);
    }
}