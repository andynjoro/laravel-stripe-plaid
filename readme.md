## Laravel Stripe Plaid Application

The main purpose of this application is to capture bank details using the Plaid API and use them later via Stripe to charge customers for recurrent bills.

## Getting Started

Clone this repository on your local environment

```sh
git clone https://github.com/andynjoro/laravel-stripe-plaid.git
```

Navigate to application directory

```sh
cd laravel-stripe-plaid
```

Rename .env.example to .env

```sh
mv .env.example .env
```

Get Stripe API credentials and Plaid credentials and add them to the .env file

```sh
STRIPE_KEY=
STRIPE_SECRET=

PLAID_ENV=
PLAID_CLIENT_ID=
PLAID_KEY=
PLAID_SECRET=
PLAID_ENDPOINT=
PLAID_CLIENT_NAME=
```

Set Laravel's Cashier model

```sh
CASHIER_MODEL=App\Customer
```

Install modules

```sh
composer install
```

Migrate your database

```sh
php artisan migrate
```

Launch app

```sh
php artisan serve
```