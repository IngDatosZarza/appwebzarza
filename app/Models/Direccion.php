<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'usuario_id',
        'calle',
        'numero',
        'colonia',
        'codigo_postal',
        'estado',
        'ciudad',
        'pais',
        'referencias',
        'tipo',
        'principal',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por');
    }

    // Accessors
    public function getDireccionCompletaAttribute()
    {
        return "{$this->calle} {$this->numero}, {$this->colonia}, {$this->ciudad}, {$this->estado}, {$this->codigo_postal}, {$this->pais}";
    }

    // Scopes
    public function scopePrincipales($query)
    {
        return $query->where('principal', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }
}