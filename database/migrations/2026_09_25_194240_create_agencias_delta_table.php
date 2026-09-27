<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('agencias_delta')) {
            Schema::create('agencias_delta', function (Blueprint $table): void {
                $table->id();
                $table->string('agencia', 25)->nullable();
                $table->string('codigo', 25)->nullable();
                $table->string('nombre_agencia', 55)->nullable();
                $table->string('nombre', 55)->nullable();
                $table->string('terminal', 25)->nullable();
                $table->string('horario_am', 35)->nullable();
                $table->string('horario_pm', 35)->nullable();
                $table->string('sistema', 55)->nullable();
                $table->string('empresa', 60)->nullable();
                $table->string('ciudad', 55)->nullable();
                $table->string('ruta', 55)->nullable();
                $table->string('operador', 55)->nullable();
                $table->string('coordinador', 55)->nullable();
                $table->string('grupo', 75)->nullable();
                $table->string('central', 75)->nullable();
                $table->string('gerente_de_servicio', 75)->nullable();
                $table->string('tipo_pago', 75)->nullable();
                $table->tinyInteger('estatus')->default(1);
                $table->boolean('aplica_incentivo')->default(true);
                $table->timestamps();

                $table->unique('terminal');
                $table->index('agencia');
                $table->index('codigo');
                $table->index('estatus');
            });
        }

        if (! Schema::hasTable('agencias_lotedom')) {
            return;
        }

        $columns = [
            'agencia', 'codigo', 'nombre_agencia', 'nombre', 'terminal', 'horario_am', 'horario_pm',
            'sistema', 'empresa', 'ciudad', 'ruta', 'operador', 'coordinador', 'estatus',
            'aplica_incentivo', 'created_at', 'updated_at',
        ];

        DB::table('agencias_lotedom')
            ->where(function ($query): void {
                $query
                    ->whereRaw('LOWER(COALESCE(sistema, "")) LIKE ?', ['%delta%'])
                    ->orWhereRaw('LOWER(COALESCE(empresa, "")) LIKE ?', ['%delta%']);
            })
            ->orderBy('id')
            ->get($columns)
            ->chunk(500)
            ->each(function ($chunk): void {
                DB::table('agencias_delta')->insertOrIgnore(
                    $chunk->map(fn ($row): array => (array) $row)->all()
                );
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencias_delta');
    }
};
