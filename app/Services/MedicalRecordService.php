<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    /**
     * Get paginated, filtered list of medical records.
     */
    public function getAllMedicalRecords(array $filters): array
    {
        $query = MedicalRecord::with(['patient', 'doctor']);

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('visit_date', $filters['date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('diagnosis_code', 'like', "%{$search}%");
            });
        }

        $records = $query->latest('visit_date')->paginate(10)->withQueryString();
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();

        return compact('records', 'patients', 'doctors');
    }

    /**
     * Create a medical record along with its prescription if provided.
     *
     * @param array $data
     * @return MedicalRecord
     * @throws \Exception
     */
    public function createRecordWithPrescription(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create Medical Record
            $record = MedicalRecord::create($data);

            // Handle Prescription
            if (!empty($data['prescription_items'])) {
                $prescription = Prescription::create([
                    'medical_record_id' => $record->id,
                ]);

                foreach ($data['prescription_items'] as $item) {
                    $prescription->items()->create($item);
                }
            }

            // If linked to appointment, update status to completed if not already
            if (!empty($data['appointment_id'])) {
                $appointment = Appointment::find($data['appointment_id']);
                if ($appointment && $appointment->status !== 'completed') {
                    $appointment->update([
                        'status' => 'completed', 
                        'completed_at' => now()
                    ]);
                }
            }

            return $record;
        });
    }

    /**
     * Update a medical record and its associated prescription items.
     *
     * @param MedicalRecord $medicalRecord
     * @param array $data
     * @return MedicalRecord
     * @throws \Exception
     */
    public function updateRecordWithPrescription(MedicalRecord $medicalRecord, array $data)
    {
        return DB::transaction(function () use ($medicalRecord, $data) {
            $medicalRecord->update($data);

            // Handle Prescription Updates (Full Re-sync for simplicity)
            if (!empty($data['prescription_items'])) {
                // Determine if prescription exists, if not create one
                $prescription = $medicalRecord->prescription ?? Prescription::create(['medical_record_id' => $medicalRecord->id]);
                
                // Remove old items
                $prescription->items()->delete();

                // Add new items
                foreach ($data['prescription_items'] as $item) {
                    $prescription->items()->create($item);
                }
            } elseif ($medicalRecord->prescription) {
                // If prescription_items is empty array, clear it
                 if (isset($data['prescription_items'])) {
                    $medicalRecord->prescription->items()->delete();
                 }
            }

            return $medicalRecord;
        });
    }

    /**
     * Delete a medical record and its associated prescription data.
     *
     * @param MedicalRecord $medicalRecord
     * @return bool|null
     * @throws \Exception
     */
    public function deleteRecord(MedicalRecord $medicalRecord)
    {
        return DB::transaction(function () use ($medicalRecord) {
            // Delete prescription items and prescription if they exist
            if ($medicalRecord->prescription) {
                $medicalRecord->prescription->items()->delete();
                $medicalRecord->prescription->delete();
            }

            return $medicalRecord->delete();
        });
    }
}
