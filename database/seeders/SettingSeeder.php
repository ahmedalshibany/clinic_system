<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Clinic Info
            ['key' => 'clinic_name', 'value' => 'Al-Amal Clinic', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_name_ar', 'value' => 'عيادة الأمل', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_address', 'value' => '123 Main St, Cityville', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_phone', 'value' => '+1234567890', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_email', 'value' => 'info@alamal.com', 'type' => 'string', 'group' => 'clinic'],
            
            // System
            ['key' => 'default_language', 'value' => 'en', 'type' => 'string', 'group' => 'system'],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'system'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'system'],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string', 'group' => 'system'],
            
            // Finance
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'tax_rate', 'value' => 0, 'type' => 'integer', 'group' => 'invoice'],
            ['key' => 'default_due_days', 'value' => 30, 'type' => 'integer', 'group' => 'invoice'],

            // Appointments
            ['key' => 'appointment_slot_duration', 'value' => 30, 'type' => 'integer', 'group' => 'appointment'],
            ['key' => 'advance_booking_days', 'value' => 30, 'type' => 'integer', 'group' => 'appointment'],
            ['key' => 'start_hour', 'value' => '09:00', 'type' => 'string', 'group' => 'appointment'],
            ['key' => 'end_hour', 'value' => '17:00', 'type' => 'string', 'group' => 'appointment'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
