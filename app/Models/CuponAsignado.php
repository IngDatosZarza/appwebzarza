<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CuponAsignado extends Model
{
    use HasFactory;

    protected $table = 'cupones_asignados';

    protected $fillable = [
        'usuario_id',
        'cupon_id',
        'estado',
        'codigo_qr',
        'asignado_por',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function cupon()
    {
        return $this->belongsTo(Cupon::class);
    }

    public function asignadoPor()
    {
        return $this->belongsTo(Usuario::class, 'asignado_por');
    }

    public function redencion()
    {
        return $this->hasOne(Redencion::class);
    }

    // Scopes
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeRedimidos($query)
    {
        return $query->where('estado', 'redimido');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'vencido');
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cuponAsignado) {
            if (empty($cuponAsignado->codigo_qr)) {
                $cuponAsignado->codigo_qr = self::generateUniqueQrCode();
            }
        });
    }

    // Métodos
    public static function generateUniqueQrCode()
    {
        do {
            $codigo = 'QR' . strtoupper(Str::random(10));
        } while (self::where('codigo_qr', $codigo)->exists());

        return $codigo;
    }

    public function puedeSerRedimido()
    {
        return $this->estado === 'pendiente' && 
               $this->cupon && 
               $this->cupon->estaDisponible();
    }

    public function marcarComoRedimido()
    {
        $this->estado = 'redimido';
        $this->save();
    }

    public function marcarComoVencido()
    {
        $this->estado = 'vencido';
        $this->save();
    }
}