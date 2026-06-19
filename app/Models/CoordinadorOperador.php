<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CoordinadorOperador extends Model
{
    use HasFactory;

    protected $table = 'coordinadores_operador';

    protected $fillable = [
        'nombre',
        'cedula',
        'telefono',
        'email',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public static function resolveTableName(): string
    {
        foreach (['coordinadores_operador', 'coordinador_operador'] as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return 'coordinadores_operador';
    }

    public static function hasResolvedColumn(string $column): bool
    {
        return Schema::hasColumn(static::resolveTableName(), $column);
    }

    public function getTable()
    {
        return static::resolveTableName();
    }

    public function getCedulaAttribute($value): string
    {
        $cedula = preg_replace('/\D/', '', (string) $value);

        return str_pad($cedula, 11, '0', STR_PAD_LEFT);
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
}
