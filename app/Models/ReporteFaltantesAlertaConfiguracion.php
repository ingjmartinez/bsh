<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteFaltantesAlertaConfiguracion extends Model
{
    /** @use HasFactory<\Database\Factories\ReporteFaltantesAlertaConfiguracionFactory> */
    use HasFactory;

    protected $table = 'reporte_faltantes_alerta_configuraciones';

    protected $fillable = [
        'minimo_faltantes',
        'maximo_monto',
        'actualizado_por',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'minimo_faltantes' => 'integer',
            'maximo_monto' => 'decimal:2',
        ];
    }
}
