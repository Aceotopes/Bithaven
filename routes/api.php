<?php

use App\Http\Controllers\Kiosk\CoinController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kiosk\ScanController;
use App\Http\Controllers\Kiosk\RentalController;
use App\Http\Controllers\Kiosk\LockerController;
use App\Http\Controllers\Kiosk\PenaltyController;
use App\Http\Controllers\Kiosk\PaymentController;
use App\Http\Controllers\Kiosk\UnlockTokenController;
use App\Http\Controllers\Kiosk\PaymentSessionController;
use App\Http\Controllers\kiosk\DaemonController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// sample health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// KIOSK ROUTES
Route::post('/kiosk/scan', [ScanController::class, 'scan']);

Route::get('/kiosk/lockers/status', [LockerController::class, 'status']);
Route::post('/kiosk/lockers/{locker}/authorize', [LockerController::class, 'authorize']);

Route::get('/kiosk/rentals/active', [RentalController::class, 'active']);
Route::post('/kiosk/rentals/{rental}/end', [RentalController::class, 'end']);
Route::post('/kiosk/rentals/{rental}/expire', [RentalController::class, 'expire']);

Route::get('/kiosk/penalties/active', [PenaltyController::class, 'active']);
Route::get('/kiosk/penalties/{penalty}/live', [PenaltyController::class, 'live']);

Route::post('/kiosk/payment-sessions/start', [PaymentSessionController::class, 'start']);
Route::post('/kiosk/coins/insert', [CoinController::class, 'insert']);

Route::post('/kiosk/payments/penalty', [PaymentController::class, 'payPenalty']);
Route::post('/kiosk/payments/rental', [PaymentController::class, 'payRental']);


// DAEMON ROUTES
Route::get('/kiosk/unlock-tokens/pending', [UnlockTokenController::class, 'pending']);
Route::post('/kiosk/unlock-tokens/{token}/confirm', [UnlockTokenController::class, 'confirm']);
Route::post('/kiosk/daemon/heartbeat', [DaemonController::class, 'heartbeat']);