<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deshabilitar checks de foreign keys temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Limpiar todas las tablas de usuarios
        DB::table('estudiantes')->truncate();
        DB::table('maestros')->truncate();
        DB::table('users')->truncate();
        
        // También limpiar tablas relacionadas si existen
        DB::table('cursos')->truncate();
        DB::table('inscripciones')->truncate();
        DB::table('pagos')->truncate();
        DB::table('certificados')->truncate();
        
        // Rehabilitar foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "🧹 Todas las tablas de usuarios han sido limpiadas:\n";
        echo "✅ Estudiantes: 0\n";
        echo "✅ Maestros: 0\n";
        echo "✅ Administradores: 0\n";
        echo "✅ Cursos: 0\n";
        echo "✅ Inscripciones: 0\n";
        echo "✅ Pagos: 0\n";
        echo "✅ Certificados: 0\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay reversión para esta operación
        echo "❌ No se puede deshacer el truncate de datos\n";
    }
};
