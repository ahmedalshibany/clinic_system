<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|string',
            'type' => 'required|in:Consultation,Checkup,Follow-up,Emergency',
            'status' => 'required|in:pending,scheduled,confirmed,waiting,in_progress,completed,cancelled,no_show,checked_in',
            'notes' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'prescription' => 'nullable|string',
            'reason' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
        ];
    }
}
