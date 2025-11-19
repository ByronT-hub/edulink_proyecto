<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'precio',                // <--- este es el precio oficial
        'duracion',
        'categoria',
        'nivel',
        'requisitos',
        'maestro_id',
        'estudiantes_inscritos',
        'calificacion_promedio',
        'total_calificaciones',
        'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',       // precio como decimal
        'duracion' => 'integer',
        'calificacion_promedio' => 'decimal:1',
        'estudiantes_inscritos' => 'integer',
        'total_calificaciones' => 'integer',
        'activo' => 'boolean'
    ];

    // Relación con inscripciones
    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    // Relación CORREGIDA con certificados a través de inscripciones
    public function certificados()
    {
        return $this->hasManyThrough(
            Certificado::class,
            Inscripcion::class,
            'curso_id',        // FK en inscripciones
            'inscripcion_id',  // FK en certificados
            'id',              // PK en cursos
            'id'               // PK en inscripciones
        );
    }

    // Relación con maestro
    public function maestro(): BelongsTo
    {
        return $this->belongsTo(Maestro::class);
    }
}
