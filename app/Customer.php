<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Laravel\Cashier\Billable;

class Customer extends Model {
    use Billable;
}
