<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culto_id')->constrained('cultos')->onDelete('cascade');
            $table->foreignId('servidor_id')->constrained('servidores')->onDelete('cascade');
            $table->string('rol_servicio');
            $table->enum('estado', ['asignado', 'reemplazado', 'cancelado'])->default('asignado');
            $table->boolean('confirmado')->default(false);
            $table->enum('motivo_reemplazo', ['inconveniente_personal', 'tema_salud', 'tema_familiar', 'fuera_ciudad', 'no_confirmo', 'otro'])->nullable();
            $table->text('motivo_descripcion')->nullable();
            $table->foreignId('reemplazado_por_id')->nullable()->constrained('asignaciones')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
