<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReporteBonoIncentivoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('catalogo_juegos', function (Blueprint $table): void {
            $table->integer('producto_id');
            $table->string('tipo');
        });

        foreach (['ventas_usuarios_bet', 'ventas_usuarios_net'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('agencia_id');
                $table->string('cedula')->nullable();
                $table->integer('producto_id');
                $table->string('tipo')->nullable();
                $table->decimal('monto', 18, 2);
                $table->date('fecha');
            });
        }

        Schema::create('empleados', function (Blueprint $table): void {
            $table->id();
            $table->integer('companyid');
            $table->integer('empleadoid');
            $table->integer('idcentrocosto')->nullable();
            $table->string('cedula')->nullable();
            $table->string('nombres');
            $table->string('apellidos');
            $table->boolean('estatus')->default(true);
        });

        Schema::create('agencias', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->nullable();
            $table->string('terminal');
            $table->string('nombre')->nullable();
            $table->string('nombre_agencia')->nullable();
            $table->string('sistema');
            $table->string('empresa')->nullable();
            $table->string('ruta')->nullable();
        });

        Schema::create('centros_de_costo', function (Blueprint $table): void {
            $table->id();
            $table->integer('id_centro_costo');
            $table->string('company_id')->nullable();
            $table->string('descripcion');
            $table->string('id_grupo')->nullable();
            $table->string('id_division')->nullable();
        });

        Schema::create('faltantes_bet', function (Blueprint $table): void {
            $table->id();
            $table->string('observacion');
            $table->decimal('monto', 18, 2);
            $table->date('fecha');
        });
    }

    protected function tearDown(): void
    {
        foreach ([
            'faltantes_bet',
            'centros_de_costo',
            'agencias',
            'empleados',
            'ventas_usuarios_net',
            'ventas_usuarios_bet',
            'catalogo_juegos',
        ] as $tableName) {
            Schema::dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_bonus_report_route_is_registered_and_exposed_in_the_view(): void
    {
        $this->assertTrue(Route::has('incentivos.reporte-bonos.index'));
        $this->assertTrue(Route::has('incentivos.reporte-bonos.datos'));

        $html = view('incentivos.reporte-bonos')->render();
        $calculationHtml = view('incentivos.incentivos-administrativos', [
            'coordinadores' => collect(),
            'administrativosConfig' => collect(),
            'terminalesExcluidasIncentivo' => collect(),
        ])->render();

        $this->assertStringContainsString('Reporte de Bonos', $html);
        $this->assertStringContainsString('id="btnGenerarReporteBonos"', $html);
        $this->assertStringContainsString('id="reporte_bonos_fecha_ini"', $html);
        $this->assertStringContainsString('id="reporte_bonos_fecha_fin"', $html);
        $this->assertStringContainsString('id="reporte_bonos_sistema"', $html);
        $this->assertStringContainsString('Venta externa pendiente de integración', $html);
        $this->assertStringNotContainsString('btnGenerarReporteBono', $calculationHtml);

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $headers = (new \DOMXPath($document))->query('//table[@id="tablaReporteBonos"]/thead/tr/th');
        $columnNames = collect($headers)->map(fn (\DOMNode $header): string => trim($header->textContent))->all();

        $this->assertSame([
            'Cédula',
            'Nombre',
            'Empleada',
            'Centro de Costo',
            'Division',
            'Grupo',
            'Ruta',
            'No Tradicionales',
            'VentaExterna',
            'Total',
            'Incentivo',
            'Faltante',
        ], $columnNames);

        $module = collect(config('module_hubs.incentivos.items'))
            ->firstWhere('url', '/incentivos/reporte-bonos');

        $this->assertNotNull($module);
        $this->assertSame('Reporte de Bonos', $module['nombre']);
        $this->assertSame('module.incentivos.item.reporte_de_bonos.view', $module['permission']);

        $this->withoutMiddleware()
            ->get(route('incentivos.reporte-bonos.index'))
            ->assertOk()
            ->assertSee('Reporte de Bonos');
    }

    public function test_bonus_report_calculates_nontraditional_sales_and_leaves_external_sales_ready(): void
    {
        DB::table('catalogo_juegos')->insert([
            ['producto_id' => 10, 'tipo' => 'No Tradicional'],
            ['producto_id' => 20, 'tipo' => 'Tradicional'],
        ]);
        DB::table('ventas_usuarios_bet')->insert([
            ['agencia_id' => '100', 'cedula' => '00123456789', 'producto_id' => 10, 'tipo' => 'No Tradicional', 'monto' => 1000, 'fecha' => '2026-07-10'],
            ['agencia_id' => '100', 'cedula' => '00123456789', 'producto_id' => 20, 'tipo' => 'Tradicional', 'monto' => 500, 'fecha' => '2026-07-10'],
        ]);
        DB::table('ventas_usuarios_net')->insert([
            ['agencia_id' => '200', 'cedula' => '00123456789', 'producto_id' => 10, 'tipo' => 'No Tradicional', 'monto' => 2000, 'fecha' => '2026-07-20'],
            ['agencia_id' => '300', 'cedula' => null, 'producto_id' => 10, 'tipo' => 'No Tradicional', 'monto' => 400, 'fecha' => '2026-07-21'],
        ]);
        DB::table('empleados')->insert([
            'companyid' => 168,
            'empleadoid' => 3743,
            'idcentrocosto' => 4717,
            'cedula' => '001-2345678-9',
            'nombres' => 'Ana',
            'apellidos' => 'Pérez',
            'estatus' => true,
        ]);
        DB::table('agencias')->insert([
            ['codigo' => '100', 'terminal' => '100', 'nombre' => 'Agencia Uno', 'sistema' => 'lotobet', 'empresa' => 'SH', 'ruta' => 'Ruta Norte'],
            ['codigo' => '200', 'terminal' => '200', 'nombre' => 'Agencia Dos', 'sistema' => 'lotonet', 'empresa' => 'SH', 'ruta' => 'Ruta Sur'],
            ['codigo' => '300', 'terminal' => '300', 'nombre' => 'Agencia Delta', 'sistema' => 'lotonet', 'empresa' => 'SH', 'ruta' => 'Ruta Delta'],
        ]);
        DB::table('centros_de_costo')->insert([
            'id_centro_costo' => 4717,
            'company_id' => '168',
            'descripcion' => 'Centro principal',
            'id_grupo' => 'Grupo A',
            'id_division' => 'SH',
        ]);
        DB::table('faltantes_bet')->insert([
            'observacion' => '001-2345678-9',
            'monto' => 50,
            'fecha' => '2026-07-25',
        ]);

        $response = $this->actingAs(User::factory()->make(['id' => 1]))
            ->withoutMiddleware()
            ->getJson(route('incentivos.reporte-bonos.datos', [
                'fecha_ini' => '2026-07-01',
                'fecha_fin' => '2026-07-31',
                'sistema' => 'Todos',
            ]))
            ->assertOk()
            ->assertJsonPath('meta.total_no_tradicional', 3400)
            ->assertJsonPath('meta.total_venta_externa', 0)
            ->assertJsonPath('meta.total_bono', 17)
            ->assertJsonPath('meta.empleados_pendientes', 1)
            ->assertJsonPath('meta.venta_externa_disponible', false)
            ->assertJsonPath('data.0.empleadoid', 3743)
            ->assertJsonPath('data.0.centro_costo', '4717-Centro principal')
            ->assertJsonPath('data.0.ruta', 'Ruta Sur')
            ->assertJsonPath('data.0.faltante', 50)
            ->assertJsonPath('data.0.estado', 'con_faltante')
            ->assertJsonPath('data.1.cedula', '')
            ->assertJsonPath('data.1.agencia', 'Agencia Delta')
            ->assertJsonPath('data.1.estado', 'pendiente_empleado');

        $this->assertSame(15.0, (float) $response->json('data.0.bono'));
    }

    public function test_bonus_report_validates_the_date_range(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1]))
            ->withoutMiddleware()
            ->getJson(route('incentivos.reporte-bonos.datos', [
                'fecha_ini' => '2026-07-31',
                'fecha_fin' => '2026-07-01',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fecha_fin');
    }
}
