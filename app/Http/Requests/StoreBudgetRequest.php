<?php

namespace App\Http\Requests;

use App\Enums\RoofDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $bill = $this->input('bill_amount');

        if (is_string($bill)) {
            $normalized = str_replace('.', '', $bill);
            $normalized = str_replace(',', '.', $normalized);
            $this->merge(['bill_amount' => $normalized]);
        }

        $roofSize = $this->input('roof_size_m2');

        if (is_string($roofSize) && $roofSize !== '') {
            $normalizedRoof = str_replace('.', '', $roofSize);
            $normalizedRoof = str_replace(',', '.', $normalizedRoof);
            $this->merge(['roof_size_m2' => $normalizedRoof]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bill_amount' => ['required', 'numeric', 'min:1'],
            'knows_roof_size' => ['required', 'boolean'],
            'roof_size_m2' => ['required_if:knows_roof_size,true', 'nullable', 'numeric', 'min:1'],
            'knows_direction' => ['required', 'boolean'],
            'roof_direction' => ['required_if:knows_direction,true', 'nullable', Rule::enum(RoofDirection::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bill_amount.required' => 'Informe o valor da conta de energia.',
            'bill_amount.numeric' => 'Informe um valor numérico para a conta de energia.',
            'bill_amount.min' => 'Informe um valor de conta maior que zero.',
            'roof_size_m2.required_if' => 'Informe o tamanho do telhado em metros quadrados.',
            'roof_direction.required_if' => 'Informe a direção do telhado.',
        ];
    }
}
