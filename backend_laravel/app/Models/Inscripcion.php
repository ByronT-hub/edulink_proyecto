<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Pago;
use App\Models\Certificado; // <-- 🔥 IMPORTANTE, FALTABA

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';

    protected $fillable = [
        'estudiante_id',
        'curso_id',
        'estado'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function certificado()
    {
        return $this->hasOne(Certificado::class);
    }
}
