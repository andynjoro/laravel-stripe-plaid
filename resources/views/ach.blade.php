@extends('layouts.front')

@section('content')
<div class="container">
    <h2 class="mt-4">{{ $customer->name }}</h2>

    <div class="col-12 col-lg-6 mt-4 bg-white p-4">
        @if(isset($bank_account))
            @include('includes.bank_account_details')
        @else
            @include('includes.add_bank_account')
        @endif
    </div>
</div>

@include('includes.processing_modal')

@endsection
