<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymobController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pay', [PaymobController::class, 'createPayment'])->name('paymob.payment');
//https://accept.paymobsolutions.com/api/acceptance/post_pay
