<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'apellido',
        'usuario',
        'password',
        'email',
        'genero',
        'edad',
        'rol_id',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function tieneRol(string $nombreRol): bool
    {
        return $this->rol && strtolower($this->rol->nombre_rol) === strtolower($nombreRol);
    }

    public function tienePermiso(string $nombrePermiso): bool
    {
        if (!$this->rol) return false;

        return $this->rol->permisos()
            ->where('nombre_permiso', $nombrePermiso)
            ->exists();
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }
}
