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
        Schema::table('agencias', function (Blueprint $table) {
            $table->string('horario_am', 35)->nullable()->after('terminal');
            $table->string('horario_pm', 35)->nullable()->after('horario_am');
        });

        $horarios = [
            ['terminal' => '5005', 'am' => '6:00 AM / 3:00 PM', 'pm' => '3:00 PM / 10:00 PM'],
            ['terminal' => '50001', 'am' => '7:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '50060', 'am' => '7:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '50091', 'am' => '7:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '50210', 'am' => '7:30 AM / 3:00 PM', 'pm' => '3:00 PM / 10:00 PM'],
            ['terminal' => '50061', 'am' => '7:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502719', 'am' => '7:00 AM / 3:00 PM', 'pm' => '3:00 PM / 10:00 PM'],
            ['terminal' => '5502957', 'am' => '6:00 AM / 2:00 PM', 'pm' => '2:00 PM / 10:00 PM'],
            ['terminal' => '51813', 'am' => '8:00 AM / 2:30 PM', 'pm' => '2:30 PM / 9:30 PM'],
            ['terminal' => '50098', 'am' => '6:00 AM / 3:00 PM', 'pm' => '3:00 PM / 10:00 PM'],
            ['terminal' => '50195', 'am' => '8:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '50197', 'am' => '8:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '50230', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5505052', 'am' => '8:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502537', 'am' => '8:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502008', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502260', 'am' => '8:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502339', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502052', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '51795', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '51802', 'am' => '8:00 AM / 2:30 PM', 'pm' => '2:30 PM / 9:00 PM'],
            ['terminal' => '5365', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5368', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5427', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '51552', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5502968', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '5550468', 'am' => '7:30 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '51796', 'am' => '8:00 AM / 2:30 PM', 'pm' => '2:30 PM / 9:00 PM'],
            ['terminal' => '51022', 'am' => '8:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '50216', 'am' => '7:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
            ['terminal' => '51231', 'am' => '6:00 AM / 2:00 PM', 'pm' => '2:30 PM / 10:00 PM'],
            ['terminal' => '55615', 'am' => '8:00 AM / 3:00 PM', 'pm' => '3:00 PM / 9:00 PM'],
            ['terminal' => '50124', 'am' => '7:00 AM / 2:00 PM', 'pm' => '2:00 PM / 9:00 PM'],
        ];

        foreach ($horarios as $item) {
            DB::table('agencias')
                ->whereRaw("COALESCE(NULLIF(TRIM(LEADING '0' FROM terminal), ''), '0') = ?", [$this->normalizarTerminal($item['terminal'])])
                ->update([
                    'horario_am' => $item['am'],
                    'horario_pm' => $item['pm'],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencias', function (Blueprint $table) {
            $table->dropColumn(['horario_am', 'horario_pm']);
        });
    }

    private function normalizarTerminal(string $terminal): string
    {
        $valor = ltrim(trim($terminal), '0');
        return $valor === '' ? '0' : $valor;
    }
};
