<?php

namespace App\Http\Middleware;

use App\Customer;
use App\Mail\TwoFactorAuthMail;
use Closure;
use Illuminate\Support\Str;

class TwoFactorVerification {
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $hash = $request->route('hash');

        $customer = Customer::where('hash', '=', $hash)->first();

        if ($customer->two_factor_expiry > \Carbon\Carbon::now()) {
            return $next($request);
        }

        $customer->two_factor_token = strtoupper(Str::random(10));

        $customer->save();

        \Mail::to($customer->email_address)->send(new TwoFactorAuthMail($customer->two_factor_token));

        return redirect("/ach/${hash}/2fa")->with('code_sent', true);
    }
}
