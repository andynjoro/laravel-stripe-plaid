@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mt-4">
        <div class="col-4">
            <h2>Customers</h2>
        </div>
        <div class="col-4">
            <form class="needs-validation form-inline" novalidate method="get" action="{{ url('/customer/search') }}">
                @csrf
                <div class="input-group">
                    <input type="text" class="form-control" id="customer_query" name="customer_query" placeholder="Find Customer" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Go</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-4 text-right">
            <a href="{{ url('/customer/create') }}" class="btn btn-primary">Add Customer</a>
        </div>
    </div>

    @include('includes.customer_list')

</div>
@endsection
