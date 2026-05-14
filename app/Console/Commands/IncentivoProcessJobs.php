<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IncentivoProcessJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incentivo:process-jobs {--once}';

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
        // toma 1 job pendiente
        $job = DB::table('incentivo_jobs')
            ->where('status', 'pending')
            ->orderBy('id')
            ->first();

        if (!$job) {
            $this->info('No pending jobs');
            return 0;
        }

        DB::table('incentivo_jobs')->where('id', $job->id)->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            DB::statement("SET SESSION max_statement_time = 1800");
            DB::statement("SET SESSION wait_timeout = 600");
            DB::statement("SET SESSION net_read_timeout = 300");
            DB::statement("SET SESSION net_write_timeout = 300");

            // Ejecuta tu SP con los datos del job
            DB::statement('CALL CalculoIncentivo(?, ?, ?)', [
                $job->mes,
                $job->anio,
                $job->excluidos ?? ''
            ]);

            DB::table('incentivo_jobs')->where('id', $job->id)->update([
                'status' => 'done',
                'finished_at' => now(),
            ]);

            $this->info("Job {$job->id} done");
            return 0;
        } catch (\Throwable $e) {
            DB::table('incentivo_jobs')->where('id', $job->id)->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            throw $e;
        }
    }
}
