<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $table = 'certificados';

    protected $fillable = [
        'inscripcion_id',
        'codigo',
        'url_qr',
        'fecha_emision'
    ];

    protected $appends = ['valido'];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    // 🔥 El frontend usa "valido": siempre será true
    public function getValidoAttribute()
    {
        return true;
    }
}
