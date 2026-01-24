<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FinancialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Find completed appointments to invoice
        $completedAppointments = Appointment::where('status', 'completed')->get();
        $adminUser = User::first(); 

        foreach ($completedAppointments as $appt) {
            
            // 1. Create Invoice
            $invoice = Invoice::create([
                'patient_id' => $appt->patient_id,
                'appointment_id' => $appt->id,
                'created_by' => $adminUser ? $adminUser->id : 1,
                'invoice_number' => 'INV-' . strtoupper($faker->unique()->bothify('????-####')),
                'due_date' => $appt->date->copy()->addDays(30),
                'status' => 'draft',
                'total' => 0,
                'subtotal' => 0,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'amount_paid' => 0,
                'created_at' => $appt->date, 
            ]);

            // 2. Add Fee Item
            $consultationFee = $appt->fee > 0 ? $appt->fee : 3000;
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Consultation Fee - ' . $appt->type,
                'quantity' => 1,
                'unit_price' => $consultationFee,
                'total' => $consultationFee,
            ]);

            // 3. Add Random Medicine Items (for 40% of patients)
            $medCost = 0;
            if ($faker->boolean(40)) {
                $medPrice = $faker->numberBetween(500, 5000);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Pharmacy Charges',
                    'quantity' => 1,
                    'unit_price' => $medPrice,
                    'total' => $medPrice,
                ]);
                $medCost = $medPrice;
            }

            // Update Totals
            $subtotal = $consultationFee + $medCost;
            $total = $subtotal; 
            
            // 4. Payment Logic
            $status = 'sent'; 
            $paidAmount = 0;
            
            // 80% chance of being paid fully if date is in past
            if ($appt->date->isPast() && $faker->boolean(80)) {
                $status = 'paid';
                $paidAmount = $total;
                
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $paidAmount,
                    'payment_date' => $appt->date,
                    'payment_method' => $faker->randomElement(['cash', 'card']),
                    'reference_number' => $faker->uuid, // Fixed column name
                    'received_by' => $adminUser ? $adminUser->id : 1, // Added missing column
                    'notes' => 'Payment received at reception',
                ]);
            }

            $invoice->update([
                'total' => $total,
                'subtotal' => $subtotal,
                'amount_paid' => $paidAmount,
                'status' => $status
            ]);
        }
    }
}
