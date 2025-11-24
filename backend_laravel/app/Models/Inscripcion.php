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

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'inscripcion_id');
    }

    // 🔥 RELACIÓN CORRECTA — ESTA ERA LA QUE FALTABA
    public function progreso()
    {
        return $this->hasOne(Progreso::class, 'inscripcion_id');
    }
}
