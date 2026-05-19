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
        'codigo',
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
            'puntos_requeridos' => 'integer',
            'activo' => 'boolean',
        ];
    }

    /**
     * Override save para manejar booleanos en PostgreSQL correctamente.
     */
    public function save(array $options = [])
    {
        // Si activo cambió, usar DB::statement para evitar problemas con boolean en PostgreSQL
        if ($this->exists && $this->isDirty('activo')) {
            $activoVal = $this->activo ? 'TRUE' : 'FALSE';
            \Illuminate\Support\Facades\DB::statement(
                "UPDATE cupones SET activo = {$activoVal}, updated_at = NOW() WHERE id = ?",
                [$this->id]
            );
            // Quitar activo del dirty para que parent::save() no lo envíe de nuevo
            $this->attributes['activo'] = $this->activo;
            $this->syncOriginalAttribute('activo');
        }
        
        // Si es nuevo registro, forzar boolean nativo
        if (!$this->exists && isset($this->attributes['activo'])) {
            $activoVal = filter_var($this->attributes['activo'], FILTER_VALIDATE_BOOLEAN);
            unset($this->attributes['activo']);
            $result = parent::save($options);
            \Illuminate\Support\Facades\DB::statement(
                "UPDATE cupones SET activo = " . ($activoVal ? 'TRUE' : 'FALSE') . " WHERE id = ?",
                [$this->id]
            );
            return $result;
        }

        return parent::save($options);
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
        return $query->whereRaw('"activo" = true');
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

    /**
     * Generar código único para el cupón si no tiene
     */
    public static function generarCodigo($nombre)
    {
        // Limpiar nombre y convertir a mayúsculas
        $base = strtoupper(preg_replace('/[^A-Z0-9]/', '', $nombre));
        $base = substr($base, 0, 12); // Máximo 12 caracteres del nombre
        
        // Generar código único
        $codigo = $base . rand(10, 99);
        
        // Verificar que sea único
        while (self::where('codigo', $codigo)->exists()) {
            $codigo = $base . rand(10, 99);
        }
        
        return $codigo;
    }
}