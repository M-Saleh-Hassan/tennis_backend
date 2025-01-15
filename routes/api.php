<?php

use App\Http\Controllers\API\TennisCourtController;
use App\Http\Controllers\API\TimeslotController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tennis-courts', TennisCourtController::class);
Route::get('timeslots', [TimeslotController::class, 'getTimeslotsByDay']);
Route::post('timeslots/{id}/reserve', [TimeslotController::class, 'reserve']);
