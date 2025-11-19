<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';

    protected $fillable = [
        'estudiante_id',
        'curso_id',
        'estado',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function pago()
    {
        return $this->hasOne(Pago::class);
    }

    public function certificado()
    {
        return $this->hasOne(Certificado::class);
    }
}
