<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estructura extends Model
{
    protected $table = 'estructuras';
    protected $fillable = [
        'curso_id',
        'estructura',
    ];
    protected $casts = [
        'estructura' => 'array',
    ];
    public $timestamps = true;
}
