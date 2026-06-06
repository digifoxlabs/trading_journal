<?php

namespace App\Http\Requests;

use App\Models\DailyBias;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trade_date' => ['required', 'date'],
            'symbol_id' => [
                'required',
                Rule::exists('symbols', 'id')->whereNull('deleted_at'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->routeIs('trades.store')) {
                        return;
                    }

                    if (! $this->filled('trade_date') || ! $value) {
                        return;
                    }

                    $hasDailyBias = DailyBias::whereDate('date', $this->input('trade_date'))
                        ->where('symbol_id', $value)
                        ->exists();

                    if (! $hasDailyBias) {
                        $fail('Create a daily bias for this symbol and trade date before punching the trade.');
                    }
                },
            ],
            'direction' => ['required', 'in:long,short'],
            'setup_type' => ['nullable', 'string', 'max:120'],
            'entry_price' => ['required', 'numeric', 'gt:0'],
            'stop_loss' => ['required', 'numeric', 'gt:0'],
            'target_price' => ['nullable', 'numeric', 'gt:0'],
            'position_size' => ['required', 'numeric', 'gt:0'],
            'entry_fees' => ['nullable', 'numeric', 'min:0'],
            'exit_price' => ['nullable', 'numeric', 'gt:0'],
            'exit_fees' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'mistake_flag' => ['nullable', 'boolean'],
            'emotion_flag' => ['nullable', 'boolean'],
        ];
    }
}
