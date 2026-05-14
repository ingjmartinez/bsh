<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $fillable = [
        'nombre_ruta',
        'empresa',
        'operador_ruta_id',
    ];

    public function setNombreRutaAttribute($value): void
    {
        $this->attributes['nombre_ruta'] = mb_strtoupper((string) $value, 'UTF-8');
    }

    public function operadorAsignado()
    {
        return $this->belongsTo(OperadorRuta::class, 'operador_ruta_id');
    }

    public function agencias()
    {
        return $this->belongsToMany(
            Agencia::class,
            'ruta_agencia',
            'ruta_id',
            'agencia_id'
        )->withTimestamps();
    }
}
