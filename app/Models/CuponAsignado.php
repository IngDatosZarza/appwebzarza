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
        'fecha_uso',
        'validado_por',
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

    public function validadoPor()
    {
        return $this->belongsTo(Usuario::class, 'validado_por');
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
        return $query->where('estado', 'asignado');
    }

    public function scopeUsados($query)
    {
        return $query->where('estado', 'usado');
    }

    public function scopeRedimidos($query)
    {
        return $query->where('estado', 'usado'); // Alias para compatibilidad
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', 'vencido');
    }

    public function scopeBloqueados($query)
    {
        return $query->where('estado', 'bloqueado');
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
        return $this->estado === 'asignado' && 
               $this->cupon && 
               $this->cupon->estaDisponible();
    }

    public function marcarComoUsado($validadoPor = null)
    {
        $this->estado = 'usado';
        $this->fecha_uso = now();
        $this->validado_por = $validadoPor;
        $this->save();
    }

    public function marcarComoRedimido()
    {
        // Alias para compatibilidad
        $this->marcarComoUsado();
    }

    public function marcarComoVencido()
    {
        $this->estado = 'vencido';
        $this->save();
    }

    /**
     * Generar código QR combinado con el código del cupón
     */
    public function generarCodigoQRCompleto()
    {
        if ($this->cupon && $this->cupon->codigo) {
            return $this->cupon->codigo . '-' . substr($this->codigo_qr, 2); // Quitar "QR" del inicio
        }
        return $this->codigo_qr;
    }
}