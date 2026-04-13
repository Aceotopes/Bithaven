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
use App\Http\Controllers\Kiosk\UnlockJobController;
use App\Http\Controllers\Kiosk\DaemonController;
use App\Http\Controllers\Kiosk\AdminUnlockController;
use App\Http\Controllers\Kiosk\AdminPenaltyController;
use App\Http\Controllers\Kiosk\AdminLockerController;
use App\Http\Controllers\Kiosk\AdminScanController;
use App\Http\Controllers\Kiosk\RfidSessionController; // for ADMIN RFID scan sessions 
use App\Http\Controllers\Kiosk\AdminSystemController; // for verifying admin PIN at kiosk
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminCardController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LiveOperationsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\RfidController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\LogsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// sample health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// KIOSK ROUTES
Route::post('/kiosk/scan', [ScanController::class, 'scan']);
Route::get('/kiosk/rfid/pending', [RfidSessionController::class, 'pending']);
Route::post('/kiosk/rfid/{session}/complete', [RfidSessionController::class, 'complete']);

Route::get('/kiosk/lockers/status', [LockerController::class, 'status']);
Route::post('/kiosk/lockers/{locker}/authorize', [LockerController::class, 'authorize']);

Route::get('/kiosk/rentals/active', [RentalController::class, 'active']);
Route::post('/kiosk/rentals/{rental}/end', [RentalController::class, 'end']);
Route::post('/kiosk/rentals/{rental}/expire', [RentalController::class, 'expire']);

Route::get('/kiosk/penalties/active', [PenaltyController::class, 'active']);
Route::get('/kiosk/penalties/{penalty}/live', [PenaltyController::class, 'live']);

Route::post('/kiosk/payment-sessions/start', [PaymentSessionController::class, 'start']);
Route::get('/kiosk/payment-sessions/{session}', [PaymentSessionController::class, 'show']);
Route::post('/kiosk/coins/insert', [CoinController::class, 'insert']);

Route::post('/kiosk/payments/penalty', [PaymentController::class, 'payPenalty']);
Route::post('/kiosk/payments/rental', [PaymentController::class, 'payRental']);

Route::get('/kiosk/unlock-jobs/pending', [UnlockJobController::class, 'pending']);
Route::get('/kiosk/unlock-jobs/{job}', [UnlockJobController::class, 'show']);
Route::post('/kiosk/unlock-jobs/{job}/status', [UnlockJobController::class, 'updateStatus']);
Route::post('/kiosk/unlock-jobs/{job}/processing', [UnlockJobController::class, 'processing']);
Route::post('/kiosk/unlock-jobs/{job}/cancel', [UnlockJobController::class, 'cancel']);

// ADMIN KIOSK ROUTES
Route::post('/kiosk/admin/scan', [AdminScanController::class, 'scan']);
Route::post('/kiosk/admin/force-unlock', [AdminUnlockController::class, 'forceUnlock']);
Route::post('/kiosk/admin/clear-penalty', [AdminPenaltyController::class, 'clear']);
Route::post('/kiosk/admin/end-rental', [AdminScanController::class, 'endRentalEarly']);
Route::post('/kiosk/admin/lockers/disable', [AdminLockerController::class, 'disable']);
Route::post('/kiosk/admin/lockers/enable', [AdminLockerController::class, 'enable']);
Route::get('/kiosk/admin/lockers', [AdminLockerController::class, 'index']);
Route::get('/kiosk/admin/lockers/{locker}', [AdminLockerController::class, 'show']);
Route::post('/kiosk/admin/verify-pin', [AdminSystemController::class, 'verifyPin']);
Route::post('/kiosk/admin/emergency-unlock', [AdminSystemController::class, 'emergencyUnlock']);
Route::get('/kiosk/admin/unlock-jobs/batch/{batchId}', [UnlockJobController::class, 'batchStatus']);
// Route::middleware('kiosk.admin')->prefix('kiosk/admin')->group(function () {
//     Route::get('/lockers', [AdminLockerController::class, 'index']);
//     Route::get('/lockers/{locker}', [AdminLockerController::class, 'show']);

// });

// DAEMON ROUTES
Route::get('/kiosk/unlock-tokens/pending', [UnlockTokenController::class, 'pending']);
Route::post('/kiosk/unlock-tokens/{token}/confirm', [UnlockTokenController::class, 'confirm']);
Route::post('/kiosk/daemon/heartbeat', [DaemonController::class, 'heartbeat']);
Route::get('/kiosk/daemon/status', [DaemonController::class, 'status']);
// Route::get('/admin/daemon/status', [DaemonController::class, 'status']);
// ADMIN ROUTES
//TEMP
// Route::get('/admin/dashboard/summary', [DashboardController::class, 'summary']);
// Route::get('/admin/live/lockers', [LiveOperationsController::class, 'lockers']);
// Route::post('/admin/live/lockers/{locker}/force-unlock', [LiveOperationsController::class, 'forceUnlock']);
// Route::post('/admin/live/rentals/{rental}/end', [LiveOperationsController::class, 'endRental']);
// Route::post('/admin/live/penalties/{penalty}/clear', [LiveOperationsController::class, 'clearPenalty']);
// Route::post('/admin/live/lockers/{locker}/disable', [LiveOperationsController::class, 'disableLocker']);
// Route::post('/admin/live/lockers/{locker}/enable', [LiveOperationsController::class, 'enableLocker']);

//FINAL
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('/admin/me', function (Request $request) {
        return response()->json([
            'admin' => $request->user()
        ]);
    });
    // temporarily commented for testing without auth
    Route::get('/admin/dashboard/summary', [DashboardController::class, 'summary']); //FOR DASHBOARD

    Route::get('/admin/live/lockers', [LiveOperationsController::class, 'lockers']);
    Route::post('/admin/live/lockers/{locker}/force-unlock', [LiveOperationsController::class, 'forceUnlock']);
    Route::post('/admin/live/rentals/{rental}/end', [LiveOperationsController::class, 'endRental']);
    Route::post('/admin/live/penalties/{penalty}/clear', [LiveOperationsController::class, 'clearPenalty']);
    Route::post('/admin/live/lockers/{locker}/disable', [LiveOperationsController::class, 'disableLocker']);
    Route::post('/admin/live/lockers/{locker}/enable', [LiveOperationsController::class, 'enableLocker']);

    Route::get('/admin/students/summary', [StudentController::class, 'summary']);
    Route::apiResource('/admin/students', StudentController::class);
    Route::post('/admin/rfid/start', [RfidController::class, 'start']);
    Route::get('/admin/rfid/{session}', [RfidController::class, 'show']);
    Route::post('/admin/rfid/{session}/cancel', [RfidController::class, 'cancel']);

    Route::post('/admin/profile/update', [AdminProfileController::class, 'update']);
    Route::post('/admin/profile/change-password', [AdminProfileController::class, 'changePassword']);

    Route::get('/admin/financials/transactions', [FinancialController::class, 'transactions']);
    Route::get('/admin/financials/summary', [FinancialController::class, 'summary']);
    Route::get('/admin/financials/locker-revenue', [FinancialController::class, 'lockerRevenue']);
    Route::get('/admin/financials/penalties', [FinancialController::class, 'penalties']);
    Route::get('/admin/financials/revenue-summary', [FinancialController::class, 'revenueSummary']);

    Route::get('/admin/logs', [LogsController::class, 'index']);
    Route::get('/admin/logs/events', [LogsController::class, 'events']);
    Route::get('/admin/logs/security', [LogsController::class, 'security']);

    Route::get('/admin/daemon/status', [DaemonController::class, 'status']);

});
Route::middleware(['auth:admin', 'superadmin'])->group(function () {

    Route::get('/admin/manage', function () {
        return response()->json(['message' => 'Super admin area']);
    });

    // Admin Management for Super Admins
    Route::get('/admin/admins', [AdminManagementController::class, 'index']);
    Route::post('/admin/admins', [AdminManagementController::class, 'store']);
    Route::put('/admin/admins/{admin}', [AdminManagementController::class, 'update']);
    Route::delete('/admin/admins/{admin}', [AdminManagementController::class, 'destroy']);

    // Admin Card Kiosk Access Management
    Route::get('/admin/cards', [AdminCardController::class, 'index']);
    Route::post('/admin/cards', [AdminCardController::class, 'store']);
    Route::put('/admin/cards/{card}', [AdminCardController::class, 'update']);
    Route::delete('/admin/cards/{card}', [AdminCardController::class, 'destroy']);
    Route::post('/admin/settings/emergency-pin', [AdminSystemController::class, 'updatePin']);
});
