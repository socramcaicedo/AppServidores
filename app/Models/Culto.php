<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Culto extends Model
{
    protected $table = 'cultos';

    protected $fillable = [
        'nombre_culto',
        'caracter',
        'fecha',
        'descripcion',
        'mensaje',
        'mensaje_autor_id',
    ];

    public static function caracteres(): array
    {
        return [
            'evangelistico'     => 'Culto Evangelístico',
            'escuela_dominical' => 'Escuela Dominical',
            'jovenes'           => 'Culto De Jóvenes',
            'damas_dorcas'      => 'Culto De Damas Dorcas',
            'damas_jovenes'     => 'Damas Jóvenes',
            'mision_juvenil'    => 'Misión Juvenil',
            'caballeros'        => 'Culto De Caballeros',
            'familia'           => 'Culto De Familia',
            'parejas'           => 'Culto De Parejas',
            'culto_oracion'     => 'Culto de Oración',
        ];
    }

    public function getCaracterNombreAttribute(): string
    {
        return self::caracteres()[$this->caracter] ?? ucfirst($this->caracter);
    }

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'culto_id');
    }

    public function mensajeAutor()
    {
        return $this->belongsTo(Usuario::class, 'mensaje_autor_id');
    }

    public function mensajesPastorales()
    {
        return $this->hasMany(MensajePastoral::class)->orderBy('fecha_publicacion', 'desc');
    }

    public function getEstadoAttribute(): string
    {
        if (!$this->fecha) return 'sin_fecha';

        // Antes de la hora de inicio
        if ($this->fecha->isFuture()) return 'programado';

        // Entre la hora de inicio y 2 horas después (en pleno desarrollo)
        if ($this->fecha->addHours(2)->isFuture()) return 'hoy';

        // Más de 2 horas después del inicio → completado
        return 'realizado';
    }
}