<div class="bg-light p-4">
    <h4>{{ $bank_account->bank_name }}</h4>
    <h5>{{ $bank_account->account_holder_name }}</h5>
    <h6>Account ending in <strong>{{ $bank_account->last4 }}</strong></h6>
    <h4>
        @if($bank_account->status == "verified")
            <span class="badge badge-success">{{ ucfirst($bank_account->status) }}</span>
        @else
            <span class="badge badge-secondary">{{ ucfirst($bank_account->status) }}</span>
        @endif
    </h4>
</div>

@if($bank_account->status != "verified")
    <div class="mt-4">
        @if(isset($verify_errors) && count($verify_errors) > 0)
            <div class="alert alert-danger mb-4">
                @foreach ($verify_errors as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <h4 class="mb-2">Verify Bank Account Details</h4>
        <div class="alert alert-info mb-3" role="alert">
            Two small deposits were sent to the bank account above. These deposits will take 1-2 business days to appear on your online statement.
            The statement description for these deposits will be AMTS: and then the values of the two microdeposits that were sent.
            Please enter the values of those deposits below.
        </div>
        <form class="needs-validation" id="verifyBankAccountForm" novalidate method="post" autocomplete="off">
            @csrf
            <div id="verify_bank_account_errors" class="alert alert-danger d-none"></div>
            <div class="form-group">
                <label for="amount1">Amount 1</label>
                <input type="text" class="form-control" id="amount1" placeholder="Enter Amount 1" name="amount1" required>
                <div class="invalid-feedback">Please enter amount 1.</div>
            </div>
            <div class="form-group">
                <label for="amount1">Amount 2</label>
                <input type="text" class="form-control" id="amount2" placeholder="Enter Amount 2" name="amount2" required>
                <div class="invalid-feedback">Please enter amount 2.</div>
            </div>
            <button type="button" id="verifyBankAccountButton" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $("#verifyBankAccountButton").on("click", function() { 
                if (document.getElementById('verifyBankAccountForm').checkValidity() === true) {
                    $("#verify_bank_account_errors").addClass("d-none");
                    $('#processing_modal').modal('show');

                    $.ajax({
                        method: 'POST',
                        url: '{{ url("/ach/$hash/verify-bank-account") }}',
                        data: $("#verifyBankAccountForm").serialize(),
                        dataType: 'json'
                    })
                    .done(function(msg) {
                        if (msg.error) {
                            $('#processing_modal').modal('hide');

                            $("#verify_bank_account_errors").html(msg.error).removeClass("d-none");
                        } else {
                            location.reload();
                        }
                    })
                    .fail(function() {
                        $('#processing_modal').modal('hide');

                        $("#verify_bank_account_errors").html("Something went wrong while trying to verify your bank account. Please try again later.").removeClass("d-none");
                    });
                }

                $("#addBankAccountForm").addClass('was-validated');
            });
        });
    </script>
@endif