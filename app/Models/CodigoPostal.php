<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodigoPostal extends Model
{
    use HasFactory;

    protected $table = 'codigos_postales';

    protected $fillable = [
        'codigo_postal',
        'estado',
        'municipio',
        'ciudad',
        'colonia',
        'tipo_asentamiento',
        'zona',
    ];

    /**
     * Relación con direcciones
     */
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }

    /**
     * Scope para buscar por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para buscar por municipio
     */
    public function scopePorMunicipio($query, $municipio)
    {
        return $query->where('municipio', $municipio);
    }

    /**
     * Scope para buscar por código postal
     */
    public function scopePorCodigoPostal($query, $cp)
    {
        return $query->where('codigo_postal', $cp);
    }

    /**
     * Obtener estados únicos
     */
    public static function getEstados()
    {
        return static::select('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');
    }

    /**
     * Obtener municipios de un estado
     */
    public static function getMunicipiosPorEstado($estado)
    {
        return static::where('estado', $estado)
            ->select('municipio')
            ->distinct()
            ->orderBy('municipio')
            ->pluck('municipio');
    }

    /**
     * Obtener colonias de un municipio
     */
    public static function getColoniasPorMunicipio($estado, $municipio)
    {
        return static::where('estado', $estado)
            ->where('municipio', $municipio)
            ->select('colonia', 'codigo_postal', 'id')
            ->orderBy('colonia')
            ->get();
    }
}
