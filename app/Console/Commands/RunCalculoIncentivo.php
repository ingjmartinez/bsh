<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunCalculoIncentivo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incentivo:run {mes} {year} {excluidos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mes = $this->argument('mes');
        $year = $this->argument('year');
        $excluidos = $this->argument('excluidos');

        DB::statement("SET SESSION max_statement_time = 1800");
        DB::statement("SET SESSION wait_timeout = 600");
        DB::statement("SET SESSION net_read_timeout = 300");
        DB::statement("SET SESSION net_write_timeout = 300");

        DB::statement('CALL CalculoIncentivo(?, ?, ?)', [$mes, $year, $excluidos]);

        $this->info("OK");
    }
}
