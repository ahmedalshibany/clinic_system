<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class MedicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $completedAppointments = Appointment::where('status', 'completed')->get();
        $medicines = Medicine::all();

        foreach ($completedAppointments as $appt) {
            
            // Create Medical Record
            $record = MedicalRecord::create([
                'patient_id' => $appt->patient_id,
                'doctor_id' => $appt->doctor_id,
                'appointment_id' => $appt->id,
                'visit_date' => $appt->date,
                'chief_complaint' => $faker->sentence,
                'diagnosis' => $faker->randomElement(['Common Cold', 'Flu', 'Migraine', 'Gastritis', 'Hypertension', 'Bronchitis', 'Allergic Rhinitis']),
                'treatment_plan' => $faker->paragraph,
                'notes' => $faker->optional()->sentence,
                'vital_signs' => [
                    'bp_systolic' => $faker->numberBetween(110, 140),
                    'bp_diastolic' => $faker->numberBetween(70, 90),
                    'pulse' => $faker->numberBetween(60, 100),
                    'temp' => $faker->randomFloat(1, 36, 38),
                    'weight' => $faker->numberBetween(50, 100),
                    'height' => $faker->numberBetween(150, 190),
                ],
            ]);

            // Create Prescription (70% chance)
            if ($medicines->count() > 0 && $faker->boolean(70)) {
                $prescription = Prescription::create([
                    'medical_record_id' => $record->id,
                ]);

                // Add 1-3 Random Medicines
                $randomMeds = $medicines->random(rand(1, min(3, $medicines->count())));
                
                foreach ($randomMeds as $med) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medication_name' => $med->name, // Using name string as per schema
                        'dosage' => $faker->randomElement(['1 tablet', '5ml', '1 capsule']),
                        'frequency' => $faker->randomElement(['Once daily', 'Twice daily', 'Every 8 hours']),
                        'duration' => $faker->randomElement(['3 days', '5 days', '1 week']),
                        'quantity' => $faker->numberBetween(1, 3),
                        'instructions' => $faker->randomElement(['After meals', 'Before meals', 'Before sleep']),
                    ]);
                }
            }
        }
    }
}
