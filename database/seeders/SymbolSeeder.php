<?php

namespace Database\Seeders;

use App\Models\Symbol;
use Illuminate\Database\Seeder;

class SymbolSeeder extends Seeder
{
    public function run(): void
    {
        $symbols = [
            ['name' => 'BTCUSDT', 'exchange' => 'BINANCE', 'instrument_type' => 'crypto', 'tick_size' => 0.01, 'lot_size' => 0.0001],
            ['name' => 'NIFTY', 'exchange' => 'NSE', 'instrument_type' => 'futures', 'tick_size' => 0.05, 'lot_size' => 50],
            ['name' => 'BANKNIFTY', 'exchange' => 'NSE', 'instrument_type' => 'futures', 'tick_size' => 0.05, 'lot_size' => 15],
        ];

        foreach ($symbols as $symbol) {
            Symbol::updateOrCreate(['name' => $symbol['name']], $symbol);
        }
    }
}
