<?php

namespace Tests\Feature;

use App\Models\AsistenciaNet;
use App\Support\LotedomRowMapper;
use Tests\TestCase;

class AsistenciasLotedomConfigurationTest extends TestCase
{
    public function test_query_and_save_use_the_confirmed_lotedom_curl(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AsistenciaController.php'));

        $this->assertIsString($controller);
        $this->assertSame(2, substr_count($controller, 'https://lotedom-api.orkapi.net/api/finan/asistencia_usuarios/{$fecha}'));
        $this->assertStringNotContainsString('http://contable.apploteka.com//api/finan/asistencia_usuarios/', $controller);

        $lotedomMethods = substr($controller, strpos($controller, 'public function getAsistenciasLotedom'));
        $this->assertStringNotContainsString('CURLOPT_POSTFIELDS', $lotedomMethods);

        preg_match_all("~'token: ([^']+)'~", $lotedomMethods, $tokens);
        preg_match_all("~'Cookie: (_orkapi_session=[^']+)'~", $lotedomMethods, $cookies);

        $this->assertCount(2, $tokens[1]);
        $this->assertCount(2, $cookies[1]);
        $this->assertSame($tokens[1][0], $tokens[1][1]);
        $this->assertSame($cookies[1][0], $cookies[1][1]);
        $this->assertSame('ZFozLWdBYyqERusVdTsW', $tokens[1][0]);
        $this->assertStringStartsWith('_orkapi_session=pOlvwaeBapzBuOeprHO383uE04gMCWtY', $cookies[1][0]);
        $this->assertStringEndsWith('--i8FViCpBoxoJKnwl3D%2Bv7g%3D%3D', $cookies[1][0]);
    }

    public function test_endpoint_row_maps_to_the_existing_database_columns(): void
    {
        $mapped = LotedomRowMapper::asistencia([
            'entrada' => '2026-09-24T16:02:12.000-04:00',
            'salida' => '2026-09-24T21:00:33.000-04:00',
            'identificacion' => '002-0877166-3',
            'username' => '2040208771663',
            'usuario' => 'ROBERT LUIS LIRIANO CONTRERAS',
            'consorcio' => 'AGENTE GRUPO BSH',
            'consorcio_codigo' => '20',
            'banca' => 'BANCA PRUEBA',
            'agencia' => '20430005',
            'terminal' => '(20430005) TERMINAL',
            'salida_inactividad' => 0,
        ], '2026-09-24');

        $this->assertSame('2026-09-24', $mapped['fecha']);
        $this->assertSame('AGENTE GRUPO BSH', $mapped['consorcio']);
        $this->assertSame('20430005', $mapped['agencia']);
        $this->assertSame('2026-09-24 16:02:12', $mapped['entrada']);
        $this->assertSame('2026-09-24 21:00:33', $mapped['salida']);
        $this->assertSame('00208771663', $mapped['identificacion']);
        $this->assertSame('0', $mapped['salida_inactividad']);
        $this->assertArrayNotHasKey('consorcio_codigo', $mapped);

        $model = new AsistenciaNet;

        $this->assertSame('id', $model->getKeyName());
        $this->assertSame(
            ['consorcio', 'agencia', 'fecha', 'usuario', 'entrada', 'salida', 'identificacion', 'username', 'banca', 'terminal', 'salida_inactividad', 'turno'],
            $model->getFillable()
        );
    }

    public function test_identification_is_normalized_for_the_data_table_without_losing_leading_zeroes(): void
    {
        $row = LotedomRowMapper::asistenciaParaConsulta([
            'identificacion' => '002-0877166-3',
            'usuario' => 'USUARIO PRUEBA',
            'entrada' => '2026-09-24T08:02:12.000-04:00',
            'salida' => '2026-09-24T17:30:45.000-04:00',
        ], '2026-09-24');

        $this->assertSame('00208771663', $row['identificacion']);
        $this->assertSame('USUARIO PRUEBA', $row['usuario']);
        $this->assertSame('2026-09-24', $row['fecha']);
        $this->assertSame('08:02:12', $row['entrada']);
        $this->assertSame('17:30:45', $row['salida']);
    }

    public function test_data_table_uses_valid_api_fields_and_reports_errors(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AsistenciaController.php'));
        $view = file_get_contents(resource_path('views/lotedom/asistencias.blade.php'));

        $this->assertIsString($controller);
        $this->assertIsString($view);
        foreach (['consorcio', 'agencia', 'usuario', 'identificacion', 'entrada', 'salida'] as $field) {
            $this->assertStringContainsString("item.{$field}", $view);
        }
        $this->assertStringContainsString('{ targets: [4, 5, 6], visible:', $view);
        $this->assertSame(2, substr_count($controller, "if (\$code !== '00' && \$code !== '0')"));
        $this->assertStringContainsString('if (!result.ok)', $view);
    }
}
