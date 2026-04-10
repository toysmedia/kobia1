<?php

use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerPackageController;
use App\Http\Controllers\Customer\PaymentController as CustomerPaymentController;
use App\Http\Controllers\Customer\CustomerSupportController;
use App\Http\Controllers\Customer\CustomerProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
|
| Routes for the PPPoE customer self-service portal.
| Accessed at {subdomain}.oxnet.co.ke/customer/
|
*/

Route::middleware(['customer.portal.enabled'])->group(function () {
    // Guest routes
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:5,1')->name('customer.login.submit');
        Route::get('/register', [CustomerAuthController::class, 'showRegistrationForm'])->name('customer.register');
        Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:3,1')->name('customer.register.submit');
        Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('customer.password.request');
        Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLink'])->name('customer.password.email');
        Route::get('/reset-password/{token}', [CustomerAuthController::class, 'showResetForm'])->name('customer.password.reset');
        Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])->name('customer.password.update');
    });

    // Authenticated routes
    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

        // Routes accessible even for expired/suspended subscribers
        Route::get('/payments/renew', [CustomerPaymentController::class, 'renew'])->name('customer.payments.renew');
        Route::post('/payments/renew', [CustomerPaymentController::class, 'processRenewal'])->name('customer.payments.process');

        // Routes requiring an active subscription
        Route::middleware('customer.active')->group(function () {
            Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

            Route::get('/package', [CustomerPackageController::class, 'index'])->name('customer.package.index');
            Route::post('/package/change', [CustomerPackageController::class, 'requestChange'])->name('customer.package.change');

            Route::get('/payments', [CustomerPaymentController::class, 'index'])->name('customer.payments.index');
            Route::get('/payments/{id}/receipt', [CustomerPaymentController::class, 'receipt'])->name('customer.payments.receipt');

            Route::get('/support', [CustomerSupportController::class, 'index'])->name('customer.support.index');
            Route::get('/support/create', [CustomerSupportController::class, 'create'])->name('customer.support.create');
            Route::post('/support', [CustomerSupportController::class, 'store'])->name('customer.support.store');
            Route::get('/support/{id}', [CustomerSupportController::class, 'show'])->name('customer.support.show');
            Route::post('/support/{id}/reply', [CustomerSupportController::class, 'reply'])->name('customer.support.reply');

            Route::get('/profile', [CustomerProfileController::class, 'index'])->name('customer.profile.index');
            Route::post('/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
            Route::post('/profile/password', [CustomerProfileController::class, 'changePassword'])->name('customer.profile.password');
        });
    });
});
