@extends('layouts.dashboard')

@section('title', 'Edit Invoice')
@section('page-title', 'Edit Invoice')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Edit Invoice: {{ $invoice->invoice_number }}</h5>
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
