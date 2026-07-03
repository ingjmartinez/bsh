<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OperadorRuta extends Model
{
    use HasFactory;

    protected $table = 'operador_ruta';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo',
        'email',
        'cedula',
        'telefono',
        'activo',
        'puesto',
    ];

    private static ?string $resolvedTableName = null;

    public static function resolveTableName(): string
    {
        if (self::$resolvedTableName !== null) {
            return self::$resolvedTableName;
        }

        $candidatas = ['operador_ruta', 'operadores_ruta'];

        foreach ($candidatas as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                return self::$resolvedTableName = $table;
            }
        }

        foreach ($candidatas as $table) {
            if (Schema::hasTable($table)) {
                return self::$resolvedTableName = $table;
            }
        }

        return self::$resolvedTableName = 'operador_ruta';
    }

    public static function hasResolvedColumn(string $column): bool
    {
        return Schema::hasColumn(static::resolveTableName(), $column);
    }

    public static function queryConNombreApellidoPuesto()
    {
        $query = static::query()->select('nombre');

        $query->addSelect(static::hasResolvedColumn('apellido') ? 'apellido' : DB::raw("'' AS apellido"));
        $query->addSelect(static::hasResolvedColumn('puesto') ? 'puesto' : DB::raw("'operador' AS puesto"));

        return $query;
    }

    public static function queryConIdentidad()
    {
        $query = static::query()->select('id', 'nombre');

        $query->addSelect(static::hasResolvedColumn('apellido') ? 'apellido' : DB::raw("'' AS apellido"));

        if (static::hasResolvedColumn('correo')) {
            $query->addSelect('correo');
        } elseif (static::hasResolvedColumn('email')) {
            $query->addSelect(DB::raw('email AS correo'));
        }

        return $query;
    }

    public function getTable()
    {
        return static::resolveTableName();
    }

    public function scopePuesto($query, string $puesto)
    {
        if (static::hasResolvedColumn('puesto')) {
            return $query->where('puesto', $puesto);
        }

        return strtolower($puesto) === 'operador'
            ? $query
            : $query->whereRaw('1 = 0');
    }

    public function getApellidoAttribute(): string
    {
        return (string) ($this->attributes['apellido'] ?? '');
    }

    public function getCorreoAttribute(): ?string
    {
        return $this->attributes['correo'] ?? $this->attributes['email'] ?? null;
    }

    public function getPuestoAttribute(): string
    {
        return (string) ($this->attributes['puesto'] ?? 'operador');
    }

    public function agencias()
    {
        return $this->belongsToMany(
            Agencia::class,
            'operador_ruta_agencia',
            'operador_ruta_id',
            'agencia_id'
        )->withTimestamps();
    }
}
