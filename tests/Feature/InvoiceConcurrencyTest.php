<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Patient $patient;
    protected InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->patient = Patient::factory()->create();
        $this->service = new InvoiceService();
    }

    public function test_bcmath_precision_in_create_invoice(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Item A', 'quantity' => 3, 'unit_price' => 4.99],
                ['description' => 'Item B', 'quantity' => 2, 'unit_price' => 12.50],
                ['description' => 'Item C', 'quantity' => 1, 'unit_price' => 99.99],
            ],
            'discount_percent' => 10,
            'tax_percent' => 5,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $bcSubtotal = round((float) bcadd(bcadd('14.97', '25.00', 4), '99.99', 4), 2);
        $bcDiscountAmount = round((float) bcdiv(bcmul((string) $bcSubtotal, '10', 4), '100', 4), 2);
        $bcTaxable = round((float) bcsub((string) $bcSubtotal, (string) $bcDiscountAmount, 4), 2);
        $bcTaxAmount = round((float) bcdiv(bcmul((string) $bcTaxable, '5', 4), '100', 4), 2);
        $bcTotal = round((float) bcadd((string) $bcTaxable, (string) $bcTaxAmount, 4), 2);

        $this->assertEquals($bcSubtotal, $invoice->subtotal);
        $this->assertEquals($bcDiscountAmount, $invoice->discount_amount);
        $this->assertEquals($bcTotal, $invoice->total);

        $items = $invoice->items;
        $this->assertCount(3, $items);
        $this->assertEqualsWithDelta(round(3 * 4.99, 2), (float) $items[0]->total, 0.001);
        $this->assertEqualsWithDelta(round(2 * 12.50, 2), (float) $items[1]->total, 0.001);
        $this->assertEqualsWithDelta(round(1 * 99.99, 2), (float) $items[2]->total, 0.001);
    }

    public function test_bcmath_no_float_drift(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Precision test', 'quantity' => 3, 'unit_price' => 0.01],
                ['description' => 'Precision test 2', 'quantity' => 7, 'unit_price' => 0.03],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->assertEquals(0.24, $invoice->subtotal);
        $this->assertEquals(0.24, $invoice->total);
        $this->assertEqualsWithDelta(0.03, (float) $invoice->items[0]->total, 0.001);
        $this->assertEqualsWithDelta(0.21, (float) $invoice->items[1]->total, 0.001);
    }

    public function test_full_payment_marks_invoice_paid(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Consultation', 'quantity' => 1, 'unit_price' => 150.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->assertEquals(150.00, $invoice->total);
        $this->assertEquals(0.0, $invoice->amount_paid);
        $this->assertSame('cancelled', $invoice->status);

        $this->service->addPayment($invoice, [
            'amount' => 150.00,
            'payment_date' => '2026-06-06',
            'payment_method' => 'cash',
        ], $this->admin->id);

        $invoice->refresh();
        $this->assertEquals(150.00, $invoice->amount_paid);
        $this->assertSame('paid', $invoice->status);
    }

    public function test_partial_payment_rejected(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('full invoice total');
        $this->service->addPayment($invoice, [
            'amount' => 50.00,
            'payment_date' => '2026-06-06',
            'payment_method' => 'cash',
        ], $this->admin->id);
    }

    public function test_overpayment_rejected(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('full invoice total');
        $this->service->addPayment($invoice, [
            'amount' => 101.00,
            'payment_date' => '2026-06-06',
            'payment_method' => 'cash',
        ], $this->admin->id);
    }

    public function test_zero_payment_rejected(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('full invoice total');
        $this->service->addPayment($invoice, [
            'amount' => 0,
            'payment_date' => '2026-06-06',
            'payment_method' => 'cash',
        ], $this->admin->id);
    }

    public function test_negative_payment_rejected(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 100.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('full invoice total');
        $this->service->addPayment($invoice, [
            'amount' => -50.00,
            'payment_date' => '2026-06-06',
            'payment_method' => 'cash',
        ], $this->admin->id);
    }

    public function test_payment_on_paid_invoice_rejected(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Test', 'quantity' => 1, 'unit_price' => 500.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $this->assertEquals(500.00, $invoice->total);
        $this->assertEquals(0.0, $invoice->amount_paid);

        $this->service->addPayment($invoice, [
            'amount' => 500.00,
            'payment_date' => '2026-06-06',
            'payment_method' => 'bank_transfer',
        ], $this->admin->id);

        $invoice->refresh();
        $this->assertEquals(500.00, $invoice->amount_paid);
        $this->assertSame('paid', $invoice->status);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('cancelled invoices');
        $this->service->addPayment($invoice, [
            'amount' => 500.00,
            'payment_date' => '2026-06-06',
            'payment_method' => 'cash',
        ], $this->admin->id);
    }

    public function test_bcmath_precision_in_update_invoice(): void
    {
        $invoice = $this->service->createInvoice([
            'patient_id' => $this->patient->id,
            'items' => [
                ['description' => 'Original', 'quantity' => 1, 'unit_price' => 50.00],
            ],
            'discount_percent' => 0,
            'tax_percent' => 0,
            'due_date' => '2026-07-01',
            'status' => 'cancelled',
        ], $this->admin->id);

        $updated = $this->service->updateInvoice($invoice->id, [
            'items' => [
                ['description' => 'Updated Item', 'quantity' => 3, 'unit_price' => 33.33],
            ],
            'discount_percent' => 5,
            'tax_percent' => 8,
            'due_date' => '2026-08-01',
            'status' => 'cancelled',
            'notes' => 'Updated',
        ]);

        $bcSubtotal = round((float) bcmul('3', '33.33', 4), 2);
        $bcDiscountAmount = round((float) bcdiv(bcmul((string) $bcSubtotal, '5', 4), '100', 4), 2);
        $bcTaxable = round((float) bcsub((string) $bcSubtotal, (string) $bcDiscountAmount, 4), 2);
        $bcTaxAmount = round((float) bcdiv(bcmul((string) $bcTaxable, '8', 4), '100', 4), 2);
        $bcTotal = round((float) bcadd((string) $bcTaxable, (string) $bcTaxAmount, 4), 2);

        $this->assertEquals($bcSubtotal, $updated->subtotal);
        $this->assertEquals($bcDiscountAmount, $updated->discount_amount);
        $this->assertEquals($bcTotal, $updated->total);
        $this->assertSame(1, $updated->items()->count());
        $this->assertEqualsWithDelta(round(3 * 33.33, 2), (float) $updated->items()->first()->total, 0.001);
    }
}
