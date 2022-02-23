<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AchController extends Controller {
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function index($hash) {
        $customer = Customer::where('hash', '=', $hash)->first();

        if (null != $customer->stripe_bank_id) {
            $bank_account = \Stripe\Customer::retrieveSource(
                $customer->stripe_id,
                $customer->stripe_bank_id
            );

            return view('ach')->with('customer', $customer)->with('hash', $hash)->with('bank_account', $bank_account);
        }

        return view('ach')->with('customer', $customer)->with('hash', $hash);
    }

    public function twoFactorAuth($hash) {
        $customer = Customer::where('hash', '=', $hash)->first();

        return view('two_factor_auth')->with('customer', $customer)->with('hash', $hash);
    }

    public function twoFactorAuthVerify(Request $request, $hash) {
        $request->validate([
            'token' => 'required',
        ]);

        $customer = Customer::where('hash', '=', $hash)->first();

        if ($request->input('token') == $customer->two_factor_token) {
            $customer->two_factor_expiry = \Carbon\Carbon::now()->addMinutes(config('session.lifetime'));

            $customer->save();

            return redirect("/ach/${hash}");
        }

        return redirect("/ach/${hash}/2fa")->with('wrong_code', true);
    }

    public function twoFactorAuthResend($hash) {
        $customer = Customer::where('hash', '=', $hash)->first();

        $customer->two_factor_token = strtoupper(Str::random(10));

        $customer->save();

        \Mail::to($customer)->send(new TwoFactorAuthMail($customer->two_factor_token));

        return redirect("/ach/${hash}/2fa")->with('code_sent', true);
    }

    public function addBankAccount(Request $request, $hash) {
        $customer = Customer::where('hash', '=', $hash)->first();

        if ($request->isMethod('post')) {
            $stripe_api_error = '';

            if (null == $customer->stripe_id) {
                try {
                    $stripe_customer = \Stripe\Customer::create([
                        'name' => $customer->name,
                        'email' => $customer->email_address,
                        'description' => 'Customer for ACH Payments',
                    ]);

                    $stripe_id = $stripe_customer->id;

                    $customer->stripe_id = $stripe_id;

                    $customer->save();
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    $stripe_api_error = 'Something went wrong while saving the customer information to Stripe. Please try again later.';

                    return response()->json([
                        'error' => $stripe_api_error,
                    ]);
                }
            }

            try {
                $bank_token = \Stripe\Token::create([
                    'bank_account' => [
                        'country' => 'US',
                        'currency' => 'USD',
                        'account_holder_name' => $request->get('account_holder_name'),
                        'account_holder_type' => $request->get('account_holder_type'),
                        'routing_number' => $request->get('routing_number'),
                        'account_number' => $request->get('account_number'),
                    ],
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $stripe_api_error = 'Something went wrong while saving the bank account details to Stripe. Please try again later.';

                return response()->json([
                    'error' => $stripe_api_error,
                ]);
            }

            $bank_token_id = $bank_token->id;
            $bank_account_id = $bank_token->bank_account->id;

            try {
                $bank_account = \Stripe\Customer::createSource(
                    $customer->stripe_id,
                    [
                        'source' => $bank_token_id,
                    ]
                );
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $stripe_api_error = 'Something went wrong while adding the bank account to the Stripe customer. Please try again later.';

                return response()->json([
                    'error' => $stripe_api_error,
                ]);
            }

            $customer->stripe_bank_id = $bank_account_id;
            $customer->bank_account_type = 'MANUAL';

            $customer->save();
        }

        return response()->json([
            'success' => '1',
        ]);
    }

    public function verifyBankAccount(Request $request, $hash) {
        $customer = Customer::where('hash', '=', $hash)->first();

        $bank_account = \Stripe\Customer::retrieveSource(
            $customer->stripe_id,
            $customer->stripe_bank_id
        );

        $verify_errors = [];

        if ('verified' !== $bank_account->status) {
            try {
                $bank_account->verify(['amounts' => [$request->get('amount1'), $request->get('amount2')]]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $verify_errors[] = $e->getError()->message;
            }
        }

        if ($verify_errors) {
            return response()->json([
                'error' => $verify_errors,
            ]);
        }

        return response()->json([
            'success' => '1',
        ]);
    }

    public function processPlaid(Request $request) {
        $hash = $request->get('hash');
        $public_token = $request->get('public_token');
        $account_id = $request->get('account_id');

        $customer = Customer::where('hash', '=', $hash)->first();

        //create customer
        if (null == $customer->stripe_id) {
            $stripe_customer = \Stripe\Customer::create([
                'name' => $customer->name,
                'email' => $customer->email_address,
                'description' => 'Customer for ACH Payments',
            ]);

            $stripe_id = $stripe_customer->id;

            $customer->stripe_id = $stripe_id;

            $customer->save();
        }

        //get public token from plaid
        $pub_token_postdata = json_encode(
            [
                'client_id' => env('PLAID_CLIENT_ID'),
                'secret' => env('PLAID_SECRET'),
                'public_token' => $public_token,
            ]
        );

        $pub_token_opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $pub_token_postdata,
            ],
        ];

        $pub_token_opts_context = stream_context_create($pub_token_opts);

        $pub_token_opts_url = env('PLAID_ENDPOINT').'/item/public_token/exchange';

        $pub_token_response = file_get_contents($pub_token_opts_url, false, $pub_token_opts_context);

        if (!$pub_token_response) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong while getting the public token.',
            ]);
        }

        $pub_token_parsed = json_decode($pub_token_response);

        //get bank token from Plaid
        $bank_token_postdata = json_encode(
            [
                'client_id' => env('PLAID_CLIENT_ID'),
                'secret' => env('PLAID_SECRET'),
                'access_token' => $pub_token_parsed->access_token,
                'account_id' => $account_id,
            ]
        );

        $bank_token_opts = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $bank_token_postdata,
            ],
        ];

        $bank_token_context = stream_context_create($bank_token_opts);

        $bank_token_url = env('PLAID_ENDPOINT').'/processor/stripe/bank_account_token/create';

        $bank_token_response = file_get_contents($bank_token_url, false, $bank_token_context);

        if (!$bank_token_response) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong while getting the bank token.',
            ]);
        }

        $bank_token_parsed = json_decode($bank_token_response);

        //assign bank account to customer
        $bank_token = $bank_token_parsed->stripe_bank_account_token;

        try {
            $bank_account = \Stripe\Customer::createSource(
                $customer->stripe_id,
                [
                    'source' => $bank_token,
                ]
            );
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Something went wrong while adding the bank account to the Stripe customer.',
            ]);
        }

        $customer->stripe_bank_id = $bank_account->id;
        $customer->bank_account_type = 'PLAID';

        $customer->save();

        $ajax_response = [
            'status' => 'success',
        ];

        return response()->json($ajax_response);
    }

    public function chargeCustomer(Request $request, $hash) {
        $amount = str_replace([',', '.'], '', $request->get('amount'));

        $customer = Customer::where('hash', '=', $hash)->first();

        try {
            $stripe_charge = \Stripe\Charge::create([
                'amount' => $amount,
                'currency' => 'usd',
                'customer' => $customer->stripe_id,
                'source' => $customer->stripe_bank_id,
                'description' => $request->get('description'),
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json([
                'error' => 'Something went wrong while charging the customer. '.$e->getError()->message,
            ]);
        }

        return response()->json([
            'success' => '1',
        ]);
    }

    public function manageCustomer(Request $request, $hash) {
        $customer = Customer::where('hash', '=', $hash)->first();

        if (null != $customer->stripe_bank_id) {
            $bank_account = \Stripe\Customer::retrieveSource(
                $customer->stripe_id,
                $customer->stripe_bank_id
            );

            $charges = \Stripe\Charge::all(
                [
                    'customer' => $customer->stripe_id,
                    'limit' => 30,
                ]
            );

            return view('customer_manage')->with('customer', $customer)->with('hash', $hash)->with('bank_account', $bank_account)->with('charges', $charges);
        }

        return view('customer_manage')->with('customer', $customer)->with('hash', $hash);
    }

    public function test() {
        $result = \Stripe\Charge::all(['limit' => 30]);

        echo '<pre>';

        print_r($result);

        // $pass = "nk6j2YhdDrbFT3GR";
        // $pass_hash = Hash::make($pass);

        // print_r($pass_hash);
    }
}
