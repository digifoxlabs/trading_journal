<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $setup = $this->route('setup');

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('setups', 'name')->whereNull('deleted_at')->ignore($setup)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
