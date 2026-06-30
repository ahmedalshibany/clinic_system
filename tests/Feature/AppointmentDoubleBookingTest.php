<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentDoubleBookingTest extends TestCase
{
    use RefreshDatabase;

    protected Patient $patient;
    protected Doctor $doctor;
    protected AppointmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->patient = Patient::factory()->create();
        $user = User::factory()->create(['role' => 'doctor']);
        $this->doctor = Doctor::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);
        // Create schedule for all weekdays so any date passes the availability check
        foreach ([1, 2, 3, 4, 5] as $day) {
            DoctorSchedule::create([
                'doctor_id' => $this->doctor->id,
                'day_of_week' => $day,
                'start_time' => '08:00',
                'end_time' => '17:00',
                'is_active' => true,
            ]);
        }
        $this->service = new AppointmentService();
    }

    public function test_create_appointment_rejects_duplicate_slot(): void
    {
        $appointment = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-15',
            'time' => '10:00',
            'type' => 'Consultation',
            'status' => 'waiting',
            'fee' => 200,
        ]);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertSame('pending', $appointment->status);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('messages.timeSlotBooked'));

        $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-15',
            'time' => '10:00',
            'type' => 'Checkup',
            'status' => 'waiting',
            'fee' => 150,
        ]);
    }

    public function test_update_appointment_rejects_conflicting_slot(): void
    {
        $appt1 = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-10',
            'time' => '09:00',
            'type' => 'Consultation',
            'status' => 'waiting',
            'fee' => 200,
        ]);

        $appt2 = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-10',
            'time' => '11:00',
            'type' => 'Follow-up',
            'status' => 'waiting',
            'fee' => 100,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('messages.timeSlotBooked'));

        $this->service->updateAppointment($appt2, [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-10',
            'time' => '09:00',
            'type' => 'Follow-up',
            'status' => 'waiting',
            'fee' => 100,
        ]);
    }

    public function test_update_appointment_allows_same_slot_when_self_reference(): void
    {
        $appointment = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-20',
            'time' => '14:00',
            'type' => 'Consultation',
            'status' => 'waiting',
            'fee' => 200,
        ]);

        $result = $this->service->updateAppointment($appointment, [
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-07-20',
            'time' => '14:00',
            'type' => 'Checkup',
            'status' => 'paid',
            'fee' => 250,
        ]);

        $this->assertTrue($result);
    }

    public function test_cancelled_appointment_does_not_block_slot(): void
    {
        $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-08-03',
            'time' => '10:00',
            'type' => 'Consultation',
            'status' => 'waiting',
            'fee' => 200,
        ]);

        $cancelled = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-08-03',
            'time' => '10:30',
            'type' => 'Checkup',
            'status' => 'waiting',
            'fee' => 150,
        ]);

        $cancelled->update(['status' => 'cancelled']);

        $replacement = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-08-03',
            'time' => '10:30',
            'type' => 'Follow-up',
            'status' => 'waiting',
            'fee' => 180,
        ]);

        $this->assertInstanceOf(Appointment::class, $replacement);
        $this->assertSame('pending', $replacement->status);
    }

    public function test_different_doctor_same_time_allowed(): void
    {
        $doctorUser2 = User::factory()->create(['role' => 'doctor']);
        $doctor2 = Doctor::factory()->create([
            'user_id' => $doctorUser2->id,
            'is_active' => true,
        ]);

        foreach ([1, 2, 3, 4, 5] as $day) {
            DoctorSchedule::create([
                'doctor_id' => $doctor2->id,
                'day_of_week' => $day,
                'start_time' => '08:00',
                'end_time' => '17:00',
                'is_active' => true,
            ]);
        }

        $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-09-01',
            'time' => '10:00',
            'type' => 'Consultation',
            'status' => 'waiting',
            'fee' => 200,
        ]);

        $appt2 = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $doctor2->id,
            'date' => '2026-09-01',
            'time' => '10:00',
            'type' => 'Checkup',
            'status' => 'waiting',
            'fee' => 150,
        ]);

        $this->assertInstanceOf(Appointment::class, $appt2);
    }

    public function test_different_date_same_doctor_time_allowed(): void
    {
        $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-10-01',
            'time' => '10:00',
            'type' => 'Consultation',
            'status' => 'waiting',
            'fee' => 200,
        ]);

        $appt2 = $this->service->createAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => '2026-10-02',
            'time' => '10:00',
            'type' => 'Follow-up',
            'status' => 'waiting',
            'fee' => 100,
        ]);

        $this->assertInstanceOf(Appointment::class, $appt2);
    }
}
