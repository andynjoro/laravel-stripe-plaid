@if (count($customers) === 0)
    <div class="alert alert-info" role="alert">
        No customers were found.
    </div>
@else
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Email Address</th>
                <th scope="col">Options</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
            <tr>
                <th scope="row">{{ $customer->id }}</th>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email_address }}</td>

                <td>
                    <button type="button" class="btn btn-outline-primary mr-4 showCustomerLinkModal" data-hash="{{ $customer->hash }}">
                        Copy Customer Link
                    </button>
                    
                    <a href="{{ url('/customer') }}/{{ $customer->hash }}/manage" class="btn btn-outline-primary">Manage</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="modal fade" id="customerLinkModal" tabindex="-1" role="dialog" aria-labelledby="customerLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerLinkModalLabel">Customer Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control" id="customerLinkInput" value="" readonly>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="copyToClipBoard()">Copy To Clipboard</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipBoard() {
            /* Get the text field */
            var copyText = document.getElementById("customerLinkInput");

            /* Select the text field */
            copyText.select();
            copyText.setSelectionRange(0, 99999); /*For mobile devices*/

            /* Copy the text inside the text field */
            document.execCommand("copy");

            /* Alert the copied text */
            copyText.classList.add("is-valid");

            setTimeout(function(){
                copyText.classList.remove("is-valid");
            }, 3000);
        } 

        $(document).ready(function() {
            $(".showCustomerLinkModal").on("click", function() {
                var customer_link = "{{ url('/ach') }}/" + $(this).attr("data-hash");

                $("#customerLinkInput").val(customer_link);

                $("#customerLinkModal").modal("show");
            });
        });    
    </script>
@endif
