<?php

namespace Tests\Feature;

use App\Services\Lotobet\LotobetIngestionService;
use App\Services\Lotobet\LotobetSessionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class LotobetPaymentProductIngestionTest extends TestCase
{
    public function test_import_keeps_product_ids_for_both_payment_sources(): void
    {
        foreach (['pagos_misma_empresa_bet', 'pagos_aotra_empresa_bet'] as $table) {
            Schema::create($table, function (Blueprint $blueprint): void {
                $blueprint->id();
                $blueprint->string('agencia_id');
                $blueprint->integer('producto_id')->nullable();
                $blueprint->decimal('monto', 14, 2);
                $blueprint->date('fecha');
                $blueprint->string('cedula')->nullable();
                $blueprint->string('tipo_pago')->nullable();
                $blueprint->timestamps();
            });
        }

        $session = Mockery::mock(LotobetSessionService::class);
        $session->shouldReceive('getReport')->twice()->andReturn(
            ['Content' => [['agencia_id' => '101', 'producto_id' => 7, 'monto' => 100, 'fecha' => '2026-09-01']]],
            ['Content' => [['agencia_id' => '101', 'ProductoId' => 400, 'monto' => 50, 'fecha' => '2026-09-01']]],
        );

        $service = new LotobetIngestionService($session);
        $service->save('pagos_misma_empresa', '2026-09-01');
        $service->save('pagos_aotra_empresa', '2026-09-01');

        $this->assertSame(7, DB::table('pagos_misma_empresa_bet')->value('producto_id'));
        $this->assertSame(400, DB::table('pagos_aotra_empresa_bet')->value('producto_id'));
    }

    public function test_migration_adds_product_ids_to_existing_payment_tables(): void
    {
        foreach (['pagos_misma_empresa_bet', 'pagos_aotra_empresa_bet'] as $table) {
            Schema::create($table, function (Blueprint $blueprint): void {
                $blueprint->id();
            });
        }

        $migration = require database_path('migrations/2026_10_02_183200_add_producto_id_to_lotobet_payment_tables.php');
        $migration->up();

        $this->assertTrue(Schema::hasColumn('pagos_misma_empresa_bet', 'producto_id'));
        $this->assertTrue(Schema::hasColumn('pagos_aotra_empresa_bet', 'producto_id'));

        $migration->down();

        $this->assertFalse(Schema::hasColumn('pagos_misma_empresa_bet', 'producto_id'));
        $this->assertFalse(Schema::hasColumn('pagos_aotra_empresa_bet', 'producto_id'));
    }

    public function test_backfill_matches_every_saved_payment_before_updating_products(): void
    {
        Schema::create('pagos_misma_empresa_bet', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('agencia_id');
            $blueprint->integer('producto_id')->nullable();
            $blueprint->decimal('monto', 14, 2);
            $blueprint->date('fecha');
        });
        DB::table('pagos_misma_empresa_bet')->insert([
            ['agencia_id' => '101', 'producto_id' => null, 'monto' => 100, 'fecha' => '2026-04-01'],
            ['agencia_id' => '102', 'producto_id' => null, 'monto' => 50, 'fecha' => '2026-04-01'],
        ]);

        $session = Mockery::mock(LotobetSessionService::class);
        $session->shouldReceive('getReport')->twice()->andReturn(
            ['Content' => [
                ['agencia_id' => '101', 'producto_id' => 7, 'monto' => 100, 'fecha' => '2026-04-01'],
                ['agencia_id' => '999', 'producto_id' => 400, 'monto' => 50, 'fecha' => '2026-04-01'],
            ]],
            ['Content' => [
                ['agencia_id' => '101', 'producto_id' => 7, 'monto' => 100, 'fecha' => '2026-04-01'],
                ['agencia_id' => '102', 'producto_id' => 400, 'monto' => 50, 'fecha' => '2026-04-01'],
            ]],
        );
        $service = new LotobetIngestionService($session);

        try {
            $service->backfillPaymentProducts('pagos_misma_empresa', '2026-04-01');
            $this->fail('Los registros distintos deben impedir la actualización.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('no coinciden', $exception->getMessage());
        }
        $this->assertSame(0, DB::table('pagos_misma_empresa_bet')->whereNotNull('producto_id')->count());

        $this->assertSame(['matched' => 2, 'updated' => 2], $service->backfillPaymentProducts('pagos_misma_empresa', '2026-04-01'));
        $this->assertSame([7, 400], DB::table('pagos_misma_empresa_bet')->orderBy('id')->pluck('producto_id')->all());
    }
}
