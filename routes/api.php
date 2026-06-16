<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;

Route::post('/data',     [SensorController::class, 'store']);
Route::get('/settings',  [SensorController::class, 'getSettings']);
Route::get('/latest',    [SensorController::class, 'latest']);
Route::post('/relay',    [SensorController::class, 'toggleRelay']);
Route::post('/automode', [SensorController::class, 'setAutoMode']);
