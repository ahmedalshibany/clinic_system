@extends('layouts.dashboard')

@section('title', __('messages.medicalRecords'))
@section('page-title', __('messages.medicalRecords'))

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">{{ __('messages.patient') }}</label>
                <select name="patient_id" class="form-select">
                    <option value="">{{ __('messages.allPatients') }}</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('messages.doctor') }}</label>
                <select name="doctor_id" class="form-select">
                    <option value="">{{ __('messages.allDoctors') }}</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('messages.date') }}</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('messages.search') }}</label>
                <input type="text" name="search" class="form-control" placeholder="{{ __('messages.diagnosisPlaceholderShort') }}" value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> {{ __('messages.filter') }}</button>
                <a href="{{ route('medical-records.index') }}" class="btn btn-light w-100"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.patient') }}</th>
                        <th>{{ __('messages.doctor') }}</th>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.diagnosis') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                    <tr>
                        <td>{{ $record->id }}</td>
                        <td>{{ $record->patient->name ?? 'N/A' }}</td>
                        <td>{{ $record->doctor->name ?? 'N/A' }}</td>
                        <td>{{ $record->visit_date->format('Y-m-d') }}</td>
                        <td>{{ Str::limit($record->diagnosis, 40) }}</td>
                        <td>
                            <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('medical-records.destroy', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this medical record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">{{ __('messages.noMedicalRecords') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $records->links() }}
</div>
@endsection
