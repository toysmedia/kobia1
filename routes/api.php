<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\ActivateVoucherController;
use App\Http\Controllers\Api\PackageController as ApiPackageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// M-Pesa Webhooks (no CSRF, no auth required — called by Safaricom servers)
Route::middleware(['throttle:60,1', 'verify_mpesa_ip'])->group(function () {
    Route::post('/mpesa/stk-callback', [MpesaController::class, 'stkCallback']);
    Route::post('/mpesa/c2b-validation', [MpesaController::class, 'c2bValidation']);
    Route::post('/mpesa/c2b-confirmation', [MpesaController::class, 'c2bConfirmation']);
});

// STK Push — rate limited to prevent abuse
Route::middleware('throttle:10,1')->post('/mpesa/stk-push', [MpesaController::class, 'stkPush']);

// Admin action — requires authentication
Route::middleware('auth:sanctum')->get('/mpesa/register-c2b', [MpesaController::class, 'registerC2BUrls']);

// Voucher activation — rate limited to prevent brute-force
Route::middleware('throttle:10,1')->post('/activate-mpesa-voucher', [ActivateVoucherController::class, 'activate']);

// Package listing
Route::get('/packages', [ApiPackageController::class, 'index']);

// Check payment status — rate limited to prevent enumeration
Route::middleware('throttle:10,1')->get('/check-payment/{ref}', [MpesaController::class, 'checkPayment']);

// Customer Portal API (tenant-scoped)
Route::prefix('customer')->name('api.customer.')->group(function () {
    Route::post('/mpesa/stk-push', [\App\Http\Controllers\Customer\PaymentController::class, 'apiStkPush'])->middleware('auth:customer')->name('stk.push');
    Route::post('/mpesa/stk-callback', [\App\Http\Controllers\Customer\PaymentController::class, 'stkCallback'])->name('stk.callback');
    Route::get('/usage', [\App\Http\Controllers\Customer\CustomerProfileController::class, 'apiUsage'])->middleware('auth:customer')->name('usage');
    Route::get('/payment-status/{checkoutRequestId}', [\App\Http\Controllers\Customer\PaymentController::class, 'paymentStatus'])->middleware('auth:customer')->name('payment.status');
});

// Router auto-registration callback (called from MikroTik script via /tool fetch)
// Protected by shared secret via verify_router_secret middleware
use App\Http\Controllers\Api\AiAssistantController;
use App\Http\Controllers\Api\RouterCallbackController;
use App\Http\Controllers\Api\RouterSyncController;
Route::middleware('verify_router_secret')->post('/router-callback', [RouterCallbackController::class, 'callback']);
Route::middleware('verify_router_secret')->post('/router-heartbeat', [RouterCallbackController::class, 'heartbeat']);
Route::middleware('verify_router_secret')->post('/router-phase-complete', [RouterCallbackController::class, 'phaseComplete']);
Route::middleware('verify_router_secret')->post('/router-sync', [RouterSyncController::class, 'sync']);

// Public hotspot file serving (fetched by MikroTik script via /tool fetch)
Route::get('/hotspot-files/{router}/{file}', [\App\Http\Controllers\Admin\RouterController::class, 'serveHotspotFile'])
    ->where('file', 'login\.html|alogin\.html|status\.html')
    ->name('api.hotspot_file');

// Hotspot files by ref_code (fetched by MikroTik script via /tool fetch)
Route::middleware('throttle:30,1')
    ->get('/router-hotspot/{ref_code}/{file}', [\App\Http\Controllers\Admin\RouterController::class, 'serveHotspotFileByRefCode'])
    ->where('file', 'login\.html|alogin\.html|status\.html')
    ->name('api.hotspot_file_by_ref');

// Router cert files (fetched by MikroTik script via /tool fetch, authenticated by ref_code)
Route::middleware('throttle:10,1')
    ->get('/router-certs/{ref_code}/{file}', [\App\Http\Controllers\Admin\RouterController::class, 'serveCertFile'])
    ->where('file', 'ca\.crt|router\.crt|router\.key')
    ->name('api.router_cert');

// AI Assistant API (public, rate-limited)
Route::prefix('ai')->group(function () {
    Route::post('/chat', [AiAssistantController::class, 'chat'])->middleware('throttle:20,1');
    Route::post('/feedback', [AiAssistantController::class, 'feedback']);
});