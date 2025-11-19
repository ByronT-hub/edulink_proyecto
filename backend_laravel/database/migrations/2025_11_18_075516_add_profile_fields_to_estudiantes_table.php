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
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('telefono');
            $table->string('institucion')->nullable()->after('carrera');
            $table->string('nivel_estudios')->nullable()->after('institucion');
            $table->string('ano_estudio')->nullable()->after('nivel_estudios');
            $table->string('ocupacion')->nullable()->after('ano_estudio');
            $table->text('objetivos')->nullable()->after('intereses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_nacimiento',
                'institucion', 
                'nivel_estudios',
                'ano_estudio',
                'ocupacion',
                'objetivos'
            ]);
        });
    }
};
