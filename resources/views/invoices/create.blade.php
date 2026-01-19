@extends('layouts.dashboard')

@section('title', 'Create Invoice')
@section('page-title', 'Create Invoice')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">New Invoice</h5>
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
