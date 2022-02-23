<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', 'HomeController@index')->name('home');
Route::get('/customer/create', 'HomeController@createCustomer');
Route::post('/customer/store', 'HomeController@storeCustomer');
Route::get('/customer/search', 'HomeController@searchCustomer');

Route::get('/customer/{hash}/manage', 'AchController@manageCustomer');

Route::get('/ach/{hash}', 'AchController@index')->middleware('two_factor_auth');
Route::get('/ach/{hash}/2fa', 'AchController@twoFactorAuth');
Route::post('/ach/{hash}/2fa/verify', 'AchController@twoFactorAuthVerify');
Route::post('/ach/{hash}/2fa/resend', 'AchController@twoFactorAuthResend');

Route::post('/ach/{hash}/add-bank-account', 'AchController@addBankAccount');
Route::post('/ach/{hash}/verify-bank-account', 'AchController@verifyBankAccount');
Route::post('/ach/process-plaid', 'AchController@processPlaid');

Route::post('/ach/{hash}/charge', 'AchController@chargeCustomer')->middleware('auth');

Route::get('/test', 'AchController@test');
