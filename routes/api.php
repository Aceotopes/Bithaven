<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kiosk\ScanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// sample health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/kiosk/scan', [ScanController::class, 'scan']);
