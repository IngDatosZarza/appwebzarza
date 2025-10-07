<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'password' => 'hashed',
        ];
    }

    // Relaciones
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }

    public function direccionPrincipal()
    {
        return $this->hasOne(Direccion::class)->where('principal', true);
    }

    public function puntos()
    {
        return $this->hasOne(Puntos::class);
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function transaccionesPuntos()
    {
        return $this->hasMany(TransaccionPuntos::class);
    }

    public function cuponesAsignados()
    {
        return $this->hasMany(CuponAsignado::class);
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class);
    }

    // Scopes
    public function scopeClientes($query)
    {
        return $query->where('rol', 'cliente');
    }

    public function scopeAdministradores($query)
    {
        return $query->where('rol', 'admin');
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}