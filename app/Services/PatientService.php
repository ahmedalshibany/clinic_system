<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Vital;
use Illuminate\Support\Facades\DB;
use Exception;

class PatientService
{
    /**
     * Create a new patient.
     *
     * @param array $data
     * @return Patient
     */
    public function createPatient(array $data): Patient
    {
        // Ensure status defaulted to active
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        return Patient::create($data);
    }

    /**
     * Update an existing patient.
     *
     * @param Patient $patient
     * @param array $data
     * @return bool
     */
    public function updatePatient(Patient $patient, array $data): bool
    {
        return $patient->update($data);
    }

    /**
     * Delete a patient and cascade delete related records.
     *
     * @param mixed $id Patient ID or Patient instance
     * @return void
     * @throws Exception
     */
    public function deletePatient($id): void
    {
        $patient = $id instanceof Patient ? $id : Patient::findOrFail($id);

        try {
            DB::beginTransaction();

            // 1. Delete Invoices (Direct & Cascading)
            $patient->invoices()->delete();

            // 2. Delete Patient Files
            $patient->files()->delete();

            // 3. Delete Medical Records & Prescriptions
            $medicalRecordIds = $patient->medicalRecords()->pluck('id');
            if ($medicalRecordIds->isNotEmpty()) {
                // Find Prescriptions linked to these records
                $prescriptionIds = Prescription::whereIn('medical_record_id', $medicalRecordIds)->pluck('id');
                
                if ($prescriptionIds->isNotEmpty()) {
                    // Delete Prescription Items
                    PrescriptionItem::whereIn('prescription_id', $prescriptionIds)->delete();
                    // Delete Prescriptions
                    Prescription::whereIn('id', $prescriptionIds)->delete();
                }
                
                // Delete Medical Records
                $patient->medicalRecords()->delete();
            }

            // 4. Delete Appointments & Vitals
            $appointmentIds = $patient->appointments()->pluck('id');
            if ($appointmentIds->isNotEmpty()) {
                // Delete Vitals linked to appointments
                Vital::whereIn('appointment_id', $appointmentIds)->delete();
                
                // Delete Appointments
                $patient->appointments()->delete();
            }

            // 5. Finally delete the patient
            $patient->delete();

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
