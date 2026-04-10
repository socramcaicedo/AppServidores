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
    ];

    public function servidor()
    {
        return $this->belongsTo(Servidor::class, 'servidor_id');
    }

    public function culto()
    {
        return $this->belongsTo(Culto::class, 'culto_id');
    }
}