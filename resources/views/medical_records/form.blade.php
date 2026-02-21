@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endsection

{{-- Patient Info & Vitals --}}

<div class="row g-4">
    {{-- Patient Information --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header   py-3">
                <h5 class="card-title mb-0"><i class="fas fa-user-circle me-2 text-primary"></i>Patient Info</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @if($patient->photo)
                        <img src="{{ Storage::url($patient->photo) }}" class="rounded-circle mb-2" width="80" height="80" alt="{{ $patient->name }}">
                    @else
                        <div class="rounded-circle   d-inline-flex align-items-center justify-content-center text-primary fw-bold fs-3 mb-2" style="width: 80px; height: 80px;">
                            {{ substr($patient->name, 0, 1) }}
                        </div>
                    @endif
                    <h5 class="fw-bold mb-0">{{ $patient->name }}</h5>
                    <div class="text-muted small">{{ $patient->patient_code }}</div>
                </div>
                
                <hr>

                <div class="mb-2">
                    <label class="small text-muted d-block">Age / Gender</label>
                    <span class="fw-medium">{{ $patient->age }} Years / {{ ucfirst($patient->gender) }}</span>
                </div>
                <div class="mb-2">
                    <label class="small text-muted d-block">Blood Type</label>
                    <span class="badge bg-danger">{{ $patient->blood_type ?? 'N/A' }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Allergies</label>
                    @if($patient->allergies)
                        <div class="alert alert-danger py-2 px-3 small mb-0 mt-1">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ $patient->allergies }}
                        </div>
                    @else
                        <span class="text-success small"><i class="fas fa-check-circle me-1"></i> No known allergies</span>
                    @endif
                </div>
                
                @if(isset($appointment))
                <div class="alert alert-info py-2 small mb-0">
                    <i class="fas fa-calendar-alt me-1"></i> Visit Reason:<br>
                    <strong>{{ $appointment->type }}</strong> - {{ $appointment->reason ?? 'No detailed reason' }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Vital Signs --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header   py-3">
                <h5 class="card-title mb-0"><i class="fas fa-heartbeat me-2 text-danger"></i>Vital Signs</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Systolic (mmHg)</label>
                        <input type="number" name="vital_signs[bp_systolic]" class="form-control" placeholder="120" 
                               value="{{ old('vital_signs.bp_systolic', $record->vital_signs['bp_systolic'] ?? '') }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Diastolic (mmHg)</label>
                        <input type="number" name="vital_signs[bp_diastolic]" class="form-control" placeholder="80"
                               value="{{ old('vital_signs.bp_diastolic', $record->vital_signs['bp_diastolic'] ?? '') }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Pulse (bpm)</label>
                        <input type="number" name="vital_signs[pulse]" class="form-control" placeholder="72"
                               value="{{ old('vital_signs.pulse', $record->vital_signs['pulse'] ?? '') }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Temp (°C)</label>
                        <input type="number" step="0.1" name="vital_signs[temp]" class="form-control" placeholder="37.0"
                               value="{{ old('vital_signs.temp', $record->vital_signs['temp'] ?? '') }}">
                    </div>
                    
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Weight (kg)</label>
                        <input type="number" step="0.1" id="weight" name="vital_signs[weight]" class="form-control" placeholder="70"
                               value="{{ old('vital_signs.weight', $record->vital_signs['weight'] ?? '') }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Height (cm)</label>
                        <input type="number" id="height" name="vital_signs[height]" class="form-control" placeholder="175"
                               value="{{ old('vital_signs.height', $record->vital_signs['height'] ?? '') }}">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">BMI</label>
                        <input type="text" id="bmi" class="form-control  " readonly tabindex="-1" placeholder="-">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label small text-muted">Oxygen Sat (%)</label>
                        <input type="number" name="vital_signs[oxygen]" class="form-control" placeholder="98"
                               value="{{ old('vital_signs.oxygen', $record->vital_signs['oxygen'] ?? '') }}">
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="form-label fw-bold">Visit Date</label>
                    <input type="date" name="visit_date" class="form-control w-auto" 
                           value="{{ old('visit_date', isset($record) ? $record->visit_date->format('Y-m-d') : now()->format('Y-m-d')) }}">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Clinical Notes --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header   py-3">
        <h5 class="card-title mb-0"><i class="fas fa-stethoscope me-2 text-primary"></i>Clinical Notes</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-12">
                <label class="form-label fw-bold">Chief Complaint <span class="text-danger">*</span></label>
                <textarea name="chief_complaint" class="form-control" rows="2" placeholder="Main reason for visit..." required>{{ old('chief_complaint', $record->chief_complaint ?? '') }}</textarea>
            </div>
            
            <div class="col-md-6">
                <label class="form-label fw-bold">History of Present Illness</label>
                <textarea name="history_of_illness" class="form-control" rows="4" placeholder="Details about the condition...">{{ old('history_of_illness', $record->history_of_illness ?? '') }}</textarea>
            </div>
            
            <div class="col-md-6">
                <label class="form-label fw-bold">Physical Examination</label>
                <textarea name="physical_examination" class="form-control" rows="4" placeholder="Observations from excessive...">{{ old('physical_examination', $record->physical_examination ?? '') }}</textarea>
            </div>

            <div class="col-12">
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Diagnosis <span class="text-danger">*</span></label>
                        <input type="text" name="diagnosis" class="form-control" placeholder="Primary diagnosis..." required value="{{ old('diagnosis', $record->diagnosis ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">ICD-10 Code</label>
                        <input type="text" name="diagnosis_code" class="form-control" placeholder="e.g. J01.90" value="{{ old('diagnosis_code', $record->diagnosis_code ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">Treatment Plan</label>
                <textarea name="treatment_plan" class="form-control" rows="3" placeholder="Plan required...">{{ old('treatment_plan', $record->treatment_plan ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- Prescription --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header   py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-pills me-2 text-success"></i>Prescription</h5>
        <button type="button" class="btn btn-sm btn-outline-success" id="add-medication">
            <i class="fas fa-plus me-1"></i> Add Medication
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="prescription-table">
                <thead class="">
                    <tr>
                        <th style="width: 25%">Medication</th>
                        <th style="width: 15%">Dosage</th>
                        <th style="width: 15%">Frequency</th>
                        <th style="width: 15%">Duration</th>
                        <th style="width: 25%">Instructions</th>
                        <th style="width: 5%"></th>
                    </tr>
                </thead>
                <tbody id="prescription-list">
                    @if(isset($record) && $record->prescription && $record->prescription->items->count() > 0)
                        @foreach($record->prescription->items as $index => $item)
                        <tr class="prescription-row">
                            <td>
                                <select name="prescription_items[{{$index}}][medication_name]" class="form-select form-select-sm medicine-select" required data-index="{{$index}}">
                                    <option value="{{ $item->medication_name }}" selected>{{ $item->medication_name }}</option>
                                </select>
                            </td>
                            <td><input type="text" name="prescription_items[{{$index}}][dosage]" class="form-control form-control-sm" placeholder="e.g. 500mg" required value="{{ $item->dosage }}"></td>
                            <td><input type="text" name="prescription_items[{{$index}}][frequency]" class="form-control form-control-sm" placeholder="e.g. 1-0-1" required value="{{ $item->frequency }}"></td>
                            <td><input type="text" name="prescription_items[{{$index}}][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days" required value="{{ $item->duration }}"></td>
                            <td><input type="text" name="prescription_items[{{$index}}][instructions]" class="form-control form-control-sm" placeholder="Note..." value="{{ $item->instructions }}"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <div class="p-3 text-center text-muted small" id="empty-prescription-msg" style="{{ (isset($record) && $record->prescription && $record->prescription->items->count() > 0) ? 'display: none;' : '' }}">
            No medications added yet. Click "Add Medication" to prescribe.
        </div>
    </div>
</div>

{{-- Follow Up & Private Notes --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header   py-3">
        <h5 class="card-title mb-0"><i class="fas fa-calendar-check me-2 text-warning"></i>Follow-up & Notes</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">Follow-up Date</label>
                <input type="date" name="follow_up_date" class="form-control" value="{{ old('follow_up_date', isset($record->follow_up_date) ? $record->follow_up_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">Private Notes <span class="badge bg-warning text-dark ms-2">Visible only to doctors</span></label>
                <textarea name="notes" class="form-control  " rows="2" placeholder="Internal notes...">{{ old('notes', $record->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- BMI Calculation ---
        const weightInput = document.getElementById('weight');
        const heightInput = document.getElementById('height');
        const bmiInput = document.getElementById('bmi');

        function calculateBMI() {
            const weight = parseFloat(weightInput.value);
            const height = parseFloat(heightInput.value) / 100; // convert cm to m

            if (weight > 0 && height > 0) {
                const bmi = (weight / (height * height)).toFixed(1);
                bmiInput.value = bmi;
                
                // Optional: Color code BMI
                if(bmi < 18.5) bmiInput.style.color = '#3b82f6'; // Underweight
                else if(bmi < 25) bmiInput.style.color = '#10b981'; // Normal
                else if(bmi < 30) bmiInput.style.color = '#f59e0b'; // Overweight
                else bmiInput.style.color = '#ef4444'; // Obese
            } else {
                bmiInput.value = '';
            }
        }

        weightInput.addEventListener('input', calculateBMI);
        heightInput.addEventListener('input', calculateBMI);
        calculateBMI(); // Run on load

        // --- Dynamic Prescription Rows ---
        const addBtn = document.getElementById('add-medication');
        const list = document.getElementById('prescription-list');
        const emptyMsg = document.getElementById('empty-prescription-msg');
        let index = {{ isset($record) && $record->prescription ? $record->prescription->items->count() : 0 }};

        });

        // --- Select2 Logic for Medicines ---
        function initMedicineSelect(element) {
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%',
                ajax: {
                    url: '{{ route("api.medicines.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data.results };
                    },
                    cache: true
                },
                placeholder: 'Search Medicine...',
                minimumInputLength: 1,
                tags: true // Allow typing custom meds if not in DB
            }).on('select2:select', function (e) {
                var data = e.params.data;
                // Auto-fill logic if available
                if (data.strength || data.form) {
                    var row = $(this).closest('tr');
                    var dosage = (data.strength ? data.strength : '') + (data.form ? ' ' + data.form : '');
                    row.find('input[name*="[dosage]"]').val(dosage);
                }
            });
        }

        // Init existing rows
        $('.medicine-select').each(function() {
            initMedicineSelect(this);
        });

        // Update add button to init select2
        addBtn.addEventListener('click', function() {
            const row = document.createElement('tr');
            row.className = 'prescription-row animate__animated animate__fadeIn';
            row.innerHTML = `
                <td>
                    <select name="prescription_items[${index}][medication_name]" class="form-select form-select-sm medicine-select" required>
                        <option value="">Search...</option>
                    </select>
                </td>
                <td><input type="text" name="prescription_items[${index}][dosage]" class="form-control form-control-sm" placeholder="e.g. 500mg" required></td>
                <td><input type="text" name="prescription_items[${index}][frequency]" class="form-control form-control-sm" placeholder="e.g. 1-0-1" required></td>
                <td><input type="text" name="prescription_items[${index}][duration]" class="form-control form-control-sm" placeholder="e.g. 5 days" required></td>
                <td><input type="text" name="prescription_items[${index}][instructions]" class="form-control form-control-sm" placeholder="Note..."></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fas fa-times"></i></button></td>
            `;
            list.appendChild(row);
            
            // Init Select2 on new element
            initMedicineSelect($(row).find('.medicine-select'));

            emptyMsg.style.display = 'none';
            index++;
        });
    });
</script>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
