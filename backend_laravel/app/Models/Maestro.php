<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maestro extends Model
{
    protected $fillable = [
        'nombre',
        'correo',
        'contrasena',
        'especialidad',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación con cursos
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }
}
