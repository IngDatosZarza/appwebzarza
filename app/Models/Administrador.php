<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Administrador extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'administradores';

    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'telefono',
        'rol',
        'sucursal_id',
        'activo',
        'ultimo_acceso',
        'intentos_fallidos',
        'bloqueado_hasta',
    ];

    protected $hidden = [
        'password',
    ];

   

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
            'bloqueado_hasta' => 'datetime',
        ];
    }

    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function clientesRegistrados()
    {
        return $this->hasMany(Usuario::class, 'registrado_por_administrador_id');
    }

    // Scopes
    public function scopeSuperadmins($query)
    {
        return $query->where('rol', 'superadmin');
    }

    public function scopeAdminsSucursal($query)
    {
        return $query->where('rol', 'admin_sucursal');
    }

    public function scopeActivos($query)
    {
        return $query->whereRaw('"activo" = TRUE');
    }

    // Accessors
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    // Métodos de rol
    public function esSuperadmin(): bool
    {
        return $this->rol === 'superadmin';
    }

    public function esAdminSucursal(): bool
    {
        return $this->rol === 'admin_sucursal';
    }

    // Métodos de seguridad (lockout)
    public function estaBloqueado(): bool
    {
        if ($this->bloqueado_hasta && $this->bloqueado_hasta->isFuture()) {
            return true;
        }

        if ($this->bloqueado_hasta && $this->bloqueado_hasta->isPast()) {
            $this->resetearIntentos();
        }

        return false;
    }

    public function incrementarIntentosFallidos(): void
    {
        $this->increment('intentos_fallidos');

        // Bloquear tras 5 intentos fallidos por 15 minutos
        if ($this->intentos_fallidos >= 5) {
            $this->update([
                'bloqueado_hasta' => Carbon::now()->addMinutes(15),
            ]);
        }
    }

    public function resetearIntentos(): void
    {
        $this->update([
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);
    }

    public function registrarAcceso(): void
    {
        $this->update([
            'ultimo_acceso' => Carbon::now(),
            'intentos_fallidos' => 0,
            'bloqueado_hasta' => null,
        ]);
    }
}
