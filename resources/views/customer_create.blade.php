@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mt-4">
        <div class="col-4">
            <h2>Add Customer</h2>
        </div>
        <div class="col-4">
            
        </div>
        <div class="col-4 text-right">
            <a href="{{ url('/') }}" class="btn btn-link">&lsaquo; Back to Customers</a>
        </div>
    </div>

    <div class="mt-4 bg-white p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <div class="col-4">
            <form class="needs-validation" novalidate method="post" action="{{ url('/customer/store') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Customer Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ old('name') }}" required>
                    <div class="invalid-feedback">Please enter the customer's name.</div>
                </div>
                <div class="form-group">
                    <label for="email_address">Email address</label>
                    <input type="email" class="form-control" id="email_address" name="email_address" placeholder="Enter Email Address" value="{{ old('email_address') }}" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>    
    </div>
</div>
@endsection
