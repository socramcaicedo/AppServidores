<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servidor extends Model
{
    protected $table = 'servidores';

    protected $fillable = [
        'nombre_completo',
        'idgenero',
        'cargo',
        'telefono',
        'estado',
        'fecha_nacimiento',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function getEdadAttribute(): int
    {
        if (!$this->fecha_nacimiento) {
            return 0;
        }

        return $this->fecha_nacimiento->age;
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class, 'idgenero');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'servidor_id');
    }

    public function asignacionesActivas()
    {
        return $this->hasMany(Asignacion::class, 'servidor_id')->where('estado', 'asignado');
    }

    public function ultimaParticipacion()
    {
        return $this->hasOne(Asignacion::class, 'servidor_id')
            ->latestOfMany('created_at');
    }

    public function getLinkWhatsappAttribute(): string
    {
        // 1. Quitar todo lo que no sea dígito (espacios, +, guiones, paréntesis)
        $telefono = preg_replace('/\D/', '', $this->telefono ?? '');

        if ($telefono === '') {
            return '#';
        }

        // 2. Eliminar ceros iniciales sobrantes (formato internacional antiguo "0057...")
        $telefono = ltrim($telefono, '0');

        // 3. Si ya viene con código de país colombiano (12 dígitos empezando en 57), dejarlo
        //    Si tiene 10 dígitos (formato local colombiano), anteponer 57
        //    Si tiene 11 dígitos y empieza con 0, quitar el 0 y anteponer 57
        if (strlen($telefono) === 10) {
            $telefono = '57' . $telefono;
        } elseif (strlen($telefono) === 11 && str_starts_with($telefono, '0')) {
            $telefono = '57' . substr($telefono, 1);
        }

        return 'https://wa.me/' . $telefono;
    }

    public function getEstadoUsoAttribute(): string
    {
        if (!$this->ultimaParticipacion) {
            return 'Sin participaciones';
        }

        $diasDesdeUltima = $this->ultimaParticipacion->created_at->diffInDays(now());

        return match(true) {
            $diasDesdeUltima <= 7 => 'Esta semana',
            $diasDesdeUltima <= 30 => 'Este mes',
            default => 'Hace más de un mes',
        };
    }

    public function getEstadoUsoColorAttribute(): string
    {
        if (!$this->ultimaParticipacion) {
            return '#999999'; // Gris - Sin participaciones
        }

        $diasDesdeUltima = $this->ultimaParticipacion->created_at->diffInDays(now());

        return match(true) {
            $diasDesdeUltima <= 7 => '#C0392B', // Rojo - Usado recientemente
            $diasDesdeUltima <= 30 => '#F5C518', // Amarillo - Usado este mes
            default => '#1A7A4A', // Verde - Disponible hace tiempo
        };
    }
}