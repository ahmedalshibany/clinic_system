<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Report - {{ $medicalRecord->patient->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background: #fff;
            font-size: 13px;
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        @media print {
            .no-print { display: none; }
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            box-sizing: border-box;
        }

        .header {
            border-bottom: 2px solid #2F4156;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .clinic-info h1 {
            color: #2F4156;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .clinic-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        .report-meta {
            text-align: right;
            font-size: 11px;
            color: #888;
        }

        .section-title {
            background: #2F4156;
            color: white;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0 10px;
            border-radius: 3px;
        }

        .patient-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #eee;
            margin-bottom: 15px;
        }
        .info-item label {
            display: block;
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
        }
        .info-item span {
            display: block;
            font-weight: 600;
            font-size: 13px;
        }

        .content-block {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .content-block:last-child {
            border-bottom: none;
        }
        .content-block label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }
        .content-block p {
            margin: 0;
            line-height: 1.6;
        }

        .vitals-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        .vital-item {
            text-align: center;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        .vital-item label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            display: block;
        }
        .vital-item span {
            font-size: 16px;
            font-weight: 700;
            color: #2F4156;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #888;
        }

        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            font-family: inherit;
        }
        .btn-primary { background: #2F4156; color: white; }
        .btn-secondary { background: #e2e8f0; color: #333; }
    </style>
</head>
<body>
    <div class="print-controls no-print">
        <a href="{{ smartBack('medical-records.index') }}" class="btn btn-secondary">{{ __('messages.back') }}</a>
        <button onclick="window.print()" class="btn btn-primary">{{ __('messages.printReport') }}</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="clinic-info">
                <h1>{{ config('app.name', 'Clinic Name') }}</h1>
                <p>123 Medical Center Drive, Health City</p>
                <p>Phone: (555) 123-4567 | Email: info@clinic.com</p>
            </div>
            <div class="report-meta">
                <div><strong>Report ID:</strong> #{{ $medicalRecord->id }}</div>
                <div><strong>Date:</strong> {{ $medicalRecord->created_at->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="patient-info-grid">
            <div class="info-item">
                <label>Patient Name</label>
                <span>{{ $medicalRecord->patient->name }}</span>
            </div>
            <div class="info-item">
                <label>Visit Date</label>
                <span>{{ $medicalRecord->visit_date->format('M d, Y') }}</span>
            </div>
            <div class="info-item">
                <label>Age / Gender</label>
                <span>{{ $medicalRecord->patient->age }} Yrs / {{ ucfirst($medicalRecord->patient->gender) }}</span>
            </div>
            <div class="info-item">
                <label>Patient ID</label>
                <span>{{ $medicalRecord->patient->patient_code }}</span>
            </div>
            <div class="info-item" style="grid-column: span 2;">
                <label>Doctor</label>
                <span>Dr. {{ $medicalRecord->doctor->name }} <span style="font-weight: normal; font-size: 11px; margin-left: 5px;">({{ $medicalRecord->doctor->specialty }})</span></span>
            </div>
        </div>

        @if($medicalRecord->vital_signs)
        <div class="section-title">Vital Signs</div>
        <div class="vitals-grid">
            @php $vitals = $medicalRecord->vital_signs; @endphp
            <div class="vital-item">
                <label>BP</label>
                <span>{{ $vitals['blood_pressure'] ?? '—' }}</span>
            </div>
            <div class="vital-item">
                <label>HR</label>
                <span>{{ $vitals['heart_rate'] ?? '—' }} <small>bpm</small></span>
            </div>
            <div class="vital-item">
                <label>Temp</label>
                <span>{{ $vitals['temperature'] ?? '—' }} <small>°C</small></span>
            </div>
            <div class="vital-item">
                <label>RR</label>
                <span>{{ $vitals['respiratory_rate'] ?? '—' }} <small>/min</small></span>
            </div>
            <div class="vital-item">
                <label>SpO₂</label>
                <span>{{ $vitals['spo2'] ?? '—' }} <small>%</small></span>
            </div>
            <div class="vital-item">
                <label>Weight</label>
                <span>{{ $vitals['weight'] ?? '—' }} <small>kg</small></span>
            </div>
            <div class="vital-item">
                <label>Height</label>
                <span>{{ $vitals['height'] ?? '—' }} <small>cm</small></span>
            </div>
            <div class="vital-item">
                <label>BMI</label>
                <span>{{ $vitals['bmi'] ?? '—' }}</span>
            </div>
        </div>
        @endif

        <div class="section-title">Clinical Notes</div>

        <div class="content-block">
            <label>Chief Complaint</label>
            <p>{{ $medicalRecord->chief_complaint ?? 'N/A' }}</p>
        </div>

        @if($medicalRecord->history_of_illness)
        <div class="content-block">
            <label>History of Present Illness</label>
            <p>{{ $medicalRecord->history_of_illness }}</p>
        </div>
        @endif

        @if($medicalRecord->diagnosis)
        <div class="content-block">
            <label>Diagnosis</label>
            <p>{{ $medicalRecord->diagnosis }}</p>
            @if($medicalRecord->diagnosis_code)
            <p style="font-size: 11px; color: #888;">Code: {{ $medicalRecord->diagnosis_code }}</p>
            @endif
        </div>
        @endif

        @if($medicalRecord->treatment_plan)
        <div class="content-block">
            <label>Treatment Plan</label>
            <p>{!! nl2br(e($medicalRecord->treatment_plan)) !!}</p>
        </div>
        @endif

        @if($medicalRecord->follow_up_date)
        <div class="content-block">
            <label>Follow-Up Date</label>
            <p>{{ $medicalRecord->follow_up_date->format('M d, Y') }}</p>
        </div>
        @endif

        @if($medicalRecord->notes)
        <div class="content-block">
            <label>Additional Notes</label>
            <p>{{ $medicalRecord->notes }}</p>
        </div>
        @endif

        <div class="footer">
            <div>Generated by {{ config('app.name') }} on {{ now()->format('M d, Y H:i') }}</div>
            <div>
                <span>Dr. {{ $medicalRecord->doctor->name }}</span>
            </div>
        </div>
    </div>
</body>
</html>