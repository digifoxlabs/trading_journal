<?php

namespace Tests\Feature;

use App\Models\DailyBias;
use App\Models\Symbol;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeDailyBiasTest extends TestCase
{
    use RefreshDatabase;

    public function test_trade_create_form_only_exposes_symbols_with_a_daily_bias(): void
    {
        $biasedSymbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);
        $unbiasedSymbol = Symbol::create([
            'name' => 'BANKNIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        DailyBias::create([
            'date' => '2026-05-11',
            'symbol_id' => $biasedSymbol->id,
            'bias' => 'bullish',
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('trades.create'));

        $response->assertOk();
        $response->assertSee('NIFTY');
        $response->assertDontSee('BANKNIFTY');
    }

    public function test_trade_cannot_be_created_without_a_daily_bias_for_the_trade_date_and_symbol(): void
    {
        $symbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->from(route('trades.create'))
            ->post(route('trades.store'), $this->tradePayload($symbol->id));

        $response->assertRedirect(route('trades.create'));
        $response->assertSessionHasErrors('symbol_id');
        $this->assertDatabaseMissing('trades', ['symbol_id' => $symbol->id]);
    }

    public function test_trade_can_be_created_when_daily_bias_exists_for_the_trade_date_and_symbol(): void
    {
        $symbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        DailyBias::create([
            'date' => '2026-05-11',
            'symbol_id' => $symbol->id,
            'bias' => 'bullish',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->post(route('trades.store'), $this->tradePayload($symbol->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('trades', [
            'trade_date' => '2026-05-11',
            'symbol_id' => $symbol->id,
        ]);
    }

    public function test_trade_edit_form_selects_the_existing_symbol(): void
    {
        $symbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        DailyBias::create([
            'date' => '2026-05-11',
            'symbol_id' => $symbol->id,
            'bias' => 'bullish',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('trades.store'), $this->tradePayload($symbol->id));

        $trade = Trade::firstOrFail();
        $response = $this->get(route('trades.edit', $trade));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<option[^>]*value="' . $symbol->id . '"[^>]*selected[^>]*>\s*NIFTY\s*<\/option>/',
            $response->getContent()
        );
    }

    public function test_trade_edit_form_exposes_symbols_without_a_daily_bias(): void
    {
        $biasedSymbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);
        $unbiasedSymbol = Symbol::create([
            'name' => 'BANKNIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        DailyBias::create([
            'date' => '2026-05-11',
            'symbol_id' => $biasedSymbol->id,
            'bias' => 'bullish',
        ]);

        $trade = Trade::create([
            ...$this->tradePayload($biasedSymbol->id),
            'trade_number' => 'TRD-20260511-0001',
            'direction' => 'long',
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('trades.edit', $trade));

        $response->assertOk();
        $response->assertSee('NIFTY');
        $response->assertSee('BANKNIFTY');
    }

    public function test_trade_can_be_updated_without_a_daily_bias_for_the_trade_date_and_symbol(): void
    {
        $symbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        $trade = Trade::create([
            ...$this->tradePayload($symbol->id),
            'trade_number' => 'TRD-20260511-0001',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->put(route('trades.update', $trade), [
                ...$this->tradePayload($symbol->id),
                'exit_price' => 108,
            ]);

        $response->assertRedirect(route('trades.show', $trade));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('trades', [
            'id' => $trade->id,
            'symbol_id' => $symbol->id,
            'exit_price' => 108,
        ]);
    }

    public function test_dashboard_open_swing_trade_uses_exit_price_modal(): void
    {
        $symbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        $trade = Trade::create([
            ...$this->tradePayload($symbol->id),
            'trade_number' => 'TRD-20260511-0001',
            'trade_date' => today()->subDay()->toDateString(),
            'exit_price' => null,
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('dashboardExitModal()');
        $response->assertSee('Add exit');
        $response->assertSee('trades\\/' . $trade->id . '\\/prices', false);
    }

    public function test_dashboard_today_open_trade_exit_text_uses_exit_price_modal(): void
    {
        $symbol = Symbol::create([
            'name' => 'NIFTY',
            'instrument_type' => 'equity',
            'tick_size' => 0.0001,
            'lot_size' => 1,
        ]);

        $trade = Trade::create([
            ...$this->tradePayload($symbol->id),
            'trade_number' => 'TRD-20260518-0001',
            'trade_date' => today()->toDateString(),
            'exit_price' => null,
        ]);

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('>Open</button>', false);
        $response->assertSee('trades\\/' . $trade->id . '\\/prices', false);
    }

    private function tradePayload(int $symbolId): array
    {
        return [
            'trade_date' => '2026-05-11',
            'symbol_id' => $symbolId,
            'direction' => 'long',
            'entry_price' => 100,
            'stop_loss' => 95,
            'target_price' => 110,
            'position_size' => 1,
            'entry_fees' => 0,
            'exit_fees' => 0,
        ];
    }
}
