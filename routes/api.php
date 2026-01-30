<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kiosk\ScanController;
use App\Http\Controllers\Kiosk\RentalController;
use App\Http\Controllers\Kiosk\LockerController;

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
Route::post('kiosk/rentals/start', [RentalController::class, 'start']); // Start Rental Route
Route::get('kiosk/rentals/active', [RentalController::class, 'active']); // Active Rental Route
Route::post('/kiosk/rentals/{rental}/end', [RentalController::class, 'end']); // End Rental Route

Route::get('/kiosk/lockers/status', [LockerController::class, 'status']);