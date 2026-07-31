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
        Schema::create('historial_acciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('accion');
            $table->string('modulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('tabla_afectada')->nullable();
            $table->string('ip_usuario')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('fecha_accion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_acciones');
    }
};
