<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PromocionOppen extends Model
{
    protected $table = 'promociones_oppen';

    protected $fillable = [
        'oppen_code',
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'dias_semana',
        'horarios',
        'condiciones',
        'acciones',
        'combinable',
        'datos_raw',
        'ultima_sincronizacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'dias_semana' => 'array',
        'horarios' => 'array',
        'condiciones' => 'array',
        'acciones' => 'array',
        'combinable' => 'boolean',
        'datos_raw' => 'array',
        'ultima_sincronizacion' => 'datetime',
    ];

    /**
     * Promociones activas y dentro de rango de fechas.
     */
    public function scopeVigentes($query)
    {
        return $query->whereRaw('"activo" = true')
            ->where('fecha_inicio', '<=', now()->toDateString())
            ->where('fecha_fin', '>=', now()->toDateString());
    }

    /**
     * Promociones vigentes + disponibles hoy (día de la semana y horario).
     */
    public function scopeActivasHoy($query)
    {
        $diaIngles = now()->format('l'); // Monday, Tuesday, etc.

        return $query->vigentes()
            ->where(function ($q) use ($diaIngles) {
                $q->whereNull('dias_semana')
                  ->orWhereRaw("(dias_semana->>?) = 'true'", [$diaIngles]);
            });
    }

    /**
     * Limpia el HTML de la descripción (campo Communication de Oppen).
     */
    public function getDescripcionLimpiaAttribute(): string
    {
        if (empty($this->descripcion)) {
            return '';
        }
        return trim(strip_tags(html_entity_decode($this->descripcion)));
    }

    /**
     * Genera un resumen legible del tipo de acción (descuento).
     */
    public function getResumenAccionAttribute(): string
    {
        if (empty($this->acciones) || !is_array($this->acciones)) {
            return 'Promoción';
        }

        $accion = $this->acciones[0] ?? null;
        if (!$accion) {
            return 'Promoción';
        }

        $label = $accion['label'] ?? '';
        if ($label) {
            return $label;
        }

        $tipo = $accion['type'] ?? '';
        $subtipo = $accion['subtype'] ?? '';

        if ($tipo === 'Discount' && $subtipo === 'BxGy') {
            $each = $accion['perEach'] ?? '?';
            $free = $accion['freeUnits'] ?? '?';
            $paga = (int) $each - (int) $free;
            return "{$each}x{$paga}";
        }

        return $label ?: 'Promoción especial';
    }

    /**
     * Horarios disponibles en formato legible.
     */
    public function getHorarioTextoAttribute(): string
    {
        if (empty($this->horarios) || !is_array($this->horarios)) {
            return 'Todo el día';
        }

        $partes = [];
        foreach ($this->horarios as $h) {
            $from = substr($h['from'] ?? '00:00:00', 0, 5);
            $to = substr($h['to'] ?? '23:59:59', 0, 5);
            if ($from === '00:00' && $to === '23:59') {
                return 'Todo el día';
            }
            $partes[] = "{$from} - {$to}";
        }

        return implode(', ', $partes);
    }

    /**
     * Días de la semana activos en formato legible abreviado.
     */
    public function getDiasActivosAttribute(): array
    {
        $mapa = [
            'Monday'    => 'Lun',
            'Tuesday'   => 'Mar',
            'Wednesday' => 'Mié',
            'Thursday'  => 'Jue',
            'Friday'    => 'Vie',
            'Saturday'  => 'Sáb',
            'Sunday'    => 'Dom',
        ];

        if (empty($this->dias_semana) || !is_array($this->dias_semana)) {
            return array_values($mapa); // Todos los días
        }

        $activos = [];
        foreach ($mapa as $en => $es) {
            if (!empty($this->dias_semana[$en])) {
                $activos[] = $es;
            }
        }

        return $activos ?: array_values($mapa);
    }

    /**
     * Verifica si la promoción es válida ahora mismo (fecha, día, hora).
     */
    public function estaDisponibleAhora(): bool
    {
        if (!$this->activo) {
            return false;
        }

        $ahora = now();

        if ($ahora->lt($this->fecha_inicio) || $ahora->gt($this->fecha_fin)) {
            return false;
        }

        // Verificar día
        $dia = $ahora->format('l');
        if (!empty($this->dias_semana) && empty($this->dias_semana[$dia])) {
            return false;
        }

        // Verificar horario
        if (!empty($this->horarios) && is_array($this->horarios)) {
            $horaActual = $ahora->format('H:i:s');
            foreach ($this->horarios as $rango) {
                $from = $rango['from'] ?? '00:00:00';
                $to = $rango['to'] ?? '23:59:59';
                if ($horaActual >= $from && $horaActual <= $to) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Crea o actualiza una promoción a partir de datos crudos de la API Oppen.
     */
    public static function sincronizarDesdeOppen(array $data): self
    {
        $diasSemana = [
            'Monday'    => $data['Monday'] ?? true,
            'Tuesday'   => $data['Tuesday'] ?? true,
            'Wednesday' => $data['Wednesday'] ?? true,
            'Thursday'  => $data['Thursday'] ?? true,
            'Friday'    => $data['Friday'] ?? true,
            'Saturday'  => $data['Saturday'] ?? true,
            'Sunday'    => $data['Sunday'] ?? true,
        ];

        $activo = !($data['Closed'] ?? false) && ($data['Status'] ?? 0) === 1;
        $combinable = (bool) ($data['Combinable'] ?? false);

        return self::updateOrCreate(
            ['oppen_code' => $data['Code']],
            [
                'nombre'                => $data['Name'] ?? 'Sin nombre',
                'descripcion'           => $data['Communication'] ?? null,
                'fecha_inicio'          => $data['FromDate'] ?? now()->toDateString(),
                'fecha_fin'             => $data['ToDate'] ?? now()->toDateString(),
                'activo'                => \DB::raw($activo ? 'true' : 'false'),
                'dias_semana'           => $diasSemana,
                'horarios'              => $data['AvailableTimes'] ?? null,
                'condiciones'           => $data['PromotionConditions'] ?? null,
                'acciones'              => $data['Actions'] ?? null,
                'combinable'            => \DB::raw($combinable ? 'true' : 'false'),
                'datos_raw'             => $data,
                'ultima_sincronizacion' => now(),
            ]
        );
    }
}
