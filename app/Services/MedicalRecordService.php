<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalRecordService
{
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
}
