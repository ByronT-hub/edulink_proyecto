<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    protected $fillable = [
        'estudiante_id',
        'curso_id',
        'estado'
    ];

    /**
     * 🔥 Estudiante dueño de la inscripción
     */
    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    /**
     * 🔥 Curso inscrito
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * 🔥 Pagos relacionados a la inscripción
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'inscripcion_id');
    }

    /**
     * 🔥 Relación con el progreso del curso
     */
    public function progreso()
    {
        return $this->hasOne(Progreso::class, 'inscripcion_id');
    }

    /**
     * 🔥 Relación que FALTABA para certificados
     * Permite hacer: $inscripcion->certificado
     * Y usar with(['inscripcion.curso','inscripcion.estudiante'])
     */
    public function certificado()
    {
        return $this->hasOne(Certificado::class, 'inscripcion_id');
    }
}
