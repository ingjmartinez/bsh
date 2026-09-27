<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoUsuario extends Model
{
    /** @use HasFactory<\Database\Factories\MovimientoUsuarioFactory> */
    use HasFactory;

    protected $fillable = [
        'empleado_id', 'cedula', 'nombre_empleado',
        'terminal_origen', 'centro_costo_origen', 'grupo_origen',
        'terminal_destino', 'centro_costo_destino', 'grupo_destino',
        'observacion', 'registrado_por',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
