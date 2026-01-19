<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::query()->with(['patient', 'appointment']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Search by invoice number
        if ($request->filled('search')) {
             $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        $patients = Patient::select('id', 'name', 'patient_code')->get();
        $services = Service::active()->get();
        // pre-select patient if provided
        $selected_patient = $request->patient_id ? Patient::find($request->patient_id) : null;

        return view('invoices.create', compact('patients', 'services', 'selected_patient'));
    }

    /**
     * Create invoice from appointment.
     */
    public function createFromAppointment(Appointment $appointment)
    {
        // Check if invoice already exists
        if ($appointment->invoice) {
            return redirect()->route('invoices.show', $appointment->invoice);
        }

        $patients = Patient::select('id', 'name', 'patient_code')->get();
        $services = Service::active()->get();
        $selected_patient = $appointment->patient;
        
        // Try to identify consultation service based on fee or default
        // This is a placeholder logic, ideally we map appointment type to service
        $default_service = null;
        if ($appointment->fee > 0) {
            // Logic to find a matching service or pass fee as custom item
        }

        return view('invoices.create', compact('patients', 'services', 'selected_patient', 'appointment'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'due_date' => 'required|date',
            'status' => 'required|in:draft,sent,paid,partial,overdue,cancelled',
            'notes' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);
        
        DB::transaction(function () use ($validated, $request) {
            // Calculate totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
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

            $discount_percent = $request->discount_percent ?? 0;
            $tax_percent = $request->tax_percent ?? 0;
            
            $discount_amount = $subtotal * ($discount_percent / 100);
            $taxable = $subtotal - $discount_amount;
            $tax_amount = $taxable * ($tax_percent / 100);
            $total = $taxable + $tax_amount;

            $invoice = Invoice::create([
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'],
                'created_by' => Auth::id(),
                'subtotal' => $subtotal,
                'discount_percent' => $discount_percent,
                'discount_amount' => $discount_amount,
                'tax_percent' => $tax_percent,
                'tax_amount' => $tax_amount,
                'total' => $total,
                'amount_paid' => 0,
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'],
            ]);

            foreach ($itemsData as $data) {
                $invoice->items()->create($data);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'patient', 'payments', 'creator']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status == 'paid' || $invoice->status == 'cancelled') {
             return redirect()->route('invoices.show', $invoice)->with('error', 'Cannot edit paid or cancelled invoices.');
        }

        $patients = Patient::select('id', 'name', 'patient_code')->get();
        $services = Service::active()->get();
        
        return view('invoices.edit', compact('invoice', 'patients', 'services'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status == 'paid' || $invoice->status == 'cancelled') {
             return back()->with('error', 'Cannot edit paid or cancelled invoices.');
        }

        $validated = $request->validate([
            'due_date' => 'required|date',
            'status' => 'required|in:draft,sent,paid,partial,overdue,cancelled',
            'notes' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);

        DB::transaction(function () use ($validated, $request, $invoice) {
             // Calculate totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
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

            $discount_percent = $request->discount_percent ?? 0;
            $tax_percent = $request->tax_percent ?? 0;
            
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
                // Do not update amount_paid here, only via payments
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'],
            ]);

            // Sync items: delete all and recreate (easiest for now)
            $invoice->items()->delete();
            foreach ($itemsData as $data) {
                $invoice->items()->create($data);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }

        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Record a payment for the invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($invoice->total - $invoice->amount_paid),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,insurance,other',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $invoice) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'received_by' => Auth::id(),
                'notes' => $validated['notes'],
            ]);

            // Update invoice status and amount paid
            $invoice->amount_paid += $validated['amount'];
            
            if ($invoice->amount_paid >= $invoice->total) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            $invoice->save();
        });

        // Notify Admins
        try {
            app(\App\Services\NotificationService::class)->notifyAdmins(
                'payment', 
                'Payment Received', 
                "Received {$validated['amount']} for Invoice #{$invoice->invoice_number}",
                ['invoice_id' => $invoice->id, 'amount' => $validated['amount']],
                route('invoices.show', $invoice->id)
            );
        } catch (\Exception $e) {}

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Mark invoice as sent.
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->status == 'draft') {
            $invoice->update(['status' => 'sent']);
        }
        return back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Print View.
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['items', 'patient', 'creator']);
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Download PDF.
     */
    public function downloadPdf(Invoice $invoice)
    {
        // For now, reuse print view or implement dompdf
        // Simulating PDF download by showing print view with a query param? 
        // Or just redirect to print for this task as we don't have dompdf installed yet.
        return $this->print($invoice);
    }
}
