<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::query()
            ->with(['patient:id,name,patient_code', 'appointment:id,date,type'])
            ->select('id', 'invoice_number', 'patient_id', 'appointment_id', 'total', 'amount_paid', 'balance', 'status', 'created_at', 'due_date');

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
        
        // Default item for appointment
        $prefilled_items = [
            [
                'service_id' => null,
                'description' => 'Consultation - ' . $appointment->type,
                'quantity' => 1,
                'unit_price' => $appointment->fee > 0 ? $appointment->fee : 0,
            ]
        ];

        return view('invoices.create', compact('patients', 'services', 'selected_patient', 'appointment', 'prefilled_items'));
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
        
        try {
            $this->invoiceService->createInvoice($validated, Auth::id());
            return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating invoice: ' . $e->getMessage())->withInput();
        }
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

        try {
            $this->invoiceService->updateInvoice($invoice, $validated);
            return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating invoice: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $this->invoiceService->deleteInvoice($invoice);
            return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting invoice: ' . $e->getMessage());
        }
    }

    /**
     * Record a payment for the invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,bank_transfer,insurance,other',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->invoiceService->addPayment($invoice, $validated, Auth::id());
            return back()->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
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
        return $this->print($invoice);
    }
}
