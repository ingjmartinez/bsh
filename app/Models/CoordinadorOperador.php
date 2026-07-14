<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoordinadorOperador extends Model
{
    use HasFactory;

    protected $table = 'coordinadores_operador';

    protected $fillable = [
        'nombre',
        'apellido',
        'cargo',
        'cedula',
        'telefono',
        'correo',
        'email',
        'activo',
        'puesto',
        'user_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    private static ?string $resolvedTableName = null;

    public static function resolveTableName(): string
    {
        if (self::$resolvedTableName !== null) {
            return self::$resolvedTableName;
        }

        $candidatas = ['coordinador_operador', 'coordinadores_operador'];

        foreach ($candidatas as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                return self::$resolvedTableName = $table;
            }
        }

        foreach ($candidatas as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'puesto')) {
                return self::$resolvedTableName = $table;
            }
        }

        foreach ($candidatas as $table) {
            if (Schema::hasTable($table)) {
                return self::$resolvedTableName = $table;
            }
        }

        return self::$resolvedTableName = 'coordinador_operador';
    }

    public static function hasResolvedColumn(string $column): bool
    {
        return Schema::hasColumn(static::resolveTableName(), $column);
    }

    public static function queryConNombreApellidoPuesto()
    {
        $query = static::query()->select('nombre');

        $query->addSelect(static::hasResolvedColumn('apellido') ? 'apellido' : DB::raw("'' AS apellido"));
        $query->addSelect(static::hasResolvedColumn('puesto') ? 'puesto' : DB::raw("'coordinador' AS puesto"));

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

        return strtolower($puesto) === 'coordinador'
            ? $query
            : $query->whereRaw('1 = 0');
    }

    public function getCedulaAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cedula = preg_replace('/\D/', '', (string) $value);

        return $cedula === '' ? null : str_pad($cedula, 11, '0', STR_PAD_LEFT);
    }

    public function getApellidoAttribute(): string
    {
        return (string) ($this->attributes['apellido'] ?? '');
    }

    public function getCorreoAttribute(): ?string
    {
        return $this->attributes['email'] ?? $this->attributes['correo'] ?? null;
    }

    public function getPuestoAttribute(): string
    {
        return (string) ($this->attributes['puesto'] ?? 'coordinador');
    }

    public function agencias()
    {
        return $this->belongsToMany(
            Agencia::class,
            'coordinador_operador_agencia',
            'coordinador_operador_id',
            'agencia_id'
        )->withTimestamps();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
