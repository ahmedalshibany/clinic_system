<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientFileSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $users = User::pluck('id')->toArray();
        if ($patients->isEmpty() || empty($users)) return;

        $categories = ['lab_result', 'xray', 'mri', 'prescription', 'report', 'other'];
        $extensions = ['pdf', 'jpg', 'png', 'docx'];
        $fileNames = [
            'lab_result' => ['blood_test_report', 'cbc_results', 'lipid_panel', 'thyroid_function', 'liver_function'],
            'xray' => ['chest_xray', 'arm_xray', 'leg_xray', 'spine_xray', 'shoulder_xray'],
            'mri' => ['brain_mri', 'knee_mri', 'spine_mri', 'abdominal_mri'],
            'prescription' => ['medication_list', 'prescription_refill', 'current_medications'],
            'report' => ['medical_report', 'discharge_summary', 'consultation_note', 'follow_up_note'],
            'other' => ['consent_form', 'referral_letter', 'insurance_document', 'id_scan'],
        ];

        foreach ($patients as $patient) {
            $numFiles = rand(2, 3);

            for ($i = 0; $i < $numFiles; $i++) {
                $category = $categories[array_rand($categories)];
                $ext = $extensions[array_rand($extensions)];
                $baseName = $fileNames[$category][array_rand($fileNames[$category])];
                $filename = $baseName . '_' . $patient->id . '_' . uniqid() . '.' . $ext;
                $originalName = $baseName . '_' . now()->subDays(rand(1, 180))->format('Y-m-d') . '.' . $ext;

                PatientFile::create([
                    'patient_id' => $patient->id,
                    'uploaded_by' => $users[array_rand($users)],
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'file_path' => 'uploads/patient_files/' . $patient->id . '/' . $filename,
                    'file_type' => $ext,
                    'file_size' => rand(51200, 5242880),
                    'category' => $category,
                    'description' => ucwords(str_replace('_', ' ', $baseName)) . ' for ' . $patient->name,
                ]);
            }
        }
    }
}
