<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperadorRuta extends Model
{
    use HasFactory;

    protected $table = 'operador_ruta';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'cedula',
        'telefono',
        'puesto',
    ];

    public function agencias()
    {
        return $this->belongsToMany(
            Agencia::class,
            'operador_ruta_agencia',
            'operador_ruta_id',
            'agencia_id'
        )->withTimestamps();
    }
}
