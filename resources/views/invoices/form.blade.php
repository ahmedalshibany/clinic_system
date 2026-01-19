@csrf
<div class="row g-3">
    <!-- Header Info -->
    <div class="col-md-6">
        <label class="form-label">Patient <span class="text-danger">*</span></label>
        <select name="patient_id" class="form-select select2" required>
            <option value="">Select Patient</option>
            @foreach($patients as $patient)
                <option value="{{ $patient->id }}" 
                    {{ (old('patient_id', $invoice->patient_id ?? '') == $patient->id) || (isset($selected_patient) && $selected_patient->id == $patient->id) ? 'selected' : '' }}>
                    {{ $patient->name }} ({{ $patient->patient_code }})
                </option>
            @endforeach
        </select>
        @if(isset($appointment))
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
            <div class="form-text text-muted"> Linked to Appointment on {{ $appointment->date->format('M d, Y') }}</div>
        @endif
    </div>

    <div class="col-md-3">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control" value="{{ old('date', isset($invoice) ? $invoice->created_at->format('Y-m-d') : date('Y-m-d')) }}" readonly>
    </div>

    <div class="col-md-3">
        <label class="form-label">Due Date <span class="text-danger">*</span></label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $invoice->due_date ?? date('Y-m-d')) }}" required>
    </div>

    @if(isset($invoice))
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['draft', 'sent', 'paid', 'partial', 'overdue', 'cancelled'] as $status)
                <option value="{{ $status }}" {{ old('status', $invoice->status) == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>
    @else
        <input type="hidden" name="status" value="draft">
    @endif

    <!-- Line Items -->
    <div class="col-12 mt-4">
        <div class="card bg-light border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Invoice Items</h6>
                <button type="button" class="btn btn-sm btn-primary" id="add-item-btn"><i class="fas fa-plus"></i> Add Item</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th width="35%">Service / Description</th>
                                <th width="15%">Price</th>
                                <th width="10%">Qty</th>
                                <th width="15%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-container">
                            <!-- Items will be injected here via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculations -->
    <div class="col-md-6 mt-4">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="4" placeholder="Additional notes for the patient...">{{ old('notes', $invoice->notes ?? '') }}</textarea>
    </div>

    <div class="col-md-5 offset-md-1 mt-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span class="fw-bold" id="subtotal-display">$0.00</span>
                </div>
                
                <div class="row mb-2 align-items-center">
                    <div class="col-7">Discount (%):</div>
                    <div class="col-5">
                        <input type="number" name="discount_percent" id="discount-input" class="form-control form-control-sm text-end" min="0" max="100" step="0.01" value="{{ old('discount_percent', $invoice->discount_percent ?? 0) }}">
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <div class="col-7">Tax (%):</div>
                    <div class="col-5">
                        <input type="number" name="tax_percent" id="tax-input" class="form-control form-control-sm text-end" min="0" max="100" step="0.01" value="{{ old('tax_percent', $invoice->tax_percent ?? 0) }}">
                    </div>
                </div>

                <hr>
                
                <div class="d-flex justify-content-between fs-5">
                    <strong>Total:</strong>
                    <strong class="text-primary" id="total-display">$0.00</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="col-12 mt-4 text-end">
        <a href="{{ route('invoices.index') }}" class="btn btn-light me-2">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Invoice</button>
    </div>
</div>

{{-- Pass services data to JS --}}
<script>
    const services = @json($services);
    const existingItems = @json(isset($invoice) ? $invoice->items : []);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('items-container');
    const addItemBtn = document.getElementById('add-item-btn');
    let itemIndex = 0;

    function createRow(data = null) {
        const index = itemIndex++;
        const row = document.createElement('tr');
        row.className = 'item-row';
        
        let servicesOptions = '<option value="">Select Service (Optional)</option>';
        services.forEach(s => {
            const selected = data && data.service_id == s.id ? 'selected' : '';
            servicesOptions += `<option value="${s.id}" data-price="${s.price}" ${selected}>${s.name} - $${s.price}</option>`;
        });

        row.innerHTML = `
            <td>
                <select name="items[${index}][service_id]" class="form-select form-select-sm service-select mb-1">
                    ${servicesOptions}
                </select>
                <input type="text" name="items[${index}][description]" class="form-control form-control-sm description-input" placeholder="Description" value="${data ? data.description : ''}" required>
            </td>
            <td>
                <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm price-input" step="0.01" min="0" value="${data ? data.unit_price : ''}" required>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]" class="form-control form-control-sm qty-input" min="1" value="${data ? data.quantity : 1}" required>
            </td>
            <td class="text-end align-middle">
                <span class="row-total fw-bold">${data ? (data.quantity * data.unit_price).toFixed(2) : '0.00'}</span>
            </td>
            <td class="align-middle">
                <button type="button" class="btn btn-link text-danger p-0 remove-row"><i class="fas fa-times"></i></button>
            </td>
        `;

        container.appendChild(row);
        
        // Event Listeners for this row
        const serviceSelect = row.querySelector('.service-select');
        const descInput = row.querySelector('.description-input');
        const priceInput = row.querySelector('.price-input');
        const qtyInput = row.querySelector('.qty-input');
        const removeBtn = row.querySelector('.remove-row');

        // Auto-fill price and description from service
        serviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const price = selectedOption.getAttribute('data-price');
                priceInput.value = price;
                if (!descInput.value) {
                    descInput.value = selectedOption.text.split(' - $')[0]; 
                }
                calculateRowTotal(row);
                calculateGrandTotal();
            }
        });

        priceInput.addEventListener('input', () => { calculateRowTotal(row); calculateGrandTotal(); });
        qtyInput.addEventListener('input', () => { calculateRowTotal(row); calculateGrandTotal(); });
        
        removeBtn.addEventListener('click', function() {
            row.remove();
            calculateGrandTotal();
        });
    }

    function calculateRowTotal(row) {
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const total = price * qty;
        row.querySelector('.row-total').textContent = total.toFixed(2);
    }

    function calculateGrandTotal() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            subtotal += price * qty;
        });

        document.getElementById('subtotal-display').textContent = '$' + subtotal.toFixed(2);

        const discountPercent = parseFloat(document.getElementById('discount-input').value) || 0;
        const taxPercent = parseFloat(document.getElementById('tax-input').value) || 0;

        const discountAmount = subtotal * (discountPercent / 100);
        const taxable = subtotal - discountAmount;
        const taxAmount = taxable * (taxPercent / 100);
        const total = taxable + taxAmount;

        document.getElementById('total-display').textContent = '$' + total.toFixed(2);
    }

    // Global listeners for tax/discount
    document.getElementById('discount-input').addEventListener('input', calculateGrandTotal);
    document.getElementById('tax-input').addEventListener('input', calculateGrandTotal);
    addItemBtn.addEventListener('click', () => createRow());

    // Initialize
    if (existingItems.length > 0) {
        existingItems.forEach(item => createRow(item));
    } else {
        createRow(); // Add one empty row
    }
    
    // Initial calculation
    calculateGrandTotal();
});
</script>
