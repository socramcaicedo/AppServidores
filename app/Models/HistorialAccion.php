<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAccion extends Model
{
    protected $table = 'historial_acciones';

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'descripcion',
        'registro_id',
        'tabla_afectada',
        'ip_usuario',
        'user_agent',
        'fecha_accion',
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}