<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Culto extends Model
{
    protected $table = 'cultos';

    protected $fillable = [
    'nombre_culto',
    'caracter',        // ← agregar
    'fecha',
    'descripcion',
    'mensaje',
    'mensaje_autor_id',
];

// Agregar este método
public static function caracteres(): array
{
    return [
        'evangelistico'   => 'Culto Evangelístico',
        'escuela_dom'     => 'Escuela Dominical',
        'jovenes'         => 'Culto de Jóvenes',
        'damas_dorcas'    => 'Culto de Damas Dorcas',
        'caballeros'      => 'Culto de Caballeros',
        'familia'         => 'Culto de Familia',
        'parejas'         => 'Culto de Parejas',
        'dominical'       => 'Culto Dominical',
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

    public function getEstadoAttribute(): string
    {
        if ($this->fecha->isPast()) return 'realizado';
        if ($this->fecha->isToday()) return 'hoy';
        return 'programado';
    }
}