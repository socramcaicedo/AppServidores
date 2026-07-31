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
        Schema::create('mensajes_pastorales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('culto_id')->constrained('cultos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->text('mensaje');
            $table->timestamp('fecha_publicacion')->useCurrent();
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index('culto_id');
            $table->index('usuario_id');
            $table->index('fecha_publicacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes_pastorales');
    }
};
