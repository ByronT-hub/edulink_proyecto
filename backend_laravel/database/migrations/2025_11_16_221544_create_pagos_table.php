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
    Schema::create('pagos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
        $table->integer('monto_centavos');
        $table->string('moneda')->default('GTQ'); // o USD, etc.
        $table->string('estado');                // approved, rejected, pending, etc.
        $table->string('codigo_autorizacion')->nullable();
        $table->text('mensaje')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
