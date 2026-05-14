<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoJuego extends Model
{
    protected $table = 'catalogo_juegos';

    public $timestamps = true;

    protected $fillable = [
        'producto_id',
        'descripcion',
        'tipo',
        'sistema',
        'activo',
    ];

    protected $casts = [
        'producto_id' => 'integer',
        'activo' => 'boolean',
    ];
}
