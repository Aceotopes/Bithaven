<?php

use App\Http\Controllers\Kiosk\CoinController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kiosk\ScanController;
use App\Http\Controllers\Kiosk\RentalController;
use App\Http\Controllers\Kiosk\LockerController;
use App\Http\Controllers\Kiosk\PenaltyController;
use App\Http\Controllers\Kiosk\PaymentController;
use App\Http\Controllers\Kiosk\PaymentSessionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// sample health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Kiosk Scan Route
Route::post('/kiosk/scan', [ScanController::class, 'scan']);

// Kiosk Rental Routes
// Route::post('kiosk/rentals/start', [RentalController::class, 'start']); // Start Rental Route
Route::get('kiosk/rentals/active', [RentalController::class, 'active']); // Active Rental Route
Route::post('/kiosk/rentals/{rental}/end', [RentalController::class, 'end']); // End Rental Route
Route::post('/kiosk/rentals/{rental}/expire', [RentalController::class, 'expire']); // Expire Rental Route

Route::get('/kiosk/lockers/status', [LockerController::class, 'status']); // Locker Status Route

Route::get('/kiosk/penalties/active', [PenaltyController::class, 'active']); // Active Penalty Route
Route::post('/kiosk/penalties/{penalty}/settle', [PenaltyController::class, 'settle']); // Settle Penalty Route 
Route::get('/kiosk/penalties/{penalty}/live', [PenaltyController::class, 'live']); // Penalty Breakdown Route

Route::post('/kiosk/payments/penalty', [PaymentController::class, 'payPenalty']); // Pay Penalty Route    
Route::post('/kiosk/payments/rental', [PaymentController::class, 'payRental']); // Pay Penalty Route    

Route::post('/kiosk/coins/insert', [CoinController::class, 'insert']); // Insert Coin Route

Route::post('/kiosk/payment-sessions/start', [PaymentSessionController::class, 'start']); // Start Payment Session Route