<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Estudiante extends Authenticatable
{
    use HasFactory;

    protected $table = 'estudiantes';

    protected $fillable = [
        'nombre',
        'correo',
        'contrasena',
        'telefono',
        'carnet',
    ];

    protected $hidden = ['contrasena'];

    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function certificados()
    {
        return $this->hasManyThrough(Certificado::class, Inscripcion::class);
    }
}
