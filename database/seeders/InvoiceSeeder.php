<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $completedAppts = Appointment::where('status', 'completed')->get();
        $users = User::pluck('id')->toArray();
        if ($completedAppts->isEmpty() || empty($users)) return;

        $services = Service::all();
        $methods = ['cash', 'card', 'bank_transfer', 'insurance'];

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
                'total' => $subtotal,
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
                $svcTotal = $svc->price * 1;
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $svc->id,
                    'description' => $svc->name,
                    'quantity' => 1,
                    'unit_price' => $svc->price,
                    'total' => $svcTotal,
                ]);
                $total += $svcTotal;
            }

            if ($appt->date->isPast() && rand(0, 10) > 2) {
                $payMethod = $methods[array_rand($methods)];
                $invoice->update(['status' => 'paid', 'amount_paid' => $total, 'total' => $total]);
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $total,
                    'payment_date' => $appt->date,
                    'payment_method' => $payMethod,
                    'received_by' => $users[array_rand($users)],
                ]);
            } else {
                $invoice->update(['total' => $total]);
            }
        }
    }
}
