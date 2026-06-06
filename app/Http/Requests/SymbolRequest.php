<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SymbolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $symbol = $this->route('symbol');

        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('symbols', 'name')->whereNull('deleted_at')->ignore($symbol)],
            'exchange' => ['nullable', 'string', 'max:80'],
            'instrument_type' => ['required', 'in:crypto,equity,futures,option,forex'],
            'tick_size' => ['required', 'numeric', 'gt:0'],
            'lot_size' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
