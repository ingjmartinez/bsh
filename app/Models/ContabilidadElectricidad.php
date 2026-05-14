<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContabilidadElectricidad extends Model
{
    use HasFactory;

    protected $table = 'contabilidad_electricidad';

    protected $fillable = [
        'fecha_factura',
        'empresa',
        'sucursal',
        'contrato',
        'medidor',
        'lectura_anterior',
        'lectura_actual',
        'ajuste_kwh',
        'tarifa_kwh',
        'otros_cargos',
        'impuestos',
        'pagado',
        'fecha_pago',
        'referencia_pago',
        'observacion',
    ];

    protected $casts = [
        'fecha_factura' => 'date',
        'fecha_pago' => 'date',
        'pagado' => 'boolean',
        'lectura_anterior' => 'decimal:3',
        'lectura_actual' => 'decimal:3',
        'ajuste_kwh' => 'decimal:3',
        'tarifa_kwh' => 'decimal:4',
        'otros_cargos' => 'decimal:2',
        'impuestos' => 'decimal:2',
    ];

    protected $appends = [
        'consumo_kwh',
        'subtotal_energia',
        'total_factura',
    ];

    public function getConsumoKwhAttribute(): float
    {
        $consumo = ((float) $this->lectura_actual - (float) $this->lectura_anterior) + (float) $this->ajuste_kwh;
        return round(max(0, $consumo), 3);
    }

    public function getSubtotalEnergiaAttribute(): float
    {
        return round($this->consumo_kwh * (float) $this->tarifa_kwh, 2);
    }

    public function getTotalFacturaAttribute(): float
    {
        return round($this->subtotal_energia + (float) $this->otros_cargos + (float) $this->impuestos, 2);
    }
}
