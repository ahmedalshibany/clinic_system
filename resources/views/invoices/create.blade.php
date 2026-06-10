@extends('layouts.dashboard')

@section('title', __('messages.create_invoice'))
@section('page-title', __('messages.create_invoice'))
@section('page-i18n', 'invoices')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header   py-3">
                <h5 class="card-title mb-0">{{ __('messages.create_invoice') }}</h5>
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
