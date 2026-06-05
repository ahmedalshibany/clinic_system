<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\DoctorLeave;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Medicine;
use App\Models\Setting;
use App\Models\Appointment;
use App\Models\Vital;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PatientFile;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedDoctors();
        $this->seedDoctorSchedules();
        $this->seedDoctorLeaves();
        $this->seedPatients();
        $this->seedServices();
        $this->seedMedicines();
        $this->seedSettings();
        $this->seedAppointments();
        $this->seedVitals();
        $this->seedMedicalRecords();
        $this->seedInvoices();
        $this->seedNotifications();
    }

    protected function seedUsers(): void
    {
        User::create(['name' => 'Administrator', 'username' => 'admin', 'password' => Hash::make('admin123'), 'role' => 'admin', 'is_active' => true]);
        User::create(['name' => 'Dr. John Smith', 'username' => 'doctor', 'password' => Hash::make('doctor123'), 'role' => 'doctor', 'is_active' => true]);
        User::create(['name' => 'Sarah Johnson', 'username' => 'receptionist', 'password' => Hash::make('reception123'), 'role' => 'receptionist', 'is_active' => true]);
        User::create(['name' => 'Nurse Joy', 'username' => 'nurse_joy', 'password' => Hash::make('password'), 'role' => 'nurse', 'is_active' => true]);
    }

    protected function seedDoctors(): void
    {
        $doctors = [
            ['name' => 'Dr. Ahmed Al-Qadhi', 'specialty' => 'Cardiology', 'phone' => '+967 711 111 111', 'email' => 'ahmed.qadhi@clinic.com', 'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'work_start_time' => '08:00', 'work_end_time' => '16:00', 'consultation_fee' => 5000, 'is_active' => true],
            ['name' => 'Dr. Fatima Al-Sharif', 'specialty' => 'Dermatology', 'phone' => '+967 711 222 222', 'email' => 'fatima.sharif@clinic.com', 'working_days' => ['Sunday', 'Monday', 'Wednesday', 'Thursday'], 'work_start_time' => '09:00', 'work_end_time' => '17:00', 'consultation_fee' => 4000, 'is_active' => true],
            ['name' => 'Dr. Mohammed Al-Hamdani', 'specialty' => 'Pediatrics', 'phone' => '+967 711 333 333', 'email' => 'mohammed.hamdani@clinic.com', 'working_days' => ['Sunday', 'Tuesday', 'Thursday'], 'work_start_time' => '08:00', 'work_end_time' => '14:00', 'consultation_fee' => 3500, 'is_active' => true],
            ['name' => 'Dr. Aisha Al-Maqtari', 'specialty' => 'Orthopedics', 'phone' => '+967 711 444 444', 'email' => 'aisha.maqtari@clinic.com', 'working_days' => ['Monday', 'Wednesday', 'Thursday'], 'work_start_time' => '10:00', 'work_end_time' => '18:00', 'consultation_fee' => 6000, 'is_active' => true],
            ['name' => 'Dr. Yusuf Al-Nahdi', 'specialty' => 'Neurology', 'phone' => '+967 711 555 555', 'email' => 'yusuf.nahdi@clinic.com', 'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday'], 'work_start_time' => '08:00', 'work_end_time' => '15:00', 'consultation_fee' => 7000, 'is_active' => true],
            ['name' => 'Dr. Mariam Al-Zubaydi', 'specialty' => 'General Practice', 'phone' => '+967 711 666 666', 'email' => 'mariam.zubaydi@clinic.com', 'working_days' => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'], 'work_start_time' => '08:00', 'work_end_time' => '20:00', 'consultation_fee' => 2500, 'is_active' => true],
        ];

        foreach ($doctors as $data) {
            Doctor::create($data);
        }
    }

    protected function seedDoctorSchedules(): void
    {
        $doctors = Doctor::all();
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($doctors as $doctor) {
            $days = $doctor->working_days ?? ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
            foreach ($days as $day) {
                $dayNum = array_search($day, $dayNames);
                if ($dayNum === false) continue;
                DoctorSchedule::firstOrCreate([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $dayNum,
                ], [
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00',
                    'slot_duration' => 30,
                    'max_appointments' => 16,
                    'is_active' => true,
                ]);
            }
        }
    }

    protected function seedDoctorLeaves(): void
    {
        $doctors = Doctor::all();
        if ($doctors->isEmpty()) return;

        foreach ($doctors->random(min(2, $doctors->count())) as $doctor) {
            $start = Carbon::now()->addDays(rand(15, 30));
            DoctorLeave::create([
                'doctor_id' => $doctor->id,
                'start_date' => $start,
                'end_date' => (clone $start)->addDays(rand(1, 3)),
                'reason' => 'Annual leave',
            ]);
        }
    }

    protected function seedPatients(): void
    {
        $staticPatients = [
            ['name' => 'Ahmed Hassan', 'age' => 35, 'gender' => 'male', 'phone' => '+967 777 123 456', 'address' => 'Sana\'a, Yemen', 'blood_type' => 'O+'],
            ['name' => 'Fatima Ali', 'age' => 28, 'gender' => 'female', 'phone' => '+967 777 234 567', 'address' => 'Aden, Yemen', 'blood_type' => 'A+'],
        ];

        foreach ($staticPatients as $data) {
            Patient::create($data);
        }

        $firstNames = ['Mohammed', 'Ali', 'Omar', 'Hassan', 'Khalid', 'Abdullah', 'Ibrahim', 'Saeed', 'Nasser', 'Tariq', 'Yahya', 'Hussein', 'Saleh', 'Jamal', 'Fahd', 'Aisha', 'Mariam', 'Khadija', 'Noor', 'Huda', 'Layla', 'Sara', 'Rania', 'Amira', 'Nadia', 'Samira', 'Duaa', 'Rasha', 'Iman', 'Hind'];
        $lastNames = ['Al-Harbi', 'Al-Qahtani', 'Al-Otaibi', 'Al-Ghamdi', 'Al-Zahrani', 'Al-Shammari', 'Al-Maliki', 'Al-Anzi', 'Al-Dossary', 'Al-Subaie'];
        $genders = ['male', 'female'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        $cities = ['Sana\'a', 'Aden', 'Taiz', 'Hodeidah', 'Ibb', 'Mukalla', 'Dhamar', 'Saada', 'Marib', 'Al-Bayda'];

        for ($i = 0; $i < 50; $i++) {
            $gender = $genders[array_rand($genders)];
            $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
            Patient::create([
                'name' => $name,
                'age' => rand(1, 85),
                'gender' => $gender,
                'phone' => '+967 7' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                'address' => $cities[array_rand($cities)] . ', Yemen',
                'blood_type' => $bloodTypes[array_rand($bloodTypes)],
            ]);
        }
    }

    protected function seedServices(): void
    {
        $services = [
            ['code' => 'CON-001', 'name' => 'General Consultation', 'name_ar' => 'استشارة عامة', 'category' => 'consultation', 'price' => 50.00],
            ['code' => 'CON-002', 'name' => 'Specialist Consultation', 'name_ar' => 'استشارة أخصائي', 'category' => 'consultation', 'price' => 100.00],
            ['code' => 'FOL-001', 'name' => 'Follow-up Visit', 'name_ar' => 'زيارة متابعة', 'category' => 'consultation', 'price' => 30.00],
            ['code' => 'LAB-001', 'name' => 'Complete Blood Count (CBC)', 'name_ar' => 'تحليل صورة دم كاملة', 'category' => 'lab', 'price' => 25.00],
            ['code' => 'LAB-002', 'name' => 'Blood Glucose Test', 'name_ar' => 'تحليل سكر دم', 'category' => 'lab', 'price' => 15.00],
            ['code' => 'IMG-001', 'name' => 'X-Ray (Chest)', 'name_ar' => 'أشعة سينية على الصدر', 'category' => 'imaging', 'price' => 75.00],
            ['code' => 'PRC-001', 'name' => 'Wound Dressing', 'name_ar' => 'غيار جروح', 'category' => 'procedure', 'price' => 40.00],
            ['code' => 'PRC-002', 'name' => 'IV Injection', 'name_ar' => 'حقن وريدي', 'category' => 'procedure', 'price' => 20.00],
        ];

        foreach ($services as $data) {
            Service::create($data);
        }
    }

    protected function seedMedicines(): void
    {
        $medicines = [
            ['name' => 'Panadol 500mg', 'generic_name' => 'Paracetamol', 'form' => 'Tablet', 'strength' => '500mg'],
            ['name' => 'Panadol Extra', 'generic_name' => 'Paracetamol + Caffeine', 'form' => 'Tablet', 'strength' => '500mg/65mg'],
            ['name' => 'Adol 120mg', 'generic_name' => 'Paracetamol', 'form' => 'Syrup', 'strength' => '120mg/5ml'],
            ['name' => 'Ibuprofen 400mg', 'generic_name' => 'Ibuprofen', 'form' => 'Tablet', 'strength' => '400mg'],
            ['name' => 'Volfast 50mg', 'generic_name' => 'Diclofenac Potassium', 'form' => 'Sachet', 'strength' => '50mg'],
            ['name' => 'Augmentin 1g', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'form' => 'Tablet', 'strength' => '875mg/125mg'],
            ['name' => 'Augmentin 625mg', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'form' => 'Tablet', 'strength' => '500mg/125mg'],
            ['name' => 'Zithromax 500mg', 'generic_name' => 'Azithromycin', 'form' => 'Tablet', 'strength' => '500mg'],
            ['name' => 'Ciprodar 500mg', 'generic_name' => 'Ciprofloxacin', 'form' => 'Tablet', 'strength' => '500mg'],
            ['name' => 'Zyrtec 10mg', 'generic_name' => 'Cetirizine', 'form' => 'Tablet', 'strength' => '10mg'],
            ['name' => 'Clarinase', 'generic_name' => 'Loratadine + Pseudoephedrine', 'form' => 'Tablet', 'strength' => '5mg/120mg'],
            ['name' => 'Ventolin Inhaler', 'generic_name' => 'Salbutamol', 'form' => 'Inhaler', 'strength' => '100mcg/dose'],
            ['name' => 'Omeprazole 20mg', 'generic_name' => 'Omeprazole', 'form' => 'Capsule', 'strength' => '20mg'],
            ['name' => 'Nexium 40mg', 'generic_name' => 'Esomeprazole', 'form' => 'Tablet', 'strength' => '40mg'],
            ['name' => 'Buscopan', 'generic_name' => 'Hyoscine Butylbromide', 'form' => 'Tablet', 'strength' => '10mg'],
        ];

        foreach ($medicines as $data) {
            Medicine::create($data);
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            ['key' => 'clinic_name', 'value' => 'Al-Amal Clinic', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_name_ar', 'value' => 'عيادة الأمل', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_address', 'value' => '123 Main St, Cityville', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_phone', 'value' => '+1234567890', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'clinic_email', 'value' => 'info@alamal.com', 'type' => 'string', 'group' => 'clinic'],
            ['key' => 'default_language', 'value' => 'en', 'type' => 'string', 'group' => 'system'],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'system'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'system'],
            ['key' => 'time_format', 'value' => 'H:i', 'type' => 'string', 'group' => 'system'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'tax_rate', 'value' => 0, 'type' => 'integer', 'group' => 'invoice'],
            ['key' => 'default_due_days', 'value' => 30, 'type' => 'integer', 'group' => 'invoice'],
            ['key' => 'appointment_slot_duration', 'value' => 30, 'type' => 'integer', 'group' => 'appointment'],
            ['key' => 'advance_booking_days', 'value' => 30, 'type' => 'integer', 'group' => 'appointment'],
            ['key' => 'start_hour', 'value' => '09:00', 'type' => 'string', 'group' => 'appointment'],
            ['key' => 'end_hour', 'value' => '17:00', 'type' => 'string', 'group' => 'appointment'],
        ];

        foreach ($settings as $data) {
            Setting::updateOrCreate(['key' => $data['key']], $data);
        }
    }

    protected function seedAppointments(): void
    {
        $patientIds = Patient::pluck('id')->toArray();
        $doctorIds = Doctor::pluck('id')->toArray();
        $types = ['Consultation', 'Checkup', 'Follow-up', 'Emergency'];
        $times = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '14:00', '14:30', '15:00', '15:30', '16:00'];

        for ($i = 0; $i < 60; $i++) {
            $date = Carbon::today()->subDays(rand(1, 30));
            $r = rand(0, 10);
            $status = $r < 7 ? 'completed' : ($r < 9 ? 'cancelled' : 'no_show');
            Appointment::create([
                'patient_id' => $patientIds[array_rand($patientIds)],
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'date' => $date,
                'time' => $times[array_rand($times)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => rand(2000, 8000),
                'completed_at' => $status === 'completed' ? $date->copy()->setTime(rand(9, 16), 0) : null,
                'started_at' => $status === 'completed' ? $date->copy()->setTime(rand(9, 16), 0)->subMinutes(30) : null,
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            $timesForToday = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '14:00', '14:30', '15:00'];
            $status = $i < 3 ? 'completed' : ($i < 5 ? 'in_progress' : ($i > 7 ? 'confirmed' : 'waiting'));
            Appointment::create([
                'patient_id' => $patientIds[array_rand($patientIds)],
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'date' => Carbon::today(),
                'time' => $timesForToday[$i % count($timesForToday)],
                'type' => $types[array_rand($types)],
                'status' => $status,
                'fee' => rand(2000, 8000),
                'checked_in_at' => in_array($status, ['waiting', 'in_progress', 'completed']) ? Carbon::now()->subMinutes(rand(10, 120)) : null,
            ]);
        }

        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::today()->addDays(rand(1, 14));
            Appointment::create([
                'patient_id' => $patientIds[array_rand($patientIds)],
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'date' => $date,
                'time' => $times[array_rand($times)],
                'type' => $types[array_rand($types)],
                'status' => rand(0, 10) > 8 ? 'pending' : 'confirmed',
                'fee' => rand(2000, 8000),
            ]);
        }
    }

    protected function seedVitals(): void
    {
        $appointments = Appointment::where('status', 'completed')->get();
        $users = User::pluck('id')->toArray();
        if ($appointments->isEmpty() || empty($users)) return;

        foreach ($appointments->random(min(15, $appointments->count())) as $appt) {
            Vital::create([
                'appointment_id' => $appt->id,
                'created_by' => $users[array_rand($users)],
                'temperature' => rand(360, 390) / 10,
                'bp_systolic' => rand(100, 140),
                'bp_diastolic' => rand(60, 90),
                'pulse' => rand(60, 100),
                'respiratory_rate' => rand(12, 20),
                'weight' => rand(500, 1200) / 10,
                'height' => rand(1500, 1900) / 10,
                'oxygen_saturation' => rand(95, 100),
            ]);
        }
    }

    protected function seedMedicalRecords(): void
    {
        $completedAppts = Appointment::where('status', 'completed')->get();
        $medicines = Medicine::all();
        if ($completedAppts->isEmpty()) return;

        $diagnoses = ['Common Cold', 'Flu', 'Migraine', 'Gastritis', 'Hypertension', 'Bronchitis', 'Allergic Rhinitis', 'Sinusitis', 'Conjunctivitis', 'Tonsillitis'];
        $dosages = ['1 tablet', '5ml', '1 capsule', '1 sachet', '2 tablets', '1 spray'];
        $frequencies = ['Once daily', 'Twice daily', 'Every 8 hours', 'Every 6 hours', 'Once before bed'];
        $durations = ['3 days', '5 days', '1 week', '10 days', '2 weeks'];
        $instructions = ['After meals', 'Before meals', 'Before sleep', 'As needed', 'With plenty of water'];

        foreach ($completedAppts as $appt) {
            $record = MedicalRecord::create([
                'patient_id' => $appt->patient_id,
                'doctor_id' => $appt->doctor_id,
                'appointment_id' => $appt->id,
                'visit_date' => $appt->date,
                'chief_complaint' => 'Patient presents with ' . $diagnoses[array_rand($diagnoses)] . ' symptoms',
                'diagnosis' => $diagnoses[array_rand($diagnoses)],
                'treatment_plan' => 'Prescribed medication and rest. Follow-up in 1 week if symptoms persist.',
                'notes' => rand(0, 1) ? 'Patient responded well to treatment.' : null,
                'vital_signs' => [
                    'bp_systolic' => rand(110, 140),
                    'bp_diastolic' => rand(70, 90),
                    'pulse' => rand(60, 100),
                    'temp' => rand(360, 380) / 10,
                    'weight' => rand(50, 100),
                    'height' => rand(150, 190),
                ],
            ]);

            if ($medicines->isNotEmpty() && rand(0, 10) > 2) {
                $prescription = Prescription::create(['medical_record_id' => $record->id]);
                $count = rand(1, min(3, $medicines->count()));
                $selectedMeds = $medicines->random($count);

                foreach ($selectedMeds as $med) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medication_name' => $med->name,
                        'dosage' => $dosages[array_rand($dosages)],
                        'frequency' => $frequencies[array_rand($frequencies)],
                        'duration' => $durations[array_rand($durations)],
                        'quantity' => rand(1, 3),
                        'instructions' => $instructions[array_rand($instructions)],
                    ]);
                }
            }
        }
    }

    protected function seedInvoices(): void
    {
        $completedAppts = Appointment::where('status', 'completed')->get();
        $users = User::pluck('id')->toArray();
        if ($completedAppts->isEmpty() || empty($users)) return;

        $services = Service::all();
        $methods = ['cash', 'card'];

        foreach ($completedAppts as $appt) {
            $subtotal = $appt->fee > 0 ? $appt->fee : 3000;
            $total = $subtotal;

            $invoice = Invoice::create([
                'patient_id' => $appt->patient_id,
                'appointment_id' => $appt->id,
                'created_by' => $users[array_rand($users)],
                'due_date' => $appt->date->copy()->addDays(30),
                'status' => 'sent',
                'subtotal' => $subtotal,
                'total' => $total,
                'amount_paid' => 0,
                'created_at' => $appt->date,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Consultation Fee - ' . $appt->type,
                'quantity' => 1,
                'unit_price' => $subtotal,
                'total' => $subtotal,
            ]);

            if ($services->isNotEmpty() && rand(0, 10) > 5) {
                $svc = $services->random();
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $svc->id,
                    'description' => $svc->name,
                    'quantity' => 1,
                    'unit_price' => $svc->price,
                    'total' => $svc->price,
                ]);
                $total += $svc->price;
            }

            if ($appt->date->isPast() && rand(0, 10) > 2) {
                $invoice->update(['status' => 'paid', 'amount_paid' => $total]);
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $total,
                    'payment_date' => $appt->date,
                    'payment_method' => $methods[array_rand($methods)],
                    'received_by' => $users[array_rand($users)],
                ]);
                $invoice->update(['total' => $total]);
            } else {
                $invoice->update(['total' => $total]);
            }
        }
    }

    protected function seedNotifications(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $messages = [
            'New patient registration completed successfully.',
            'Appointment schedule updated for tomorrow.',
            'Lab results are ready for review.',
            'Prescription refill request received.',
            'Invoice payment confirmed.',
            'New doctor account created.',
            'System maintenance scheduled for tonight.',
            'Monthly report is now available.',
        ];

        foreach ($users->random(min(3, $users->count())) as $user) {
            Notification::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'type' => 'system_alert',
                'title' => 'System Update',
                'message' => $messages[array_rand($messages)],
                'data' => json_encode(['version' => '1.0.0']),
                'read_at' => rand(0, 1) ? now() : null,
                'link' => '/dashboard',
            ]);
        }
    }
}
