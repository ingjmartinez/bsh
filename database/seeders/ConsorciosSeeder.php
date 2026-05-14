<?php

namespace Database\Seeders;

use App\Models\Consorcio;
use Illuminate\Database\Seeder;

class ConsorciosSeeder extends Seeder
{
    public function run(): void
    {
        $consorcios = [
            ['id' => 1, 'consorcios' => 'LOTEKA CENTRAL'],
            ['id' => 2, 'consorcios' => 'ORTIZ'],
            ['id' => 3, 'consorcios' => 'ZERTIDAL'],
            ['id' => 4, 'consorcios' => 'MELINA'],
            ['id' => 6, 'consorcios' => 'SOÑADORA'],
            ['id' => 8, 'consorcios' => 'COLOMBO'],
            ['id' => 9, 'consorcios' => 'LA SOLUCION'],
            ['id' => 10, 'consorcios' => 'EDWARD'],
            ['id' => 11, 'consorcios' => 'LOTZASEDI'],
            ['id' => 12, 'consorcios' => 'LA DINAMICA'],
        ];

        foreach ($consorcios as $consorcio) {
            Consorcio::updateOrCreate(
                ['id' => $consorcio['id']],
                ['consorcios' => $consorcio['consorcios']]
            );
        }
    }
}
