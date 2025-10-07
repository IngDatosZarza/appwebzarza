<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puntos extends Model
{
    use HasFactory;

    protected $table = 'puntos';
    
    // Solo tiene updated_at, no created_at
    const CREATED_AT = null;

    protected $fillable = [
        'usuario_id',
        'saldo',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'saldo' => 'integer',
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

    // Métodos para manejar puntos
    public function agregar($cantidad, $actualizadoPor = null)
    {
        $this->saldo += $cantidad;
        $this->actualizado_por = $actualizadoPor;
        $this->save();
    }

    public function descontar($cantidad, $actualizadoPor = null)
    {
        if ($this->saldo >= $cantidad) {
            $this->saldo -= $cantidad;
            $this->actualizado_por = $actualizadoPor;
            $this->save();
            return true;
        }
        return false;
    }

    public function tieneSSuficientes($cantidad)
    {
        return $this->saldo >= $cantidad;
    }
}