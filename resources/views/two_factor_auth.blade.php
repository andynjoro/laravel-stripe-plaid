@extends('layouts.front') 
@section('content')
<div class="container">
  <h2 class="mt-4">{{ $customer->name }}</h2>
  <h4 class="mt-3 mb-4">We just need to verify that it's really you</h4>

  @if (session('code_sent'))
    <div class="alert alert-info" role="alert">
        <div>A two factor authentication code has been sent to your email address {{ substr($customer->email_address, 0, 3) }}******{{ substr($customer->email_address, 9) }}.</div>
    </div>
  @endif
  <div class="row justify-content-left">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header h5">Enter Two Factor Authentication Token</div>
        <div class="card-body">
            @if (session('wrong_code'))
                <div class="alert alert-danger" role="alert">
                    <div class="mb-2">You have entered an incorrect code or your code has expired.</div>
                    <div>
                        <form action='{{ url("/ach/$hash/2fa/resend") }}' method="post">
                            @csrf
                            <button class="btn btn-primary btn-large">Resend Code?</button>
                        </form>
                    </div>
                </div>
            @endif

            <form action='{{ url("/ach/$hash/2fa/verify") }}' method="post">
                @csrf
                <div class="form-group">
                <label for="token">Token</label>
                <input type="text" name="token" placeholder="Enter received token" class="form-control{{ $errors->has('token') ? ' is-invalid' : '' }}"
                    id="token"> @if ($errors->has('token'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('token') }}</strong>
                    </span> @endif
                </div>

                <button class="btn btn-primary btn-large">Verify</button>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection