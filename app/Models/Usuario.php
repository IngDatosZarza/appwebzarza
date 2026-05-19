<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'email_verified_at',
        'password',
        'telefono',
        'fecha_nacimiento',
        'rfc',
        'genero',
        'promo_email',
        'promo_whatsapp',
        'rol',
        'club_zarza',
        'oppen_customer_id',
        'qr_codigo',
        // Campos de tracking de registro
        'origen_registro',
        'dispositivo_registro',
        'registrado_por_admin_id',
        'registrado_por_administrador_id',
        'sucursal_registro_id',
        'campana_id',
        'user_agent',
        'ip_registro',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $usuario) {
            if (empty($usuario->qr_codigo)) {
                $usuario->qr_codigo = 'ZRZ-' . strtoupper(Str::random(16));
            }
        });
    }

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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'club_zarza' => 'boolean',
            'promo_email' => 'boolean',
            'promo_whatsapp' => 'boolean',
        ];
    }

    // Relaciones
    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }

    public function direccionPrincipal()
    {
        return $this->hasOne(Direccion::class)->whereRaw('principal = ?', [true]);
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

    public function registradoPorAdministrador()
    {
        return $this->belongsTo(Administrador::class, 'registrado_por_administrador_id');
    }

    public function sucursalRegistro()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_registro_id');
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

    /**
     * Verificar si el usuario es mayor de edad
     */
    public function isMayorDeEdad(): bool
    {
        if (!$this->fecha_nacimiento) {
            return false;
        }
        return $this->fecha_nacimiento->diffInYears(now()) >= 18;
    }

    /**
     * Verificar si el email está verificado
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Marcar el email como verificado
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }
}