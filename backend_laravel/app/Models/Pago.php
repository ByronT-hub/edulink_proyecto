<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'inscripcion_id',
        'monto_centavos',
        'moneda',
        'estado',
        'codigo_autorizacion',
        'mensaje',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }
}
