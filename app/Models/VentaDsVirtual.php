<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDsVirtual extends Model
{
    /** @use HasFactory<\Database\Factories\VentaDsVirtualFactory> */
    use HasFactory;

    protected $table = 'ventas_ds_virtual';

    protected $fillable = [
        'consorcio_id',
        'fecha',
        'agencia_id',
        'ventas',
        'premios_pagados',
        'proveedor_id',
        'premios',
        'proveedor_nombre',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'ventas' => 'decimal:2',
            'premios_pagados' => 'decimal:2',
            'premios' => 'decimal:2',
        ];
    }
}
