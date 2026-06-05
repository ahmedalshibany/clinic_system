<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            ['name' => 'Panadol 500mg', 'generic_name' => 'Paracetamol', 'form' => 'Tablet', 'strength' => '500mg', 'stock' => 500],
            ['name' => 'Panadol Extra', 'generic_name' => 'Paracetamol + Caffeine', 'form' => 'Tablet', 'strength' => '500mg/65mg', 'stock' => 400],
            ['name' => 'Adol 120mg', 'generic_name' => 'Paracetamol', 'form' => 'Syrup', 'strength' => '120mg/5ml', 'stock' => 200],
            ['name' => 'Ibuprofen 400mg', 'generic_name' => 'Ibuprofen', 'form' => 'Tablet', 'strength' => '400mg', 'stock' => 350],
            ['name' => 'Volfast 50mg', 'generic_name' => 'Diclofenac Potassium', 'form' => 'Sachet', 'strength' => '50mg', 'stock' => 150],
            ['name' => 'Augmentin 1g', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'form' => 'Tablet', 'strength' => '875mg/125mg', 'stock' => 300],
            ['name' => 'Augmentin 625mg', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'form' => 'Tablet', 'strength' => '500mg/125mg', 'stock' => 300],
            ['name' => 'Zithromax 500mg', 'generic_name' => 'Azithromycin', 'form' => 'Tablet', 'strength' => '500mg', 'stock' => 200],
            ['name' => 'Ciprodar 500mg', 'generic_name' => 'Ciprofloxacin', 'form' => 'Tablet', 'strength' => '500mg', 'stock' => 250],
            ['name' => 'Zyrtec 10mg', 'generic_name' => 'Cetirizine', 'form' => 'Tablet', 'strength' => '10mg', 'stock' => 300],
            ['name' => 'Clarinase', 'generic_name' => 'Loratadine + Pseudoephedrine', 'form' => 'Tablet', 'strength' => '5mg/120mg', 'stock' => 200],
            ['name' => 'Ventolin Inhaler', 'generic_name' => 'Salbutamol', 'form' => 'Inhaler', 'strength' => '100mcg/dose', 'stock' => 100],
            ['name' => 'Omeprazole 20mg', 'generic_name' => 'Omeprazole', 'form' => 'Capsule', 'strength' => '20mg', 'stock' => 400],
            ['name' => 'Nexium 40mg', 'generic_name' => 'Esomeprazole', 'form' => 'Tablet', 'strength' => '40mg', 'stock' => 250],
            ['name' => 'Buscopan', 'generic_name' => 'Hyoscine Butylbromide', 'form' => 'Tablet', 'strength' => '10mg', 'stock' => 300],
        ];

        foreach ($medicines as $data) {
            Medicine::create($data);
        }
    }
}
