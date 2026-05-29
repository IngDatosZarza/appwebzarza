<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionUsuario extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones_usuarios';

    protected $fillable = [
        'usuario_id',
        'latitud',
        'longitud',
        'precision',
        'ciudad',
        'estado',
        'pais',
        'codigo_postal',
        'dispositivo',
        'navegador',
        'sistema_operativo',
        'user_agent',
        'ip_address',
        'pagina_origen',
        'evento',
        'session_id',
        'es_primera_visita',
        'metadata',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'precision' => 'decimal:2',
        'es_primera_visita' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mutator para asegurar que es_primera_visita sea booleano para PostgreSQL
     */
    public function setEsPrimeraVisitaAttribute($value)
    {
        $this->attributes['es_primera_visita'] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Relación con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Scope para ubicaciones de un usuario específico
     */
    public function scopeDeUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para ubicaciones anónimas
     */
    public function scopeAnonimas($query)
    {
        return $query->whereNull('usuario_id');
    }

    /**
     * Scope para ubicaciones de una ciudad
     */
    public function scopeDeCiudad($query, $ciudad)
    {
        return $query->where('ciudad', 'ILIKE', "%{$ciudad}%");
    }

    /**
     * Scope para ubicaciones de un estado
     */
    public function scopeDeEstado($query, $estado)
    {
        return $query->where('estado', 'ILIKE', "%{$estado}%");
    }

    /**
     * Scope para ubicaciones por evento
     */
    public function scopePorEvento($query, $evento)
    {
        return $query->where('evento', $evento);
    }

    /**
     * Scope para ubicaciones en un rango de fechas
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('created_at', [$desde, $hasta]);
    }

    /**
     * Scope para primeras visitas
     */
    public function scopePrimerasVisitas($query)
    {
        return $query->where('es_primera_visita', true);
    }

    /**
     * Obtener la última ubicación de un usuario
     */
    public static function ultimaDeUsuario($usuarioId)
    {
        return static::where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Obtener estadísticas de ubicaciones
     */
    public static function estadisticas()
    {
        return [
            'total' => static::count(),
            'con_usuario' => static::whereNotNull('usuario_id')->count(),
            'anonimas' => static::whereNull('usuario_id')->count(),
            'ciudades_unicas' => static::distinct('ciudad')->whereNotNull('ciudad')->count(),
            'estados_unicos' => static::distinct('estado')->whereNotNull('estado')->count(),
            'hoy' => static::whereDate('created_at', today())->count(),
            'esta_semana' => static::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'este_mes' => static::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
