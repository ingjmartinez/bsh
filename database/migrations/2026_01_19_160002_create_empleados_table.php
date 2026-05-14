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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('companyid');
            $table->string('empleadoid')->unique();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('idposicion')->nullable();
            $table->string('posicion')->nullable();
            $table->decimal('salariomensual', 10, 2)->nullable();
            $table->string('iddepto')->nullable();
            $table->string('depto')->nullable();
            $table->string('idciudad')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('idpais')->nullable();
            $table->string('pais')->nullable();
            $table->string('ctabanco')->nullable();
            $table->string('tipodocidentidad')->nullable();
            $table->string('cedula')->unique();
            $table->string('sexo')->nullable();
            $table->string('estadocivil')->nullable();
            $table->integer('nohijos')->nullable();
            $table->text('direccion')->nullable();
            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();
            $table->string('email')->nullable();
            $table->string('profesion1')->nullable();
            $table->string('profesion2')->nullable();
            $table->date('fechanacimiento')->nullable();
            $table->date('fechaingreso')->nullable();
            $table->date('fechasalida')->nullable();
            $table->date('iniciovacaciones')->nullable();
            $table->date('finalvacaciones')->nullable();
            $table->string('clienteid')->nullable();
            $table->string('codigovendedor')->nullable();
            $table->boolean('chofer')->nullable();
            $table->boolean('bombero')->nullable();
            $table->string('creadopor')->nullable();
            $table->string('modificadopor')->nullable();
            $table->timestamp('fechagrabado')->nullable();
            $table->timestamp('fechamodificado')->nullable();
            $table->string('atributoprn')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
