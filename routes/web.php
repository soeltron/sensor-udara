<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;

// Auth Routes (only for guests)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Logout (auth only)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Dashboard — accessible by EVERYONE (guests see read-only, logged-in see controls)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/data/latest', [DashboardController::class, 'getData'])->name('data.latest');

// Protected Settings & Controls (auth required)
Route::middleware('auth')->group(function () {
    Route::post('/settings/update', [DashboardController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/led', [DashboardController::class, 'toggleLed'])->name('settings.led');
    Route::post('/settings/fan', [DashboardController::class, 'toggleFan'])->name('settings.fan');
    Route::post('/settings/fan/mode', [DashboardController::class, 'setFanMode'])->name('settings.fan.mode');
    Route::post('/settings/unit', [DashboardController::class, 'changeUnit'])->name('settings.unit');
});

// API Routes (Simulated in web.php, CSRF disabled for api/*)
Route::post('/api/data', [ApiController::class, 'storeData']);
Route::get('/api/settings', [ApiController::class, 'getSettings']);
