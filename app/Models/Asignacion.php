<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignacion extends Model
{
    protected $table = 'asignaciones';

    protected $fillable = [
        'culto_id',
        'servidor_id',
        'rol_servicio',
        'estado',
        'confirmado',
        'motivo_reemplazo',
        'motivo_descripcion',
        'reemplazado_por_id',
        'orden',
    ];

    protected $casts = [
        'confirmado' => 'boolean',
    ];

    public function servidor()
    {
        return $this->belongsTo(Servidor::class, 'servidor_id');
    }

    public function culto()
    {
        return $this->belongsTo(Culto::class, 'culto_id');
    }

    /**
     * Obtiene la lista de motivos de reemplazo predefinidos.
     * Implementa RF8 de la ERS.
     *
     * @return array Lista de motivos con key => valor
     */
    public static function rolesServicio(): array
    {
        return [
            'director_culto'                  => 'Director del culto',
            'predicacion_palabra'             => 'Predicación de la Palabra',
            'apertura_servicio'               => 'Apertura del servicio',
            'lectura_biblica_inicial'         => 'Lectura bíblica inicial',
            'oracion_apertura'                => 'Oración de apertura',
            'canto_coros_congregacionales'    => 'Canto de coros congregacionales',
            'canto_himnos_congregacionales'   => 'Canto de himnos congregacionales',
            'canto_alabanzas_especiales'      => 'Canto de alabanzas especiales',
            'especial_musical_solista'        => 'Especial musical (solista)',
            'especial_musical_grupo'          => 'Especial musical (grupo)',
            'participacion_coro'              => 'Participación del coro',
            'testimonios'                     => 'Testimonios',
            'lectura_peticiones_oracion'      => 'Lectura de peticiones de oración',
            'oracion_necesidades_especiales'  => 'Oración por necesidades especiales',
            'oracion_enfermos'                => 'Oración por enfermos',
            'recoleccion_ofrenda'             => 'Recolección de la ofrenda',
            'presentacion_visitantes'         => 'Presentación de visitantes',
            'bienvenida_visitantes'           => 'Bienvenida a los visitantes',
            'anuncios_congregacion'           => 'Anuncios de la congregación',
            'lectura_informes_actividades'    => 'Lectura de informes o actividades',
            'reconocimientos_especiales'      => 'Reconocimientos especiales',
            'presentacion_predicador'         => 'Presentación del predicador',
            'oracion_predicador'              => 'Oración por el predicador',
            'llamado_altar'                   => 'Llamado al altar',
            'oracion_nuevos_creyentes'        => 'Oración por los nuevos creyentes',
            'despedida_congregacion'          => 'Despedida de la congregación',
        ];
    }

    /**
     * Obtiene la lista de motivos de reemplazo predefinidos.
     * Implementa RF8 de la ERS.
     *
     * @return array Lista de motivos con key => valor
     */
    public static function motivosReemplazo(): array
    {
        return [
            'inconveniente_personal' => 'Inconveniente personal',
            'tema_salud' => 'Tema de salud',
            'tema_familiar' => 'Tema familiar',
            'fuera_ciudad' => 'No se encuentra en la ciudad',
            'no_confirmo' => 'No confirmó asistencia',
            'otro' => 'Otro motivo',
        ];
    }

    /**
     * Relación: Una asignación puede ser reemplazada por otra.
     */
    public function reemplazadoPor()
    {
        return $this->belongsTo(Asignacion::class, 'reemplazado_por_id');
    }

    /**
     * Relación: Una asignación puede reemplazar a otra.
     */
    public function reemplazaA()
    {
        return $this->hasOne(Asignacion::class, 'reemplazado_por_id');
    }
}