<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna "orden" a las asignaciones para poder definir
     * el orden del culto (primero a último) que se refleja en el PDF.
     */
    public function up(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->unsignedInteger('orden')->nullable()->after('reemplazado_por_id');
            $table->index(['culto_id', 'orden']);
        });

        // Backfill: numerar las asignaciones existentes 1..N por culto,
        // conservando el orden de creación (id) como punto de partida.
        \App\Models\Asignacion::orderBy('culto_id')->orderBy('id')->chunk(500, function ($asignaciones) {
            foreach ($asignaciones->groupBy('culto_id') as $grupo) {
                foreach ($grupo->values() as $i => $asignacion) {
                    if ($asignacion->orden === null) {
                        $asignacion->forceFill(['orden' => $i + 1])->saveQuietly();
                    }
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropIndex(['culto_id', 'orden']);
            $table->dropColumn('orden');
        });
    }
};
