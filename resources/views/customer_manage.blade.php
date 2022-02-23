@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mt-4">
        <div class="col-8">
            <h2>Manage {{ $customer->name }}</h2>
        </div>
        
        <div class="col-4 text-right">
            <a href="{{ url('/') }}" class="btn btn-link">&lsaquo; Back to All Customers</a>
        </div>
    </div>

    <div class="mt-4 bg-white p-4">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header h5">
                        Bank Details
                    </div>
                    <div class="card-body">
                        @if(isset($bank_account))
                            @include('includes.bank_account_details')
                        @else
                            @include('includes.add_bank_account')
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card">
                    <div class="card-header h5">
                        Transactions
                    </div>
                    <div class="card-body">
                        @isset($bank_account)
                            @if ($bank_account->status == "verified")
                                <div class="text-left border-bottom pb-3 mb-3">
                                    <button type="button" class="btn btn-lg btn-primary" data-toggle="modal" data-target="#chargeCustomerModal">
                                        Charge Customer
                                    </button>
                                </div>
                                <div class="modal fade" id="chargeCustomerModal" tabindex="-1" role="dialog" aria-labelledby="chargeCustomerModalLabel" aria-hidden="true" data-backdrop="static">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form class="needs-validation" novalidate method="post" id="chargeCustomerForm" autocomplete="off">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="chargeCustomerModalLabel">Charge {{ $customer->name }}</h5>
                                                    <button type="button" class="close close-charge-customer-modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div id="charge_customer_errors" class="alert alert-danger d-none"></div>
                                                    <div id="charge_customer_success" class="alert alert-light border-success shadow d-none">
                                                        <div class="media">
                                                            <img src="{{ asset('img/tick_animated.gif') }}" class="mr-2" width="80">
                                                            <div class="media-body">
                                                                <h5 class="mt-3 text-success">Charge transaction was submitted successfully</h5>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2">Please note that ACH transactions will have an initial status of <code>pending</code>.</div>
                                                        <div class="mb-2">During the next 4 business days, the payment will either transition to <code>succeeded</code> or <code>failed</code> depending on the customer’s bank.</div>
                                                        <div class="mb-2">Successful ACH payments will be reflected in your Stripe available balance after 7 business days, at which point the funds are available for automatic or manual transfer to your bank account.</div>
                                                    </div>

                                                    <div id="charge_customer_fields">
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
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary close-charge-customer-modal">Close</button>
                                                    <button type="button" id="chargeCustomerButton" class="btn btn-primary">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endisset    

                        @if (isset($charges) && $charges->data)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Date</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($charges as $charge)
                                        <tr>
                                            <td>{{ date('h:i A - l, F jS, Y' ,$charge->created) }}</td>
                                            <td>USD {{ $charge->amount/100 }}.00</td>
                                            <td>{{ $charge->description }}</td>
                                            <td><code class="text-nowrap">{{ $charge->status }}</code></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info" role="alert">
                                No transactions were found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#amount').mask('#,##0.00', {
            placeholder: "0.00",
            reverse: true,
        });
        
        $("#chargeCustomerButton").on("click", function() {
            if (document.getElementById('chargeCustomerForm').checkValidity() === true) {
                $("#charge_customer_errors").addClass("d-none");
                hideChargeCustomerModal(false);
                $('#processing_modal').modal('show');

                $.ajax({
                    method: 'POST',
                    url: '{{ url("/ach/$hash/charge") }}',
                    data: $("#chargeCustomerForm").serialize(),
                    dataType: 'json'
                })
                .done(function(msg) {
                    $('#processing_modal').modal('hide');
                    $('#chargeCustomerModal').modal('show');

                    if (msg.error) {
                        $("#charge_customer_errors").html(msg.error).removeClass("d-none");
                    } else {
                        $("#charge_customer_success").removeClass("d-none");
                        $("#charge_customer_fields").addClass("d-none");
                        $("#chargeCustomerButton").addClass("d-none");
                    }
                })
                .fail(function() {
                    $('#processing_modal').modal('hide');
                    $('#chargeCustomerModal').modal('show');
                    
                    $("#charge_customer_errors").html("Something went wrong while trying to charge the customer. Please try again later.").removeClass("d-none");
                })
                .always(function() {
                    $(".close-charge-customer-modal").addClass("allow-modal-close");
                });
            }

            $("#chargeCustomerForm").addClass('was-validated');
        });

        $(".close-charge-customer-modal").on("click", function() {
            hideChargeCustomerModal(true);
        });

        function hideChargeCustomerModal(reloadPage) {
            if (reloadPage == false) {
                $('#chargeCustomerModal').modal('hide');
            } else {
                if ($(".close-charge-customer-modal").first().hasClass("allow-modal-close")) {
                    location.reload();
                } else {
                    $('#chargeCustomerModal').modal('hide');
                }
            }
        }
    });
</script>

@include('includes.processing_modal')

@endsection
