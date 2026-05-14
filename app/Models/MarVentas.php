<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarVentas extends Model
{
    protected $table = 'mar_ventas';
    public $timestamps = false;
    protected $primaryKey = 'VentaID';
    protected $fillable = [
        'Dia',
        'EDiFecha',
        'GrupoID',
        'GruNombre',
        'RiferoID',
        'RifNombre',
        'BancaID',
        'BanNombre',
        'BanContacto',
        'BanComisionQ',
        'BanComisionP',
        'BanComisionT',
        'BanVComision',
        'PagoDeOtra',
        'PagoEnOtra',
        'PagosPendiente',
        'DiasPendiente',
        'VTarjComisionBanca',
        'VTarjComision',
        'VTarjetas',
        'CVQuinielas',
        'VQuinielas',
        'CVPales',
        'CVTripletas',
        'VPales',
        'VTripletas',
        'CPrimero',
        'CSegundo',
        'CTercero',
        'CPales',
        'CTripletas',
        'MPrimero',
        'MSegundo',
        'MTercero',
        'MPales',
        'MTripletas',
        'RifDescuento',
        'ISRRetenido',
    ];
}
