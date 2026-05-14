<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detalle_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('external_key', 64)->unique();
            $table->string('cuenta', 50)->index();
            $table->string('no_asiento', 50)->nullable();
            $table->date('fecha')->nullable()->index();
            $table->string('fecha_raw', 50)->nullable();
            $table->string('ref', 50)->nullable();
            $table->string('no_ref', 50)->nullable();
            $table->decimal('debito', 18, 2)->nullable();
            $table->decimal('credito', 18, 2)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('grupo', 100)->nullable();
            $table->string('sub_grupo', 100)->nullable();
            $table->string('division', 100)->nullable();
            $table->string('centro_costo', 100)->nullable();
            $table->string('conciliado', 20)->nullable();
            $table->string('modulo', 50)->nullable();
            $table->string('fecha_grabado', 50)->nullable();
            $table->string('fecha_modificado', 50)->nullable();
            $table->string('creado_por', 100)->nullable();
            $table->string('modificado_por', 100)->nullable();
            $table->string('ref_desc', 255)->nullable();
            $table->string('sociedad', 100)->nullable();
            $table->timestamps();

            $table->index(['cuenta', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_cuentas');
    }
};
