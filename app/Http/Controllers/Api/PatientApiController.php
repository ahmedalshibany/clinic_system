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
        $term = $request->get('q');
        
        $query = Patient::query()
            ->select('id', 'name', 'patient_code', 'phone');

        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('patient_code', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $patients = $query->limit(20)->get()->map(function($patient) {
            return [
                'id' => $patient->id,
                'text' => $patient->name . ' (' . $patient->patient_code . ')',
                'phone' => $patient->phone
            ];
        });

        return response()->json([
            'results' => $patients
        ]);
    }
}
