<?php

namespace App\Http\Requests\MedicalRecord;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'diagnosis' => 'required|string',
            'diagnosis_code' => 'nullable|string',
            'vital_signs' => 'required|array',
            'vital_signs.bp_systolic' => 'nullable|numeric',
            'vital_signs.bp_diastolic' => 'nullable|numeric',
            'vital_signs.temp' => 'nullable|numeric',
            'vital_signs.pulse' => 'nullable|numeric',
            'vital_signs.weight' => 'nullable|numeric',
            'chief_complaint' => 'required|string',
            'history_of_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:visit_date',
            'prescription_items' => 'nullable|array',
            'prescription_items.*.medicine_id' => 'nullable|exists:medicines,id',
            'prescription_items.*.medication_name' => 'required_with:prescription_items|string',
            'prescription_items.*.dosage' => 'required_with:prescription_items|string',
            'prescription_items.*.frequency' => 'required_with:prescription_items|string',
            'prescription_items.*.duration' => 'required_with:prescription_items|string',
        ];
    }
}
