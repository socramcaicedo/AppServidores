<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensajePastoral extends Model
{
    protected $table = 'mensajes_pastorales';

    protected $fillable = [
        'culto_id',
        'usuario_id',
        'mensaje',
        'fecha_publicacion',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
    ];

    /**
     * Relación con el culto
     */
    public function culto()
    {
        return $this->belongsTo(Culto::class);
    }

    /**
     * Relación con el usuario (pastor)
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Scope para mensajes de un culto específico, ordenados por fecha
     */
    public function scopeDelCulto($query, $cultoId)
    {
        return $query->where('culto_id', $cultoId)
                    ->orderBy('fecha_publicacion', 'desc');
    }
}
