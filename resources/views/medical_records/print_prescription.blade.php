<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $medicalRecord->patient->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background: #fff;
            font-size: 14px;
        }
        
        @page {
            size: A5;
            margin: 0;
        }

        @media print {
            body {
                width: 148mm;
                height: 210mm;
            }
            .no-print {
                display: none;
            }
        }

        .container {
            width: 100%;
            max-width: 148mm;
            margin: 0 auto;
            padding: 15mm;
            box-sizing: border-box;
            background: white;
            min-height: 200mm;
            position: relative;
        }

        /* Header */
        .header {
            border-bottom: 2px solid #2F4156;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .clinic-info h1 {
            color: #2F4156;
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .clinic-info p {
            margin: 2px 0;
            font-size: 12px;
            color: #666;
        }
        .logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        /* Content */
        .section-title {
            color: #2F4156;
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            border: 1px solid #eee;
            background: #f8f9fa;
            padding: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            background: #fcfcfc;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #eee;
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

        /* Rx Symbol */
        .rx-symbol {
            font-family: 'Times New Roman', serif;
            font-size: 40px;
            font-weight: bold;
            font-style: italic;
            margin: 10px 0;
            color: #2F4156;
        }

        /* Table */
        .medication-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .medication-table th {
            text-align: left;
            background: #2F4156;
            color: white;
            padding: 8px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .medication-table td {
            border-bottom: 1px solid #eee;
            padding: 10px 8px;
            vertical-align: top;
        }
        .medication-name {
            font-weight: 700;
            color: #000;
            font-size: 14px;
        }
        .medication-detail {
            font-size: 12px;
            color: #555;
            margin-top: 2px;
        }
        .instructions {
            font-style: italic;
            color: #666;
            font-size: 12px;
            margin-top: 4px;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            width: 150px;
            border-bottom: 1px dashed #333;
            margin-bottom: 5px;
            height: 30px;
        }
        .disclaimer {
            font-size: 10px;
            color: #999;
            text-align: center;
            margin-top: 10px;
            width: 100%;
            position: absolute;
            bottom: -30px;
        }

        /* Print Controls */
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
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
        <button onclick="window.print()" class="btn btn-primary">Print Prescription</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="clinic-info">
                <h1>{{ config('app.name', 'Clinic Name') }}</h1>
                <p>123 Medical Center Drive, Health City</p>
                <p>Phone: (555) 123-4567 | Email: info@clinic.com</p>
            </div>
            <!-- Placeholder Logo -->
            <img src="https://ui-avatars.com/api/?name=Clinic+System&background=2F4156&color=fff&size=128" alt="Logo" class="logo">
        </div>

        <div class="section-title">Prescription</div>

        <div class="info-grid">
            <div class="info-item">
                <label>Patient Name</label>
                <span>{{ $medicalRecord->patient->name }}</span>
            </div>
            <div class="info-item">
                <label>Date</label>
                <span>{{ $medicalRecord->created_at->format('d M, Y') }}</span>
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

        <div class="rx-symbol">Rx</div>

        <table class="medication-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="50%">Medication</th>
                    <th width="20%">Dosage</th>
                    <th width="25%">Duration</th>
                </tr>
            </thead>
            <tbody>
                @if($medicalRecord->prescription)
                    @foreach($medicalRecord->prescription->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="medication-name">{{ $item->medication_name }}</div>
                            <div class="instructions">Freq: {{ $item->frequency }}</div>
                            @if($item->instructions)
                                <div class="instructions">Note: {{ $item->instructions }}</div>
                            @endif
                        </td>
                        <td>{{ $item->dosage }}</td>
                        <td>
                            {{ $item->duration }}
                            @if($item->quantity)
                                <br><small>Qty: {{ $item->quantity }}</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999; padding: 20px;">No medications prescribed.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <div style="font-size: 12px;">
                <strong>Next Visit:</strong> 
                {{ $medicalRecord->follow_up_date ? $medicalRecord->follow_up_date->format('d M, Y') : 'As needed' }}
            </div>

            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="font-weight: 600;">Dr. {{ $medicalRecord->doctor->name }}</div>
                <div style="font-size: 10px; color: #666;">Lic: #123456789</div>
            </div>
        </div>

        <div class="disclaimer">
            This prescription is valid for 30 days from the date of issue. • Generated by Clinic System
        </div>
    </div>

</body>
</html>
