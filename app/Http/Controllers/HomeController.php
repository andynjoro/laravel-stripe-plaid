<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller {
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $customers = Customer::all();

        return view('home')->with('customers', $customers);
    }

    public function createCustomer() {
        return view('customer_create');
    }

    public function storeCustomer(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required',
            'email_address' => 'required|unique:customers|max:255',
        ]);

        $customer = new Customer();

        $customer->name = $validatedData['name'];
        $customer->email_address = $validatedData['email_address'];
        $customer->hash = Str::random();

        $customer->save();

        return redirect()->action('HomeController@index');
    }

    public function searchCustomer(Request $request) {
        $customer_query = $request->get('customer_query');

        $customers = Customer::where('name', 'LIKE', '%'.$customer_query.'%')->get();

        return view('customer_search')->with('customers', $customers);
    }
}
