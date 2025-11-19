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
        Schema::table('cursos', function (Blueprint $table) {
            // Eliminar columnas duplicadas/innecesarias de la estructura original
            if (Schema::hasColumn('cursos', 'costo_centavos')) {
                $table->dropColumn('costo_centavos');
            }
            
            if (Schema::hasColumn('cursos', 'fecha_inicio')) {
                $table->dropColumn('fecha_inicio');
            }
            
            if (Schema::hasColumn('cursos', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
        });
        
        echo "✅ Estructura de la tabla 'cursos' limpiada:\n";
        echo "  - Removido: costo_centavos (duplicado de precio)\n";
        echo "  - Removido: fecha_inicio (no necesario)\n";
        echo "  - Removido: fecha_fin (no necesario)\n";
        echo "  - Mantenido: precio, duracion, categoria, nivel, requisitos, maestro_id\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->integer('costo_centavos')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
        });
    }
};
