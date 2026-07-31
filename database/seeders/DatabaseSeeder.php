<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Géneros ────────────────────────────────────────
        DB::table('genero')->insertOrIgnore([
            ['denominacion' => 'Masculino'],
            ['denominacion' => 'Femenino'],
        ]);

        // ── Roles ─────────────────────────────────────────
        DB::table('roles')->insertOrIgnore([
            ['nombre_rol' => 'secretario_general', 'descripcion' => 'Administrador general', 'estado' => 1],
            ['nombre_rol' => 'lider_comite',       'descripcion' => 'Líder de comité',        'estado' => 1],
            ['nombre_rol' => 'pastor',             'descripcion' => 'Supervisión pastoral',   'estado' => 1],
        ]);

        $idSecretario = DB::table('roles')->where('nombre_rol', 'secretario_general')->value('id');
        $idLider      = DB::table('roles')->where('nombre_rol', 'lider_comite')->value('id');
        $idPastor     = DB::table('roles')->where('nombre_rol', 'pastor')->value('id');

        // ── Permisos ───────────────────────────────────────
        $permisos = [
            ['nombre_permiso' => 'registrar servidores',   'modulo' => 'servidores'],
            ['nombre_permiso' => 'editar servidores',       'modulo' => 'servidores'],
            ['nombre_permiso' => 'ver servidores',          'modulo' => 'servidores'],
            ['nombre_permiso' => 'contactar whatsapp',      'modulo' => 'servidores'],
            ['nombre_permiso' => 'crear orden culto',       'modulo' => 'cultos'],
            ['nombre_permiso' => 'confirmar participacion', 'modulo' => 'cultos'],
            ['nombre_permiso' => 'modificar asignaciones',  'modulo' => 'cultos'],
            ['nombre_permiso' => 'reemplazar servidores',   'modulo' => 'cultos'],
            ['nombre_permiso' => 'registrar motivos',       'modulo' => 'cultos'],
            ['nombre_permiso' => 'ver historial',           'modulo' => 'historial'],
            ['nombre_permiso' => 'ver estadisticas',        'modulo' => 'estadisticas'],
            ['nombre_permiso' => 'control acceso',          'modulo' => 'admin'],
        ];

        DB::table('permisos')->insertOrIgnore($permisos);

        // ── Rol → Permisos ─────────────────────────────────
        $todos    = DB::table('permisos')->pluck('id')->toArray();

        $idsLider = DB::table('permisos')->whereIn('nombre_permiso', [
            'ver servidores', 'contactar whatsapp', 'crear orden culto',
            'confirmar participacion', 'modificar asignaciones',
            'reemplazar servidores', 'registrar motivos', 'ver historial',
        ])->pluck('id')->toArray();

        $idsPastor = DB::table('permisos')->whereIn('nombre_permiso', [
            'ver servidores', 'contactar whatsapp',
            'ver historial', 'ver estadisticas',
        ])->pluck('id')->toArray();

        foreach ($todos as $pid) {
            DB::table('rol_permisos')->insertOrIgnore(['rol_id' => $idSecretario, 'permiso_id' => $pid]);
        }
        foreach ($idsLider as $pid) {
            DB::table('rol_permisos')->insertOrIgnore(['rol_id' => $idLider, 'permiso_id' => $pid]);
        }
        foreach ($idsPastor as $pid) {
            DB::table('rol_permisos')->insertOrIgnore(['rol_id' => $idPastor, 'permiso_id' => $pid]);
        }

        // ── Usuarios de prueba ─────────────────────────────
        DB::table('usuarios')->insertOrIgnore([
            [
                'nombre'     => 'Carlos',
                'apellido'   => 'Ramírez',
                'usuario'    => 'secretario',
                'email'      => 'secretario@ipuc.com',
                'password'   => Hash::make('password123'),
                'genero'     => 'masculino',
                'edad'       => 35,
                'rol_id'     => $idSecretario,
                'estado'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Ana',
                'apellido'   => 'Gómez',
                'usuario'    => 'lider',
                'email'      => 'lider@ipuc.com',
                'password'   => Hash::make('password123'),
                'genero'     => 'femenino',
                'edad'       => 28,
                'rol_id'     => $idLider,
                'estado'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre'     => 'Roberto',
                'apellido'   => 'Torres',
                'usuario'    => 'pastor',
                'email'      => 'pastor@ipuc.com',
                'password'   => Hash::make('password123'),
                'genero'     => 'masculino',
                'edad'       => 50,
                'rol_id'     => $idPastor,
                'estado'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}