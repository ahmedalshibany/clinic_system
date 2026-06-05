@extends('layouts.dashboard')

@section('title', 'Invoices / Create')
@section('page-title', 'Invoices / Create')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header   py-3">
                <h5 class="card-title mb-0">Invoices / Create</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.store') }}" method="POST">
                    @include('invoices.form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
