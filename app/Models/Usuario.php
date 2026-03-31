<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    // Apuntar a tu tabla propia
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
    'estado',       // ← agregar esta línea
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ── Relaciones ──────────────────────────────────────

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // ── Helpers de rol ──────────────────────────────────

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

public function login(Request $request)
{
    $request->validate([
        'usuario'  => 'required|string',
        'password' => 'required|string',
    ]);

    // ↓ La clave del array debe ser el nombre exacto de la columna en BD
    $credenciales = [
        'usuario'  => $request->usuario,
        'password' => $request->password,
    ];

    if (Auth::attempt($credenciales, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()
        ->withInput($request->only('usuario'))
        ->withErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
}





}