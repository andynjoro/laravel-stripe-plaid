<div>
    <h3 class="mb-3">Connect bank account</h3>
    
    <div id="plaid_errors" class="alert alert-danger d-none"></div>

    <div class="mt-4">
        <div class="mb-4">
            <button id="linkButton" class="btn btn-lg btn-primary shadow">
                <img src="{{ asset('img/plaid_logo.png') }}" width="28" class="align-bottom" />
                <span class="ml-1">Connect via Plaid</span>
            </button>
        </div>
        <div>
            <button type="button" class="btn btn-lg btn-primary" data-toggle="modal" data-target="#addBankAccountModal">
                Manually Add Bank Account
            </button>

            <div class="modal fade" id="addBankAccountModal" tabindex="-1" role="dialog" aria-labelledby="addBankAccountModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form class="needs-validation" novalidate method="post" id="addBankAccountForm" autocomplete="off">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="addBankAccountModalLabel">Manually Add Bank Account</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="add_bank_account_errors" class="alert alert-danger d-none"></div>

                                <div class="form-group">
                                    <label for="account_holder_name">Account Holder's Name</label>
                                    <input type="text" class="form-control"
                                        id="account_holder_name" placeholder="Enter name" name="account_holder_name"
                                        required>
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
                                    <input type="text" class="form-control"
                                        id="routing_number" placeholder="Enter routing number" name="routing_number"
                                        required>
                                    <div class="invalid-feedback">Please enter the routing number.</div>
                                </div>
                                <div class="form-group">
                                    <label for="account_number">Account Number</label>
                                    <input type="text" class="form-control"
                                        id="account_number" placeholder="Enter account number" name="account_number"
                                        required>
                                    <div class="invalid-feedback">Please enter the account number.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" id="addBankAccountButton" class="btn btn-primary">Submit</button>
                            </div>
                        </form>    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
<script>    
    var user_error = "Something went wrong while trying to link your bank account. Please try again later.";

    var linkHandler = Plaid.create({
        env: '{{ env('PLAID_ENV') }}',
        clientName: 'Travis Wood',
        key: '{{ env('PLAID_KEY') }}',
        product: ['auth'],
        selectAccount: true,
        onSuccess: function(public_token, metadata) {
            $('#processing_modal').modal('show');

            // Send the public_token and account ID to your app server.
            var data = {
                hash: "{{ $hash }}",
                public_token: public_token,
                account_id: metadata.account_id,
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                method: "POST",
                url: "{{ url('/ach/process-plaid') }}",
                data: data
            }).always(function() {
                $('#processing_modal').modal('hide');
            }).done(function(response) {
                if (response.status == 'success') {
                    location.reload();
                } else {
                    $("#plaid_errors").html(user_error).removeClass("d-none");;
                }
            }).fail(function() {
                $("#plaid_errors").html(user_error).removeClass("d-none");
            });
        },
        onExit: function(err, metadata) {
            // The user exited the Link flow.
            if (err != null) {
                // The user encountered a Plaid API error prior to exiting.
                $("#plaid_errors").html(user_error).removeClass("d-none");;
            }
        },
    });

    // Trigger the Link UI
    document.getElementById('linkButton').onclick = function() {
        linkHandler.open();
    };

    $(document).ready(function() {
        $("#addBankAccountButton").on("click", function() {
            if (document.getElementById('addBankAccountForm').checkValidity() === true) {
                $("#add_bank_account_errors").addClass("d-none");
                $('#addBankAccountModal').modal('hide');
                $('#processing_modal').modal('show');

                $.ajax({
                    method: 'POST',
                    url: '{{ url("/ach/$hash/add-bank-account") }}',
                    data: $("#addBankAccountForm").serialize(),
                    dataType: 'json'
                })
                .done(function(msg) {
                    if (msg.error) {
                        $('#processing_modal').modal('hide');
                        $('#addBankAccountModal').modal('show');

                        $("#add_bank_account_errors").html(msg.error).removeClass("d-none");
                    } else {
                        location.reload();
                    }
                })
                .fail(function() {
                    $('#processing_modal').modal('hide');
                    $('#addBankAccountModal').modal('show');
                    
                    $("#add_bank_account_errors").html(user_error).removeClass("d-none");
                });
            }

            $("#addBankAccountForm").addClass('was-validated');
        });
    });
</script>