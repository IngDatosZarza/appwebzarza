<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'telefono',
        'actualizado_por',
    ];

    // Relaciones
    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function redenciones()
    {
        return $this->hasMany(Redencion::class);
    }

    // Scopes
    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo', $codigo);
    }

    // Accessors
    public function getInfoCompletaAttribute()
    {
        return "{$this->nombre} ({$this->codigo}) - {$this->direccion}";
    }
}