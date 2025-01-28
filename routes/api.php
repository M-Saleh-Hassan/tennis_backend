<?php

use App\Http\Controllers\API\TennisCourtController;
use App\Http\Controllers\API\TimeslotController;
use App\Http\Controllers\PaymobWebhookController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tennis-courts', TennisCourtController::class);
Route::get('timeslots', [TimeslotController::class, 'getTimeslotsByDay']);
Route::post('timeslots/reserve', [TimeslotController::class, 'reserve']);

Route::post('/paymob/webhook', [PaymobWebhookController::class, 'handle'])->name('paymob.webhook');
