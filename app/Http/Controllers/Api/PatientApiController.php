<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientApiController extends Controller
{
    /**
     * Search patients by name or code.
     * Returns JSON compatible with Select2.
     */
    public function search(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'doctor', 'receptionist', 'nurse'])) {
            abort(403, __('messages.unauthorized'));
        }

        $term = $request->get('q');

        $patients = Patient::where(function ($q) use ($term) {
                if ($term) {
                    $q->where('name', 'like', "%{$term}%")
                      ->orWhere('patient_code', 'like', "%{$term}%");
                }
            })
            ->select(['id', 'name', 'patient_code'])
            ->limit(10)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'text' => "[{$patient->patient_code}] {$patient->name}",
                ];
            });

        return response()->json(['results' => $patients]);
    }
}
