<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            // Allow same email for the currently editing doctor's associated User, if known. 
            // In DoctorController, Doctor's email does not map directly to a unique constraint on Doctors table. It's stored in Users when refactored.
            // Leaving it as nullable|email|max:255 per original controller, will handle unique check via Service layer if needed.
            'email' => 'nullable|email|max:255', 
            'bio' => 'nullable|string',
            'working_days' => 'nullable|array',
            'working_days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'work_start_time' => 'nullable|string',
            'work_end_time' => 'nullable|string',
            'consultation_fee' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }
}
