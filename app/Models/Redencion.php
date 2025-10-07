<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redencion extends Model
{
    use HasFactory;

    protected $table = 'redenciones';
    
    // Solo tiene created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'cupon_asignado_id',
        'sucursal_id',
        'fecha_redencion',
        'observaciones',
        'realizado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_redencion' => 'datetime',
        ];
    }

    // Relaciones
    public function cuponAsignado()
    {
        return $this->belongsTo(CuponAsignado::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function realizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'realizado_por');
    }

    // Relaciones a través de CuponAsignado
    public function usuario()
    {
        return $this->hasOneThrough(
            Usuario::class,
            CuponAsignado::class,
            'id', // clave foránea en cupones_asignados
            'id', // clave foránea en usuarios
            'cupon_asignado_id', // clave local en redenciones
            'usuario_id' // clave local en cupones_asignados
        );
    }

    public function cupon()
    {
        return $this->hasOneThrough(
            Cupon::class,
            CuponAsignado::class,
            'id', // clave foránea en cupones_asignados
            'id', // clave foránea en cupones
            'cupon_asignado_id', // clave local en redenciones
            'cupon_id' // clave local en cupones_asignados
        );
    }

    // Scopes
    public function scopePorSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    public function scopePorRangoFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_redencion', [$fechaInicio, $fechaFin]);
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($redencion) {
            if (empty($redencion->fecha_redencion)) {
                $redencion->fecha_redencion = now();
            }
        });

        static::created(function ($redencion) {
            // Marcar el cupón asignado como redimido
            $redencion->cuponAsignado->marcarComoRedimido();
        });
    }
}