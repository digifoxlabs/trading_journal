<?php

namespace Database\Seeders;

use App\Models\Setup;
use Illuminate\Database\Seeder;

class SetupSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['EMA Pullback', 'Breakout', 'Mean Reversion', 'Liquidity Sweep', 'Range Reversal'] as $name) {
            Setup::updateOrCreate(['name' => $name], ['description' => null]);
        }
    }
}
