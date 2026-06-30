<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Jobs\DispatchNotification;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
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
                'status' => $data['status'] ?? InvoiceStatus::Cancelled->value,
                'due_date' => $dueDate,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    public function updateInvoice(int $invoiceId, array $data): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $data) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoiceId);

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

            $invoice->items()->whereNotIn('id', $incomingIds)->delete();

            foreach ($newItems as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    public function addPayment($id, array $data, ?int $receiverId = null): Payment
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);

        return DB::transaction(function () use ($invoice, $data, $receiverId) {
            $lockedInvoice = Invoice::where('id', $invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedInvoice->status !== InvoiceStatus::Cancelled->value) {
                throw new Exception(__('messages.invoicePaymentNotAllowed'));
            }

            if (bccomp((string) $data['amount'], (string) $lockedInvoice->total, 2) !== 0) {
                throw new Exception(__('messages.invoiceInvalidPaymentAmount'));
            }

            $payment = Payment::create([
                'invoice_id' => $lockedInvoice->id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'received_by' => $receiverId,
                'notes' => $data['notes'] ?? null,
            ]);

            $lockedInvoice->amount_paid = $lockedInvoice->total;
            $lockedInvoice->status = InvoiceStatus::Paid->value;
            $lockedInvoice->save();

            DispatchNotification::dispatch(
                'admins',
                null,
                'payment',
                __('messages.notification.title_payment_received'),
                __('messages.notification.message_payment_received', [
                    'amount' => $data['amount'],
                    'invoice_number' => $lockedInvoice->invoice_number,
                ]),
                [
                    'invoice_id' => $lockedInvoice->id,
                    'amount' => $data['amount'],
                    'invoice_number' => $lockedInvoice->invoice_number,
                    'title_key' => 'notification.title_payment_received',
                    'message_key' => 'notification.message_payment_received',
                ],
                route('invoices.show', $lockedInvoice->id)
            );

            return $payment;
        });
    }

    public function deleteInvoice($id): void
    {
        $invoice = $id instanceof Invoice ? $id : Invoice::findOrFail($id);

        DB::transaction(function () use ($invoice) {
            $invoice->payments()->delete();
            $invoice->items()->delete();
            $invoice->delete();
        });
    }
}
