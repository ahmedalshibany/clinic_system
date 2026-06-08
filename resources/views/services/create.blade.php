@extends('layouts.dashboard')

@section('title', __('messages.services') . ' / ' . __('messages.create'))
@section('page-title', __('messages.services') . ' / ' . __('messages.create'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">{{ __('messages.create') }} {{ __('messages.service') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('services.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="code" class="form-label">{{ __('messages.serviceCode') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required placeholder="e.g. CON-001">
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label">{{ __('messages.nameEn') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="name_ar" class="form-label">{{ __('messages.nameAr') }}</label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" dir="rtl">
                            @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="category" class="form-label">{{ __('messages.category') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="" selected disabled>{{ __('messages.selectCategory') }}</option>
                                <option value="consultation" {{ old('category') == 'consultation' ? 'selected' : '' }}>{{ __('messages.catConsultation') }}</option>
                                <option value="procedure" {{ old('category') == 'procedure' ? 'selected' : '' }}>{{ __('messages.catProcedure') }}</option>
                                <option value="lab" {{ old('category') == 'lab' ? 'selected' : '' }}>{{ __('messages.catLab') }}</option>
                                <option value="imaging" {{ old('category') == 'imaging' ? 'selected' : '' }}>{{ __('messages.catImaging') }}</option>
                                <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>{{ __('messages.catOther') }}</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="price" class="form-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">{{ __('messages.activeService') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('services.index') }}" class="btn btn-light">{{ __('messages.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('messages.saveService') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection