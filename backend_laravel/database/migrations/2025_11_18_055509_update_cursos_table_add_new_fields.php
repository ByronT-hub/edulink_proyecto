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
            // Verificar si las columnas ya existen antes de agregarlas
            if (!Schema::hasColumn('cursos', 'precio')) {
                $table->decimal('precio', 10, 2)->after('descripcion')->nullable();
            }
            
            if (!Schema::hasColumn('cursos', 'duracion')) {
                $table->integer('duracion')->after('precio')->nullable()->comment('Duración en horas');
            }
            
            if (!Schema::hasColumn('cursos', 'categoria')) {
                $table->string('categoria')->after('duracion')->nullable();
            }
            
            if (!Schema::hasColumn('cursos', 'nivel')) {
                $table->enum('nivel', ['principiante', 'intermedio', 'avanzado'])->after('categoria')->default('principiante');
            }
            
            if (!Schema::hasColumn('cursos', 'requisitos')) {
                $table->text('requisitos')->after('nivel')->nullable();
            }
            
            // Solo agregar maestro_id si no existe
            if (!Schema::hasColumn('cursos', 'maestro_id')) {
                $table->unsignedBigInteger('maestro_id')->after('requisitos')->nullable();
                $table->foreign('maestro_id')->references('id')->on('maestros')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('cursos', 'estudiantes_inscritos')) {
                $table->integer('estudiantes_inscritos')->after('maestro_id')->default(0);
            }
            
            if (!Schema::hasColumn('cursos', 'calificacion_promedio')) {
                $table->decimal('calificacion_promedio', 2, 1)->after('estudiantes_inscritos')->default(0.0);
            }
            
            if (!Schema::hasColumn('cursos', 'total_calificaciones')) {
                $table->integer('total_calificaciones')->after('calificacion_promedio')->default(0);
            }
        });
        
        echo "✅ Tabla 'cursos' actualizada con nuevos campos\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropForeign(['maestro_id']);
            $table->dropColumn([
                'precio',
                'duracion', 
                'categoria',
                'nivel',
                'requisitos',
                'maestro_id',
                'estudiantes_inscritos',
                'calificacion_promedio',
                'total_calificaciones'
            ]);
        });
    }
};
