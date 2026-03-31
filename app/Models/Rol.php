<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre_rol',
        'descripcion',
        'estado',
    ];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }

    public function permisos()
    {
        return $this->belongsToMany(
            Permiso::class,
            'rol_permisos',
            'rol_id',
            'permiso_id'
        );
    }
}