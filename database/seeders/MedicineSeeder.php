<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            // Pain / Fever
            ['name' => 'Panadol 500mg', 'generic_name' => 'Paracetamol', 'form' => 'Tablet', 'strength' => '500mg'],
            ['name' => 'Panadol Extra', 'generic_name' => 'Paracetamol + Caffeine', 'form' => 'Tablet', 'strength' => '500mg/65mg'],
            ['name' => 'Adol 120mg', 'generic_name' => 'Paracetamol', 'form' => 'Syrup', 'strength' => '120mg/5ml'],
            ['name' => 'Ibuprofen 400mg', 'generic_name' => 'Ibuprofen', 'form' => 'Tablet', 'strength' => '400mg'],
            ['name' => 'Volfast 50mg', 'generic_name' => 'Diclofenac Potassium', 'form' => 'Sachet', 'strength' => '50mg'],

            // Antibiotics
            ['name' => 'Augmentin 1g', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'form' => 'Tablet', 'strength' => '875mg/125mg'],
            ['name' => 'Augmentin 625mg', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'form' => 'Tablet', 'strength' => '500mg/125mg'],
            ['name' => 'Zithromax 500mg', 'generic_name' => 'Azithromycin', 'form' => 'Tablet', 'strength' => '500mg'],
            ['name' => 'Ciprodar 500mg', 'generic_name' => 'Ciprofloxacin', 'form' => 'Tablet', 'strength' => '500mg'],
            
            // Respiratory / Allergy
            ['name' => 'Zyrtec 10mg', 'generic_name' => 'Cetirizine', 'form' => 'Tablet', 'strength' => '10mg'],
            ['name' => 'Clarinase', 'generic_name' => 'Loratadine + Pseudoephedrine', 'form' => 'Tablet', 'strength' => '5mg/120mg'],
            ['name' => 'Ventolin Inhaler', 'generic_name' => 'Salbutamol', 'form' => 'Inhaler', 'strength' => '100mcg/dose'],

            // GI
            ['name' => 'Omeprazole 20mg', 'generic_name' => 'Omeprazole', 'form' => 'Capsule', 'strength' => '20mg'],
            ['name' => 'Nexium 40mg', 'generic_name' => 'Esomeprazole', 'form' => 'Tablet', 'strength' => '40mg'],
            ['name' => 'Buscopan', 'generic_name' => 'Hyoscine Butylbromide', 'form' => 'Tablet', 'strength' => '10mg'],
        ];

        foreach ($medicines as $med) {
            Medicine::updateOrCreate(['name' => $med['name']], $med);
        }
    }
}
