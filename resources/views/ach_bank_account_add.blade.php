@extends('layouts.front')

@section('content')
<div class="container">
    <div class="row mt-4">
        <div class="col-8">
            <h2>{{ $customer->name }}</h2>
        </div>
        
        <div class="col-4 text-right">
            <a href="{{ url('ach') }}/{{ $customer->hash }}" class="btn btn-link">&lsaquo; Back to Bank Details</a>
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
        <div class="col-6">
            <h3 class="mb-3">Add Bank Account Details</h3>
            <form class="needs-validation" novalidate method="post">
                @csrf
                <div class="form-group">
                    <label for="account_holder_name">Account Holder's Name</label>
                    <input type="text" value="{{ old('account_holder_name') }}" class="form-control" id="account_holder_name" placeholder="Enter name" name="account_holder_name" required>
                    <div class="invalid-feedback">Please enter the account holder's name.</div>
                </div>
                <div class="form-group">
                    <label for="account_holder_type">Account Type</label>
                    <select name="account_holder_type" class="custom-select">
                        <option value="company">Company</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="routing_number">Routing Number</label>
                    <input type="text" value="{{ old('routing_number') }}" class="form-control" id="routing_number" placeholder="Enter routing number" name="routing_number" required>
                    <div class="invalid-feedback">Please enter the routing number.</div>
                </div>
                <div class="form-group">
                    <label for="account_number">Account Number</label>
                    <input type="text" value="{{ old('account_number') }}" class="form-control" id="account_number" placeholder="Enter account number" name="account_number" required>
                    <div class="invalid-feedback">Please enter the account number.</div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>  
    </div>
</div>
@endsection
