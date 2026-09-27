<?php

namespace App\Models;

use Database\Factories\AgenciaDeltaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgenciaDelta extends Model
{
    /** @use HasFactory<AgenciaDeltaFactory> */
    use HasFactory;

    protected $table = 'agencias_delta';

    protected $primaryKey = 'id';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'agencia',
        'codigo',
        'nombre_agencia',
        'nombre',
        'terminal',
        'horario_am',
        'horario_pm',
        'sistema',
        'empresa',
        'ciudad',
        'ruta',
        'operador',
        'coordinador',
        'grupo',
        'central',
        'gerente_de_servicio',
        'tipo_pago',
        'estatus',
        'aplica_incentivo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'estatus' => 'integer',
            'aplica_incentivo' => 'boolean',
        ];
    }
}
