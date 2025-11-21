<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progreso extends Model
{
    protected $table = 'progresos';
    protected $fillable = [
        'inscripcion_id',
        'lecciones_completadas',
        'porcentaje',
    ];

    protected $casts = [
        'lecciones_completadas' => 'array',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }
}
