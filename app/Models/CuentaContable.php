<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaContable extends Model
{
    use HasFactory;

    protected $table = 'cuentas_contables';

    protected $fillable = [
        'company_id',
        'cuenta',
        'descripcion',
        'ctacontrol',
        'tipo',
    ];
}
