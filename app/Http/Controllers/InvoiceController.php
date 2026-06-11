<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\Invoice\UpdateInvoiceRequest;
use App\Http\Requests\Invoice\RecordPaymentRequest;

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
        $this->authorize('viewAny', Invoice::class);
        $invoices = $this->invoiceService->getAllInvoices($request->all());
        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Invoice::class);
        $patients = Patient::select('id', 'name', 'patient_code')->get();
        $services = Service::active()->get();
        $selected_patient = $request->patient_id ? Patient::find($request->patient_id) : null;
        return view('invoices.create', compact('patients', 'services', 'selected_patient'));
    }

    public function createFromAppointment(Appointment $appointment)
    {
        $this->authorize('create', Invoice::class);
        if ($appointment->invoice) {
            return redirect()->route('invoices.show', $appointment->invoice)
                ->with('warning', __('messages.invoiceAlreadyExists'));
        }
        $patients = Patient::select('id', 'name', 'patient_code')->get();
        $services = Service::active()->get();
        $selected_patient = $appointment->patient;
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

    public function store(StoreInvoiceRequest $request)
    {
        $this->authorize('create', Invoice::class);
        $validated = $request->validated();
        try {
            $this->invoiceService->createInvoice($validated, Auth::id());
            return redirect()->route('invoices.index')->with('success', __('messages.invoiceCreated'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error creating invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['items', 'patient', 'payments', 'creator']);

        if ($invoice->status === 'draft') {
            session()->flash('info', __('messages.invoiceDraftInfo'));
        }

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        if ($invoice->status == 'paid' || $invoice->status == 'cancelled') {
             return redirect()->route('invoices.show', $invoice)->with('error', __('messages.invoiceEditLocked'));
        }
        $patients = Patient::select('id', 'name', 'patient_code')->get();
        $services = Service::active()->get();
        return view('invoices.edit', compact('invoice', 'patients', 'services'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        $validated = $request->validated();
        try {
            $this->invoiceService->updateInvoice($invoice->id, $validated);
            return redirect()->route('invoices.show', $invoice)->with('success', __('messages.invoiceUpdated'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        try {
            $this->invoiceService->deleteInvoice($invoice);
            return redirect()->route('invoices.index')->with('success', __('messages.invoiceDeleted'));
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting invoice: ' . $e->getMessage());
        }
    }

    public function recordPayment(RecordPaymentRequest $request, Invoice $invoice)
    {
        $this->authorize('recordPayment', $invoice);
        $validated = $request->validated();
        try {
            $this->invoiceService->addPayment($invoice, $validated, Auth::id());
            return back()->with('success', __('messages.paymentRecorded'));
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }
    }

    public function send(Invoice $invoice)
    {
        $this->authorize('update', $invoice);
        if ($invoice->status == 'draft') {
            if ($invoice->items->count() === 0) {
                session()->flash('warning', __('messages.invoiceZeroItems'));
            }
            $invoice->update(['status' => 'sent']);
        }
        return back()->with('success', __('messages.invoiceMarkedSent'));
    }

    public function print(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load(['items', 'patient', 'creator']);
        return view('invoices.print', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        return $this->print($invoice);
    }
}
