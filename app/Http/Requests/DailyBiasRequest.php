<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyBiasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'symbol_id' => ['required', Rule::exists('symbols', 'id')->whereNull('deleted_at')],
            'bias' => ['required', 'in:bullish,bearish,neutral'],
            'htf_trend' => ['nullable', 'string', 'max:120'],
            'key_levels' => ['nullable', 'string', 'max:5000'],
            'expected_move' => ['nullable', 'string', 'max:255'],
            'invalidation_level' => ['nullable', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
