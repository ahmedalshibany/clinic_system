<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $completedAppts = Appointment::where('status', 'completed')->get();
        if ($completedAppts->isEmpty()) return;

        $clinicalMatrix = [
            'Cardiology' => [
                ['diagnosis' => 'Hypertension', 'icd10' => 'I10', 'chief' => 'Patient presents with elevated blood pressure readings and occasional headaches', 'treatment' => 'Prescribed antihypertensive therapy. Low-sodium diet and regular exercise recommended. Monthly BP monitoring follow-up.'],
                ['diagnosis' => 'Coronary Artery Disease', 'icd10' => 'I25.1', 'chief' => 'Patient reports chest pain on exertion and shortness of breath', 'treatment' => 'Prescribed anti-anginal therapy. Cardiac rehabilitation program recommended. Lipid profile monitoring.'],
                ['diagnosis' => 'Arrhythmia', 'icd10' => 'I49.9', 'chief' => 'Patient complains of heart palpitations and occasional dizziness', 'treatment' => 'Prescribed antiarrhythmic medication. ECG monitoring scheduled. Avoid stimulants.'],
                ['diagnosis' => 'Heart Failure', 'icd10' => 'I50.9', 'chief' => 'Patient presents with bilateral lower extremity edema and dyspnea', 'treatment' => 'Prescribed diuretics and ACE inhibitors. Fluid restriction advised. Daily weight monitoring.'],
            ],
            'Dermatology' => [
                ['diagnosis' => 'Eczema', 'icd10' => 'L30.9', 'chief' => 'Patient presents with dry, itchy, inflamed skin patches on arms and legs', 'treatment' => 'Prescribed topical corticosteroids. Moisturizing regimen advised. Avoid known triggers.'],
                ['diagnosis' => 'Acne Vulgaris', 'icd10' => 'L70.0', 'chief' => 'Patient complains of persistent facial breakouts with inflamed lesions', 'treatment' => 'Prescribed topical treatment. Skincare routine recommended. Avoid oily products.'],
                ['diagnosis' => 'Fungal Infection', 'icd10' => 'B35.9', 'chief' => 'Patient presents with itchy, ring-shaped rash on torso', 'treatment' => 'Prescribed antifungal therapy. Keep area dry and clean. Avoid sharing towels.'],
                ['diagnosis' => 'Psoriasis', 'icd10' => 'L40.0', 'chief' => 'Patient presents with red, scaly plaques on elbows and scalp', 'treatment' => 'Prescribed topical treatment. Phototherapy considered for refractory cases.'],
            ],
            'Pediatrics' => [
                ['diagnosis' => 'Upper Respiratory Tract Infection', 'icd10' => 'J06.9', 'chief' => 'Child presents with fever, cough, and runny nose for 3 days', 'treatment' => 'Prescribed supportive care. Antipyretics as needed. Encourage fluids and rest.'],
                ['diagnosis' => 'Acute Gastroenteritis', 'icd10' => 'A09', 'chief' => 'Child presents with vomiting and diarrhea for 2 days, mild dehydration', 'treatment' => 'Oral rehydration therapy prescribed. BRAT diet recommended. Monitor for signs of dehydration.'],
                ['diagnosis' => 'Bronchitis', 'icd10' => 'J20.9', 'chief' => 'Child presents with persistent cough and low-grade fever', 'treatment' => 'Prescribed bronchodilators. Chest physiotherapy if needed. Increase fluid intake.'],
                ['diagnosis' => 'Childhood Asthma', 'icd10' => 'J45.9', 'chief' => 'Child presents with wheezing and shortness of breath, especially at night', 'treatment' => 'Prescribed bronchodilator inhaler. Trigger avoidance. Peak flow monitoring at home.'],
            ],
            'Orthopedics' => [
                ['diagnosis' => 'Lower Back Pain', 'icd10' => 'M54.5', 'chief' => 'Patient complains of persistent lower back pain aggravated by movement', 'treatment' => 'Prescribed NSAIDs and muscle relaxants. Physical therapy recommended. Ergonomic adjustments.'],
                ['diagnosis' => 'Ankle Fracture', 'icd10' => 'S92.9', 'chief' => 'Patient presents with swollen, painful ankle after fall', 'treatment' => 'Immobilization with cast. Prescribed analgesics. Follow-up X-ray in 2 weeks.'],
                ['diagnosis' => 'Knee Osteoarthritis', 'icd10' => 'M17.9', 'chief' => 'Patient reports gradual onset knee pain and stiffness, worse with activity', 'treatment' => 'Prescribed pain management. Joint protection techniques. Physical therapy referral.'],
                ['diagnosis' => 'Ankle Sprain', 'icd10' => 'S93.4', 'chief' => 'Patient presents with swollen ankle after twisting injury', 'treatment' => 'RICE protocol advised. Prescribed anti-inflammatories. Compression bandage applied.'],
            ],
            'Neurology' => [
                ['diagnosis' => 'Migraine without Aura', 'icd10' => 'G43.9', 'chief' => 'Patient reports recurrent severe unilateral headaches with nausea and photophobia', 'treatment' => 'Prescribed acute migraine therapy. Trigger identification and avoidance advised. Sleep hygiene.'],
                ['diagnosis' => 'Tension-Type Headache', 'icd10' => 'G44.2', 'chief' => 'Patient complains of bilateral pressing headache lasting hours', 'treatment' => 'Prescribed analgesics. Stress management techniques. Neck and shoulder exercises.'],
                ['diagnosis' => 'Epilepsy', 'icd10' => 'G40.9', 'chief' => 'Patient experienced a generalized tonic-clonic seizure', 'treatment' => 'Prescribed anticonvulsant therapy. Seizure diary recommended. Avoid driving until cleared.'],
                ['diagnosis' => 'Peripheral Neuropathy', 'icd10' => 'G62.9', 'chief' => 'Patient reports numbness and tingling in hands and feet', 'treatment' => 'Prescribed neuropathic pain medication. B12 supplementation. Glucose control optimization.'],
            ],
            'General Practice' => [
                ['diagnosis' => 'Common Cold', 'icd10' => 'J00', 'chief' => 'Patient presents with sore throat, nasal congestion, and mild cough', 'treatment' => 'Supportive care prescribed. Rest and hydration advised. Antipyretics if fever develops.'],
                ['diagnosis' => 'Acute Gastritis', 'icd10' => 'K29.7', 'chief' => 'Patient complains of epigastric pain, nausea, and burning sensation', 'treatment' => 'Prescribed PPI therapy. Dietary modifications recommended. Avoid spicy and acidic foods.'],
                ['diagnosis' => 'Allergic Rhinitis', 'icd10' => 'J30.1', 'chief' => 'Patient presents with sneezing, runny nose, and itchy eyes', 'treatment' => 'Prescribed antihistamines. Nasal spray if needed. Allergen avoidance advised.'],
                ['diagnosis' => 'Vitamin D Deficiency', 'icd10' => 'E55.9', 'chief' => 'Patient reports fatigue, bone pain, and muscle weakness', 'treatment' => 'Prescribed Vitamin D supplementation. Increased sun exposure advised. Calcium-rich diet recommended.'],
            ],
        ];

        $medicationMap = [
            'Hypertension' => ['medicines' => [0 => 'Panadol 500mg', 3 => 'Ibuprofen 400mg'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '1 month'],
            'Coronary Artery Disease' => ['medicines' => [0 => 'Panadol 500mg'], 'dosage' => '1 tablet', 'frequency' => 'As needed', 'duration' => '1 week'],
            'Arrhythmia' => ['medicines' => [12 => 'Omeprazole 20mg'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '1 month'],
            'Heart Failure' => ['medicines' => [12 => 'Omeprazole 20mg', 13 => 'Nexium 40mg'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '1 month'],
            'Eczema' => ['medicines' => [9 => 'Zyrtec 10mg', 10 => 'Clarinase'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '2 weeks'],
            'Acne Vulgaris' => ['medicines' => [8 => 'Ciprodar 500mg'], 'dosage' => '1 tablet', 'frequency' => 'Twice daily', 'duration' => '1 week'],
            'Fungal Infection' => ['medicines' => [8 => 'Ciprodar 500mg'], 'dosage' => '1 tablet', 'frequency' => 'Twice daily', 'duration' => '10 days'],
            'Psoriasis' => ['medicines' => [9 => 'Zyrtec 10mg'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '2 weeks'],
            'Upper Respiratory Tract Infection' => ['medicines' => [0 => 'Panadol 500mg', 2 => 'Adol 120mg'], 'dosage' => '5ml', 'frequency' => 'Every 6 hours', 'duration' => '5 days'],
            'Acute Gastroenteritis' => ['medicines' => [14 => 'Buscopan', 12 => 'Omeprazole 20mg'], 'dosage' => '1 tablet', 'frequency' => 'Three times daily', 'duration' => '3 days'],
            'Bronchitis' => ['medicines' => [11 => 'Ventolin Inhaler', 6 => 'Augmentin 625mg'], 'dosage' => '1 puff', 'frequency' => 'Every 4-6 hours as needed', 'duration' => '1 week'],
            'Childhood Asthma' => ['medicines' => [11 => 'Ventolin Inhaler'], 'dosage' => '1 puff', 'frequency' => 'Every 4-6 hours as needed', 'duration' => '1 month'],
            'Lower Back Pain' => ['medicines' => [3 => 'Ibuprofen 400mg', 4 => 'Volfast 50mg'], 'dosage' => '1 tablet', 'frequency' => 'Twice daily', 'duration' => '1 week'],
            'Ankle Fracture' => ['medicines' => [3 => 'Ibuprofen 400mg', 0 => 'Panadol 500mg'], 'dosage' => '1 tablet', 'frequency' => 'Every 8 hours', 'duration' => '1 week'],
            'Knee Osteoarthritis' => ['medicines' => [4 => 'Volfast 50mg'], 'dosage' => '1 sachet', 'frequency' => 'Twice daily', 'duration' => '2 weeks'],
            'Ankle Sprain' => ['medicines' => [4 => 'Volfast 50mg', 3 => 'Ibuprofen 400mg'], 'dosage' => '1 tablet', 'frequency' => 'Twice daily', 'duration' => '5 days'],
            'Migraine without Aura' => ['medicines' => [1 => 'Panadol Extra', 0 => 'Panadol 500mg'], 'dosage' => '2 tablets', 'frequency' => 'Every 6 hours as needed', 'duration' => '3 days'],
            'Tension-Type Headache' => ['medicines' => [0 => 'Panadol 500mg'], 'dosage' => '1 tablet', 'frequency' => 'Every 6 hours as needed', 'duration' => '3 days'],
            'Epilepsy' => ['medicines' => [12 => 'Omeprazole 20mg'], 'dosage' => '1 capsule', 'frequency' => 'Once daily', 'duration' => '1 month'],
            'Peripheral Neuropathy' => ['medicines' => [1 => 'Panadol Extra'], 'dosage' => '1 tablet', 'frequency' => 'Twice daily', 'duration' => '2 weeks'],
            'Common Cold' => ['medicines' => [0 => 'Panadol 500mg', 9 => 'Zyrtec 10mg'], 'dosage' => '1 tablet', 'frequency' => 'Every 8 hours', 'duration' => '5 days'],
            'Acute Gastritis' => ['medicines' => [13 => 'Nexium 40mg', 14 => 'Buscopan'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '2 weeks'],
            'Allergic Rhinitis' => ['medicines' => [9 => 'Zyrtec 10mg', 10 => 'Clarinase'], 'dosage' => '1 tablet', 'frequency' => 'Once daily', 'duration' => '2 weeks'],
            'Vitamin D Deficiency' => ['medicines' => [12 => 'Omeprazole 20mg'], 'dosage' => '1 capsule', 'frequency' => 'Once daily', 'duration' => '1 month'],
        ];

        $medicines = Medicine::all()->keyBy('name');

        foreach ($completedAppts as $appt) {
            $doctor = $appt->doctor;
            $specialty = $doctor ? $doctor->specialty : 'General Practice';
            $matrix = $clinicalMatrix[$specialty] ?? $clinicalMatrix['General Practice'];
            $entry = $matrix[array_rand($matrix)];

            $vitalSigns = [
                'bp_systolic' => rand(110, 140),
                'bp_diastolic' => rand(70, 90),
                'pulse' => rand(60, 100),
                'temp' => rand(360, 380) / 10,
                'weight' => rand(50, 100),
                'height' => rand(150, 190),
            ];

            $record = MedicalRecord::create([
                'patient_id' => $appt->patient_id,
                'doctor_id' => $appt->doctor_id,
                'appointment_id' => $appt->id,
                'visit_date' => $appt->date,
                'chief_complaint' => $entry['chief'],
                'diagnosis' => $entry['diagnosis'],
                'diagnosis_code' => $entry['icd10'],
                'treatment_plan' => $entry['treatment'],
                'vital_signs' => $vitalSigns,
                'notes' => rand(0, 1) ? 'Patient responded well to treatment.' : null,
                'follow_up_date' => rand(0, 1) ? $appt->date->copy()->addDays(rand(7, 30)) : null,
            ]);

            if (rand(0, 10) > 1) {
                $prescription = Prescription::create(['medical_record_id' => $record->id]);
                $medData = $medicationMap[$entry['diagnosis']] ?? null;

                if ($medData) {
                    foreach ($medData['medicines'] as $idx => $medName) {
                        if ($idx === 0 || rand(0, 10) > 4) {
                            $dosage = $medData['dosage'];
                            $frequency = $medData['frequency'];
                            $duration = $medData['duration'];
                            $instructions = ['After meals', 'Before meals', 'Before sleep', 'As needed', 'With plenty of water'][array_rand(['After meals', 'Before meals', 'Before sleep', 'As needed', 'With plenty of water'])];

                            PrescriptionItem::create([
                                'prescription_id' => $prescription->id,
                                'medication_name' => $medName,
                                'dosage' => $dosage,
                                'frequency' => $frequency,
                                'duration' => $duration,
                                'quantity' => rand(1, 3),
                                'instructions' => $instructions,
                            ]);
                        }
                    }
                } else {
                    $randMeds = $medicines->random(min(rand(1, 2), $medicines->count()));
                    $dosages = ['1 tablet', '5ml', '1 capsule', '1 sachet'];
                    $frequencies = ['Once daily', 'Twice daily', 'Every 8 hours', 'Every 6 hours'];
                    $durations = ['3 days', '5 days', '1 week', '10 days'];
                    $allInstructions = ['After meals', 'Before meals', 'Before sleep', 'As needed'];

                    foreach ($randMeds as $med) {
                        PrescriptionItem::create([
                            'prescription_id' => $prescription->id,
                            'medication_name' => $med->name,
                            'dosage' => $dosages[array_rand($dosages)],
                            'frequency' => $frequencies[array_rand($frequencies)],
                            'duration' => $durations[array_rand($durations)],
                            'quantity' => rand(1, 3),
                            'instructions' => $allInstructions[array_rand($allInstructions)],
                        ]);
                    }
                }
            }
        }
    }
}
