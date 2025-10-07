<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'monto',
        'puntos_generados',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'puntos_generados' => 'integer',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function transaccionPuntos()
    {
        return $this->hasOne(TransaccionPuntos::class, 'registro_id')
                    ->where('tipo', 'compra');
    }

    // Scopes
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    public function scopePorRangoFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }

    // Métodos
    public static function calcularPuntos($monto, $factorConversion = 1)
    {
        // Por cada peso gastado, se otorga 1 punto (o según el factor de conversión)
        return (int) floor($monto * $factorConversion);
    }
}