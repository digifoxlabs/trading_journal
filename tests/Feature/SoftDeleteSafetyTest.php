<?php

namespace Tests\Feature;

use App\Models\DailyBias;
use App\Models\Setup;
use App\Models\Symbol;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_symbol_setup_trade_and_daily_bias_deletes_are_soft_deletes(): void
    {
        $user = User::factory()->create();
        $symbol = Symbol::create($this->symbolPayload());
        $setup = Setup::create(['name' => 'Breakout', 'description' => null]);
        $dailyBias = DailyBias::create([
            'date' => '2026-05-18',
            'symbol_id' => $symbol->id,
            'bias' => 'bullish',
        ]);
        $trade = Trade::create([
            'trade_number' => 'TRD-20260518-0001',
            'trade_date' => '2026-05-18',
            'symbol_id' => $symbol->id,
            'direction' => 'long',
            'entry_price' => 100,
            'stop_loss' => 95,
            'position_size' => 1,
        ]);

        $this->actingAs($user)->delete(route('trades.destroy', $trade))->assertRedirect(route('trades.index'));
        $this->actingAs($user)->delete(route('daily-biases.destroy', $dailyBias))->assertRedirect();
        $this->actingAs($user)->delete(route('setups.destroy', $setup))->assertRedirect();
        $this->actingAs($user)->delete(route('symbols.destroy', $symbol))->assertRedirect();

        $this->assertSoftDeleted($trade);
        $this->assertSoftDeleted($dailyBias);
        $this->assertSoftDeleted($setup);
        $this->assertSoftDeleted($symbol);
    }

    public function test_historical_trades_and_biases_can_display_a_soft_deleted_symbol(): void
    {
        $symbol = Symbol::create($this->symbolPayload());
        $trade = Trade::create([
            'trade_number' => 'TRD-20260518-0001',
            'trade_date' => '2026-05-18',
            'symbol_id' => $symbol->id,
            'direction' => 'long',
            'entry_price' => 100,
            'stop_loss' => 95,
            'position_size' => 1,
        ]);
        $dailyBias = DailyBias::create([
            'date' => '2026-05-18',
            'symbol_id' => $symbol->id,
            'bias' => 'bullish',
        ]);

        $symbol->delete();

        $this->assertTrue($trade->fresh()->symbol->is($symbol));
        $this->assertTrue($dailyBias->fresh()->symbol->is($symbol));
    }

    private function symbolPayload(): array
    {
        return [
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ];
    }
}
