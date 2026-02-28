<?php

namespace App\Http\Requests\Vitals;

use Illuminate\Foundation\Http\FormRequest;

class StoreVitalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'temperature' => 'required|numeric|min:30|max:45',
            'bp_systolic' => 'required|integer|min:50|max:250',
            'bp_diastolic' => 'required|integer|min:30|max:150',
            'pulse' => 'required|integer|min:30|max:250',
            'respiratory_rate' => 'nullable|integer|min:10|max:60',
            'weight' => 'required|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:10|max:300',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'notes' => 'nullable|string',
        ];
    }
}
