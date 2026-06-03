<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UploadPatientFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'category' => 'required|in:lab_result,xray,mri,prescription,report,other',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
