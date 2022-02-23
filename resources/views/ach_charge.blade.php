@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mt-4">
        <div class="col-8">
            <h2>Charge {{ $customer->name }}</h2>
        </div>

        <div class="col-4 text-right">
            <a href="{{ url('/') }}" class="btn btn-link">&lsaquo; Back to All Customers</a>
        </div>
    </div>

    <div class="mt-4 bg-white p-4">
        <div class="bg-light p-4">
            <h4>{{ $bank_account->bank_name }}</h4>
            <h5>{{ $bank_account->account_holder_name }}</h5>
            <h6>Account ending in {{ $bank_account->last4 }}</h6>
            <h4>
                @if($bank_account->status == "verified")
                    <span class="badge badge-success">{{ ucfirst($bank_account->status) }}</span>
                @else
                <span class="badge badge-secondary">{{ ucfirst($bank_account->status) }}</span>
                @endif
            </h4>
        </div>

        @if ($bank_account->status != "verified")
            <div class="mt-4">
                <div class="alert alert-warning" role="alert">
                    <h4>The customer's bank account is not verified.</h4>
                    <div>
                        <a target="_blank" href="{{ url('/customer/ach') }}/{{ $customer->hash }}" class="btn btn-primary">Verify</a>
                    </div>
                </div>
            </div>
        @else
            <div class="row bg-light p-4 mt-4">
                <div class="col-6">
                    <h3>Charge Details</h3>
                    <form class="needs-validation mt-4" novalidate method="post" action="{{ url('/customer/ach/charge/result') }}" autocomplete="off">
                        @csrf
                        <input type="hidden" name="hash" value="{{ $hash }}" />
                        <div class="form-group">
                            <label for="amount">Amount (in USD)</label>
                            <input class="form-control" id="amount" placeholder="Enter amount" name="amount" required>
                            <div class="invalid-feedback">Please enter a valid amount.</div>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control" id="description" placeholder="Enter description" name="description" required>
                            <div class="invalid-feedback">Please enter a brief description.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#amount').mask('#,##0.00', {
            placeholder: "0.00",
            reverse: true
        });
    });

</script>
@endsection
