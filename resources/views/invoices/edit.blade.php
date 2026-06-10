@extends('layouts.dashboard')

@section('title', __('messages.edit_invoice'))
@section('page-title', __('messages.edit_invoice'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header   py-3">
                <h5 class="card-title mb-0">{{ __('messages.edit_invoice') }}: {{ $invoice->invoice_number }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                    @method('PUT')
                    @include('invoices.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
