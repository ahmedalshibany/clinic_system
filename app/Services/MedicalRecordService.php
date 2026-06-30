<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
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

    public function createRecordWithPrescription(array $data): MedicalRecord
    {
        return DB::transaction(function () use ($data) {
            $this->validatePrescriptionStock($data['prescription_items'] ?? []);

            $record = MedicalRecord::create($data);

            if (!empty($data['prescription_items'])) {
                $prescription = Prescription::create([
                    'medical_record_id' => $record->id,
                ]);

                foreach ($data['prescription_items'] as $item) {
                    $prescription->items()->create($item);
                    $this->decrementMedicineStock($item);
                }
            }

            if (!empty($data['appointment_id'])) {
                $appointment = Appointment::find($data['appointment_id']);
                if ($appointment && $appointment->status === Appointment::STATUS_IN_PROGRESS) {
                    $appointment->update([
                        'status' => Appointment::STATUS_COMPLETED,
                        'completed_at' => now(),
                    ]);
                }
            }

            return $record;
        });
    }

    public function updateRecordWithPrescription(MedicalRecord $medicalRecord, array $data): MedicalRecord
    {
        return DB::transaction(function () use ($medicalRecord, $data) {
            $oldItems = $medicalRecord->prescription?->items()->get() ?? collect();

            foreach ($oldItems as $oldItem) {
                $this->restoreMedicineStock($oldItem);
            }

            $this->validatePrescriptionStock($data['prescription_items'] ?? []);

            $medicalRecord->update($data);

            if (!empty($data['prescription_items'])) {
                $prescription = $medicalRecord->prescription ?? Prescription::create(['medical_record_id' => $medicalRecord->id]);

                $prescription->items()->whereNotIn('id', $oldItems->pluck('id'))->delete();

                foreach ($data['prescription_items'] as $item) {
                    $prescription->items()->create($item);
                    $this->decrementMedicineStock($item);
                }
            } elseif ($medicalRecord->prescription && array_key_exists('prescription_items', $data)) {
                $medicalRecord->prescription->items()->delete();
            }

            return $medicalRecord;
        });
    }

    public function deleteRecord(MedicalRecord $medicalRecord)
    {
        return DB::transaction(function () use ($medicalRecord) {
            if ($medicalRecord->prescription) {
                foreach ($medicalRecord->prescription->items as $item) {
                    $this->restoreMedicineStock($item);
                }
                $medicalRecord->prescription->items()->delete();
                $medicalRecord->prescription->delete();
            }

            return $medicalRecord->delete();
        });
    }

    protected function validatePrescriptionStock(array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['medicine_id']) || empty($item['quantity'])) {
                continue;
            }

            $medicine = Medicine::lockForUpdate()->find($item['medicine_id']);
            if (!$medicine) {
                throw new \InvalidArgumentException(__('messages.medicineNotFound', ['id' => $item['medicine_id']]));
            }

            if ($medicine->stock < (int) $item['quantity']) {
                throw new \RuntimeException(__('messages.medicineInsufficientStock', [
                    'name' => $medicine->name,
                    'stock' => $medicine->stock,
                    'requested' => $item['quantity'],
                ]));
            }
        }
    }

    protected function decrementMedicineStock(array $item): void
    {
        if (empty($item['medicine_id']) || empty($item['quantity'])) {
            return;
        }

        Medicine::where('id', $item['medicine_id'])
            ->lockForUpdate()
            ->decrement('stock', (int) $item['quantity']);
    }

    protected function restoreMedicineStock($oldItem): void
    {
        if (empty($oldItem->medicine_id) || empty($oldItem->quantity)) {
            return;
        }

        Medicine::where('id', $oldItem->medicine_id)
            ->lockForUpdate()
            ->increment('stock', (int) $oldItem->quantity);
    }
}
