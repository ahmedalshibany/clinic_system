<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'code' => 'CON-001',
                'name' => 'General Consultation',
                'name_ar' => 'استشارة عامة',
                'category' => 'consultation',
                'price' => 50.00,
            ],
            [
                'code' => 'CON-002',
                'name' => 'Specialist Consultation',
                'name_ar' => 'استشارة أخصائي',
                'category' => 'consultation',
                'price' => 100.00,
            ],
            [
                'code' => 'FOL-001',
                'name' => 'Follow-up Visit',
                'name_ar' => 'زيارة متابعة',
                'category' => 'consultation',
                'price' => 30.00,
            ],
            [
                'code' => 'LAB-001',
                'name' => 'Complete Blood Count (CBC)',
                'name_ar' => 'تحليل صورة دم كاملة',
                'category' => 'lab',
                'price' => 25.00,
            ],
            [
                'code' => 'LAB-002',
                'name' => 'Blood Glucose Test',
                'name_ar' => 'تحليل سكر دم',
                'category' => 'lab',
                'price' => 15.00,
            ],
            [
                'code' => 'IMG-001',
                'name' => 'X-Ray (Chest)',
                'name_ar' => 'أشعة سينية على الصدر',
                'category' => 'imaging',
                'price' => 75.00,
            ],
            [
                'code' => 'PRC-001',
                'name' => 'Wound Dressing',
                'name_ar' => 'غيار جروح',
                'category' => 'procedure',
                'price' => 40.00,
            ],
            [
                'code' => 'PRC-002',
                'name' => 'IV Injection',
                'name_ar' => 'حقن وريدي',
                'category' => 'procedure',
                'price' => 20.00,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
