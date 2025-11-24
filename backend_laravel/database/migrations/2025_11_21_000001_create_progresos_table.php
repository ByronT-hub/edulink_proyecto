<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('progresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inscripcion_id');

            $table->json('lecciones_completadas')->nullable();
            $table->unsignedTinyInteger('porcentaje')->default(0);

            $table->timestamps();

            // 🔥 CORRECCIÓN: NOMBRE REAL DE LA TABLA
            $table->foreign('inscripcion_id')
                  ->references('id')
                  ->on('inscripciones')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progresos');
    }
};
