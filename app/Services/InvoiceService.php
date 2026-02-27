<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Exception;

class InvoiceService
{
    /**
     * Create a new invoice.
     *
     * @param array $data
     * @param int|null $creatorId
     * @return Invoice
     */
    public function createInvoice(array $data, ?int $creatorId = null): Invoice
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                
                $itemsData[] = [
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                    'discount' => 0, // Item level discount not in form yet, default 0
                ];
            }

            $discount_percent = $data['discount_percent'] ?? 0;
            $tax_percent = $data['tax_percent'] ?? 0;
            
            $discount_amount = $subtotal * ($discount_percent / 100);
            $taxable = $subtotal - $discount_amount;
            $tax_amount = $taxable * ($tax_percent / 100);
            $total = $taxable + $tax_amount;

            $invoice = Invoice::create([
                'patient_id' => $data['patient_id'],
                'appointment_id' => $data['appointment_id'] ?? null,
                'created_by' => $creatorId,
                'subtotal' => $subtotal,
                'discount_percent' => $discount_percent,
                'discount_amount' => $discount_amount,
                'tax_percent' => $tax_percent,
                'tax_amount' => $tax_amount,
                'total' => $total,
                'amount_paid' => 0,
                'status' => $data['status'] ?? 'draft',
                'due_date' => $data['due_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    /**
     * Update an invoice.
     *
     * @param mixed $id
     * @param array $data
     * @return Invoice
     * @throws Exception
     */
    public function updateInvoice($id, array $data): Invoice
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);

        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            throw new Exception('Cannot edit paid or cancelled invoices.');
        }

        return DB::transaction(function () use ($invoice, $data) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $lineTotal;
                
                $itemsData[] = [
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                    'discount' => 0,
                ];
            }

            $discount_percent = $data['discount_percent'] ?? 0;
            $tax_percent = $data['tax_percent'] ?? 0;
            
            $discount_amount = $subtotal * ($discount_percent / 100);
            $taxable = $subtotal - $discount_amount;
            $tax_amount = $taxable * ($tax_percent / 100);
            $total = $taxable + $tax_amount;

            $invoice->update([
                'subtotal' => $subtotal,
                'discount_percent' => $discount_percent,
                'discount_amount' => $discount_amount,
                'tax_percent' => $tax_percent,
                'tax_amount' => $tax_amount,
                'total' => $total,
                'status' => $data['status'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Sync items
            $invoice->items()->delete();
            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    /**
     * Add a payment to an invoice.
     *
     * @param mixed $id
     * @param array $data
     * @param int|null $receiverId
     * @return Payment
     * @throws Exception
     */
    public function addPayment($id, array $data, ?int $receiverId = null): Payment
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);

        if ($data['amount'] <= 0 || $data['amount'] > ($invoice->total - $invoice->amount_paid)) {
            throw new Exception('Invalid payment amount (exceeds balance or is <= 0).');
        }

        return DB::transaction(function () use ($invoice, $data, $receiverId) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'received_by' => $receiverId,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update invoice status and amount paid
            $invoice->amount_paid += $data['amount'];
            
            if ($invoice->amount_paid >= $invoice->total) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            $invoice->save();

            // Notify Admins
            try {
                app(\App\Services\NotificationService::class)->notifyAdmins(
                    'payment', 
                    'Payment Received', 
                    "Received {$data['amount']} for Invoice #{$invoice->invoice_number}",
                    ['invoice_id' => $invoice->id, 'amount' => $data['amount']],
                    route('invoices.show', $invoice->id)
                );
            } catch (\Exception $e) {}

            return $payment;
        });
    }

    /**
     * Safely delete an invoice.
     *
     * @param mixed $id
     * @return void
     * @throws Exception
     */
    public function deleteInvoice($id): void
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);

        if ($invoice->status !== 'draft') {
            throw new Exception('Only draft invoices can be deleted.');
        }

        DB::transaction(function () use ($invoice) {
            // Delete payments first
            $invoice->payments()->delete();
            
            // Delete items
            $invoice->items()->delete();

            // Delete invoice
            $invoice->delete();
        });
    }
}
