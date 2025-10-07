<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';
    
    // Solo tiene fecha, no created_at ni updated_at estándar
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tabla',
        'registro_id',
        'accion',
        'cambios',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'cambios' => 'array',
            'fecha' => 'datetime',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    // Scopes
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla', $tabla);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopePorRegistro($query, $tabla, $registroId)
    {
        return $query->where('tabla', $tabla)->where('registro_id', $registroId);
    }

    public function scopePorRangoFecha($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($auditoria) {
            if (empty($auditoria->fecha)) {
                $auditoria->fecha = now();
            }
        });
    }

    // Métodos estáticos para crear registros de auditoría
    public static function registrar($usuarioId, $tabla, $registroId, $accion, $cambios = [])
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'accion' => $accion,
            'cambios' => $cambios,
        ]);
    }

    public static function registrarCreacion($usuarioId, $tabla, $registroId, $datos = [])
    {
        return self::registrar($usuarioId, $tabla, $registroId, 'create', [
            'datos_nuevos' => $datos
        ]);
    }

    public static function registrarActualizacion($usuarioId, $tabla, $registroId, $datosAnteriores, $datosNuevos)
    {
        return self::registrar($usuarioId, $tabla, $registroId, 'update', [
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos
        ]);
    }

    public static function registrarEliminacion($usuarioId, $tabla, $registroId, $datos = [])
    {
        return self::registrar($usuarioId, $tabla, $registroId, 'delete', [
            'datos_eliminados' => $datos
        ]);
    }
}