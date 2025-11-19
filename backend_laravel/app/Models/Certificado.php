<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    use HasFactory;

    protected $table = 'certificados';

    protected $fillable = [
        'inscripcion_id',
        'codigo',
        'url_qr',
        'fecha_emision',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }
}
