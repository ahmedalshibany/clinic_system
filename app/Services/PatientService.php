<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Vital;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class PatientService
{
    public function getAllPatients(array $filters): LengthAwarePaginator
    {
        $query = Patient::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10)->withQueryString();
    }

    public function createPatient(array $data): Patient
    {
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        return Patient::create($data);
    }

    public function updatePatient(Patient $patient, array $data): bool
    {
        return $patient->update($data);
    }

    /**
     * Soft-delete a patient and all related clinical records.
     * No data is permanently destroyed — all related models use SoftDeletes.
     */
    public function deletePatient($id): void
    {
        $patient = $id instanceof Patient ? $id : Patient::findOrFail($id);

        DB::transaction(function () use ($patient) {
            $patient->medicalRecords()->delete();
            $patient->appointments()->delete();
            $patient->invoices()->delete();
            $patient->files()->delete();
            $patient->delete();
        });
    }
}
