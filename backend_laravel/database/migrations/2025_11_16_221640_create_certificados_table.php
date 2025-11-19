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
        Schema::create('certificados', function (Blueprint $table) {
        $table->id();
        $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
        $table->string('codigo')->unique();      // ej: CERT-XYZ123
        $table->string('url_qr');                // URL que apunta a Flask /api/validar/{codigo}
        $table->timestamp('fecha_emision')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
