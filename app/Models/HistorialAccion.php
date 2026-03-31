<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialAccion extends Model
{
    use HasFactory;

    protected $table = 'historial_acciones';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'descripcion',
        'registro_id',
        'tabla_afectada',
        'ip_usuario',
        'user_agent',
        'fecha_accion'
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    protected static function booted()
{
    static::creating(function ($model) {
        $model->fecha_accion = now();
    });
}
}
