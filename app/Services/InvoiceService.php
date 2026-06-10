<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Get paginated, filtered list of invoices.
     */
    public function getAllInvoices(array $filters): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with(['patient:id,name,patient_code', 'appointment:id,date,type'])
            ->select('id', 'invoice_number', 'patient_id', 'appointment_id', 'total', 'amount_paid', 'status', 'created_at', 'due_date');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('invoice_number', 'like', "%{$filters['search']}%");
        }

        $sortColumn = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate(10)->withQueryString();
    }

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
                $qty = $item['quantity'];
                $price = $item['unit_price'];
                $lineTotal = (float) bcmul((string) $qty, (string) $price, 4);
                $subtotal = (float) bcadd((string) $subtotal, (string) $lineTotal, 4);

                $itemsData[] = [
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => round($lineTotal, 2),
                    'discount' => 0,
                ];
            }

            $discount_percent = (float) ($data['discount_percent'] ?? 0);
            $tax_percent = (float) ($data['tax_percent'] ?? 0);

            $discount_amount = (float) bcmul((string) $subtotal, bcdiv((string) $discount_percent, '100', 4), 4);
            $taxable = (float) bcsub((string) $subtotal, (string) $discount_amount, 4);
            $tax_amount = (float) bcmul((string) $taxable, bcdiv((string) $tax_percent, '100', 4), 4);
            $total = (float) bcadd((string) $taxable, (string) $tax_amount, 4);

            $dueDate = $data['due_date'] ?? now()->addDays((int) Setting::get('default_due_days', 0))->toDateString();

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
                'due_date' => $dueDate,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    /**
     * Update an invoice with pessimistic row locking.
     *
     * @param int $invoiceId
     * @param array $data
     * @return Invoice
     * @throws Exception
     */
    public function updateInvoice(int $invoiceId, array $data): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

            if (in_array($invoice->status, ['paid', 'cancelled'])) {
                throw new Exception('Cannot modify a finalized or cancelled invoice.');
            }

            $subtotal = 0;
            $incomingIds = [];
            $newItems = [];

            foreach ($data['items'] as $item) {
                $qty = $item['quantity'];
                $price = $item['unit_price'];
                $lineTotal = (float) bcmul((string) $qty, (string) $price, 4);
                $subtotal = (float) bcadd((string) $subtotal, (string) $lineTotal, 4);

                $itemId = $item['id'] ?? null;

                if ($itemId && $invoice->items()->where('id', $itemId)->exists()) {
                    $invoice->items()->where('id', $itemId)->update([
                        'service_id' => $item['service_id'] ?? null,
                        'description' => $item['description'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total' => round($lineTotal, 2),
                        'discount' => 0,
                    ]);
                    $incomingIds[] = $itemId;
                } else {
                    $newItems[] = [
                        'service_id' => $item['service_id'] ?? null,
                        'description' => $item['description'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total' => round($lineTotal, 2),
                        'discount' => 0,
                    ];
                }
            }

            $discount_percent = (float) ($data['discount_percent'] ?? 0);
            $tax_percent = (float) ($data['tax_percent'] ?? 0);

            $discount_amount = (float) bcmul((string) $subtotal, bcdiv((string) $discount_percent, '100', 4), 4);
            $taxable = (float) bcsub((string) $subtotal, (string) $discount_amount, 4);
            $tax_amount = (float) bcmul((string) $taxable, bcdiv((string) $tax_percent, '100', 4), 4);
            $total = (float) bcadd((string) $taxable, (string) $tax_amount, 4);

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

            // Delete items explicitly removed by the user
            $invoice->items()->whereNotIn('id', $incomingIds)->delete();

            // Create genuinely new items
            foreach ($newItems as $itemData) {
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
            // Pessimistic lock to prevent concurrent payment race conditions
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

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
