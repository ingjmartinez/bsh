<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteDiarioRuta extends Model
{
    use HasFactory;

    protected $table = 'reporte_diario_rutas';

    protected $fillable = [
        'fecha',
        'serial_ruta',
        'ruta_id',
        'operador_ruta_id',
        'banco_nombre',
        'entregado',
        'procesado',
        'gasto',
        'diferencia',
        'correo_destino',
        'observacion',
        'comprobante_entregado_path',
        'comprobante_diferencia_path',
        'enviado_operador_at',
    ];

    protected $casts = [
        'fecha' => 'date',
        'entregado' => 'decimal:2',
        'procesado' => 'decimal:2',
        'gasto' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'comprobante_entregado_path' => 'encrypted',
        'comprobante_diferencia_path' => 'encrypted',
        'enviado_operador_at' => 'datetime',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    public function operador()
    {
        return $this->belongsTo(OperadorRuta::class, 'operador_ruta_id');
    }
}
