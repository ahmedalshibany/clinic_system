<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Services\VitalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class VitalTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected Patient $patient;
    protected Doctor $doctor;
    protected User $nurse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->patient = Patient::factory()->create();
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $this->doctor = Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'is_active' => true,
        ]);
        $this->nurse = User::factory()->create(['role' => 'nurse']);
    }

    protected function setUpServiceTest(): VitalService
    {
        $this->actingAs($this->nurse);
        return app(VitalService::class);
    }

    private function createAppointment(string $status): Appointment
    {
        return Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'date' => today()->toDateString(),
            'status' => $status,
        ]);
    }

    private function validVitalsData(): array
    {
        return [
            'temperature' => 36.6,
            'bp_systolic' => 120,
            'bp_diastolic' => 80,
            'pulse' => 72,
            'weight' => 70,
            'height' => 170,
            'respiratory_rate' => 16,
            'oxygen_saturation' => 98,
        ];
    }

    public function test_record_vitals_from_confirmed_transitions_to_waiting(): void
    {
        $appointment = $this->createAppointment(Appointment::STATUS_CONFIRMED);
        $service = $this->setUpServiceTest();

        $vital = $service->recordVitals($appointment, $this->validVitalsData());

        $this->assertDatabaseHas('vitals', ['id' => $vital->id]);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_WAITING,
        ]);
    }

    public function test_record_vitals_from_checked_in_transitions_to_waiting(): void
    {
        $appointment = $this->createAppointment(Appointment::STATUS_CHECKED_IN);
        $service = $this->setUpServiceTest();

        $vital = $service->recordVitals($appointment, $this->validVitalsData());

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_WAITING,
        ]);
    }

    public function test_record_vitals_from_pending_transitions_to_waiting(): void
    {
        $appointment = $this->createAppointment(Appointment::STATUS_PENDING);
        $service = $this->setUpServiceTest();

        $vital = $service->recordVitals($appointment, $this->validVitalsData());

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_WAITING,
        ]);
    }

    public function test_record_vitals_from_waiting_throws(): void
    {
        $appointment = $this->createAppointment(Appointment::STATUS_WAITING);
        $service = $this->setUpServiceTest();

        $this->expectException(\App\Exceptions\InvalidTransitionException::class);

        $service->recordVitals($appointment, $this->validVitalsData());
    }

    public function test_record_vitals_clears_vitals_unlocked(): void
    {
        $appointment = $this->createAppointment(Appointment::STATUS_CONFIRMED);
        $appointment->vitals_unlocked = true;
        $appointment->save();
        $service = $this->setUpServiceTest();

        $service->recordVitals($appointment, $this->validVitalsData());

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'vitals_unlocked' => false,
        ]);
    }

    public function test_record_vitals_via_http_flow(): void
    {
        $appointment = $this->createAppointment(Appointment::STATUS_CONFIRMED);

        $response = $this->actingAs($this->nurse)
            ->post(route('nurse.vitals.store', $appointment), $this->validVitalsData());

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_WAITING,
        ]);
    }
}
