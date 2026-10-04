<?php

namespace Tests\Feature;

use App\Mail\AutoProcesoResumenMail;
use App\Models\AutoProcesoConfig;
use App\Services\AutoProcesoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AutoProcesoProgramadoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            '2026_03_26_120000_create_auto_proceso_configs_table.php',
            '2026_03_26_130000_add_process_day_offset_to_auto_proceso_configs_table.php',
            '2026_03_26_194500_add_process_date_to_auto_proceso_configs_table.php',
            '2026_05_14_131000_add_max_seconds_to_auto_proceso_configs.php',
        ] as $migrationFile) {
            (require database_path('migrations/'.$migrationFile))->up();
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Schema::dropIfExists('auto_proceso_configs');

        parent::tearDown();
    }

    public function test_delayed_schedule_processes_each_configuration_once_and_sends_mail_after_completion(): void
    {
        Carbon::setTestNow('2026-10-04 10:05:00');
        Mail::fake();

        foreach (['lotobet', 'lotedom', 'delta', 'ds_virtual'] as $sistema) {
            AutoProcesoConfig::query()->create([
                'sistema' => $sistema,
                'enabled' => true,
                'hora' => '10:00',
                'correo' => $sistema.'@example.com',
                'process_day_offset' => -1,
            ]);
        }

        $service = $this->mock(AutoProcesoService::class);
        $service->shouldReceive('execute')->times(4)->withArgs(function (string $sistema, string $fecha, int $maxSeconds): bool {
            return in_array($sistema, ['lotobet', 'lotedom', 'delta', 'ds_virtual'], true)
                && $fecha === '2026-10-03'
                && $maxSeconds === 1800;
        })->andReturn([
            'ok' => true,
            'ok_count' => 2,
            'error_count' => 0,
            'no_data_count' => 0,
            'timed_out' => false,
            'elapsed_seconds' => 1,
            'details' => [],
        ]);

        $this->artisan('auto-proceso:run-due')->assertSuccessful();
        $this->artisan('auto-proceso:run-due')->assertSuccessful();

        Mail::assertSent(AutoProcesoResumenMail::class, 4);
        $this->assertSame(4, AutoProcesoConfig::query()->where('last_status', 'ok')->count());
    }
}
