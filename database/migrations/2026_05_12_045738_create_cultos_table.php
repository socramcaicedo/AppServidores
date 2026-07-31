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
        Schema::create('cultos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_culto');
            $table->enum('caracter', ['evangelistico', 'escuela_dominical', 'jovenes', 'damas_dorcas', 'damas_jovenes', 'mision_juvenil', 'caballeros', 'familia', 'parejas', 'culto_oracion'])->default('evangelistico');
            $table->dateTime('fecha');
            $table->text('descripcion')->nullable();
            $table->text('mensaje')->nullable();
            $table->foreignId('mensaje_autor_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultos');
    }
};
