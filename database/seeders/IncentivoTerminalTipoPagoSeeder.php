<?php

namespace Database\Seeders;

use App\Models\IncentivoTerminalTipoPago;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncentivoTerminalTipoPagoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IncentivoTerminalTipoPago::factory()->count(10)->create();
    }
}
