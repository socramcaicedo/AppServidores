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
    ];

    public function genero()
    {
        return $this->belongsTo(Genero::class, 'idgenero');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'servidor_id');
    }

    public function ultimaParticipacion()
    {
        return $this->hasOne(Asignacion::class, 'servidor_id')
            ->latestOfMany('created_at');
    }

    public function getLinkWhatsappAttribute(): string
    {
        $telefono = preg_replace('/\D/', '', $this->telefono);
        return 'https://wa.me/' . $telefono;
    }
}