<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\DashboardController as IspDashboardController;
use App\Http\Controllers\Admin\RouterController;
use App\Http\Controllers\Admin\IspPackageController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\IspPaymentController;
use App\Http\Controllers\Admin\IspSettingController;
use App\Http\Controllers\Admin\IspResellerController;
use App\Http\Controllers\Admin\ConfigurationController;

Route::middleware(['is_installed'])->group(function () {
    
    // At the very top of routes/web.php, before everything else:
Route::get('/provision/{token}', [\App\Http\Controllers\Admin\RouterController::class, 'provision'])
    ->name('router.provision');

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::middleware('throttle:5,1')->post('login', [LoginController::class, 'login']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    Route::middleware(['auth:admin'])->group(function () {
        Route::redirect('/', '/admin/isp/dashboard')->name('home');
        Route::redirect('/dashboard', '/admin/isp/dashboard')->name('dashboard');

        Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/update', 'update')->name('update');
            Route::get('/change-password', 'showChangePasswordForm')->name('change_password_form');
            Route::post('/change-password', 'changePassword')->name('change_password');
        });

        // ISP Dashboard
        Route::get('/isp/dashboard', [IspDashboardController::class, 'index'])->name('isp.dashboard');

        // Routers
        Route::prefix('isp/routers')->name('isp.routers.')->controller(RouterController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{router}', 'show')->name('show');
            Route::get('/{router}/edit', 'edit')->name('edit');
            Route::put('/{router}', 'update')->name('update');
            Route::delete('/{router}', 'destroy')->name('destroy');
            Route::get('/{router}/script', 'script')->name('script');
            Route::get('/{router}/download-script', 'downloadScript')->name('download_script');
            Route::get('/{router}/provision-status', 'provisionStatus')->name('provision_status');
            Route::get('/{router}/hotspot-files', 'downloadHotspotFiles')->name('hotspot_files');
            Route::post('/{router}/test-connection', 'testConnection')->name('test_connection');
            Route::post('/{router}/configure', 'configureRouter')->name('configure');
            Route::post('/{router}/ping-status', 'pingStatus')->name('ping_status');  // <-- ADD THIS
        });

        // ISP Packages
        Route::prefix('isp/packages')->name('isp.packages.')->controller(IspPackageController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{ispPackage}/edit', 'edit')->name('edit');
            Route::put('/{ispPackage}', 'update')->name('update');
            Route::delete('/{ispPackage}', 'destroy')->name('destroy');
        });

        // Subscribers
        Route::prefix('isp/subscribers')->name('isp.subscribers.')->controller(SubscriberController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pppoe', 'pppoe')->name('pppoe');
            Route::get('/hotspot', 'hotspot')->name('hotspot');
            Route::post('/bulk', 'bulkAction')->name('bulk');
            Route::get('/{subscriber}', 'show')->name('show');
            Route::get('/{subscriber}/edit', 'edit')->name('edit');
            Route::put('/{subscriber}', 'update')->name('update');
            Route::delete('/{subscriber}', 'destroy')->name('destroy');
            Route::get('/{subscriber}/usage-data', 'usageData')->name('usage_data');
        });

        // Live Sessions
        Route::prefix('isp/sessions')->name('isp.sessions.')->controller(SessionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/disconnect', 'disconnect')->name('disconnect');
        });

        // ISP Payments
        Route::prefix('isp/payments')->name('isp.payments.')->controller(IspPaymentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/export', 'export')->name('export');
        });

        // ISP Settings
        Route::prefix('isp/settings')->name('isp.settings.')->controller(IspSettingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'update')->name('update');
        });

        Route::prefix('isp/configuration')->name('isp.configuration.')->controller(ConfigurationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/services-status', 'servicesStatus')->name('services_status');
            Route::post('/restart-service', 'restartService')->name('restart_service');
        });

        // Resellers
        Route::prefix('isp/resellers')->name('isp.resellers.')->controller(IspResellerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{reseller}/edit', 'edit')->name('edit');
            Route::put('/{reseller}', 'update')->name('update');
            Route::delete('/{reseller}', 'destroy')->name('destroy');
        });

        // Expired PPPoE
        Route::prefix('isp/expired-pppoe')->name('isp.expired_pppoe.')->controller(\App\Http\Controllers\Admin\ExpiredPppoeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'getData')->name('data');
        });

        // e-Receipts
        Route::prefix('isp/e-receipts')->name('isp.ereceipts.')->controller(\App\Http\Controllers\Admin\EReceiptController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'getData')->name('data');
        });

        // Reports
        Route::prefix('isp/reports')->name('isp.reports.')->controller(\App\Http\Controllers\Admin\ReportController::class)->group(function () {
            Route::get('/pppoe-sales', 'pppoeSales')->name('pppoe_sales');
            Route::get('/hotspot-sales', 'hotspotSales')->name('hotspot_sales');
            Route::get('/monthly-combined', 'monthlyCombined')->name('monthly_combined');
            Route::get('/sales-by-package', 'salesByPackage')->name('sales_by_package');
            Route::get('/revenue-summary', 'revenueSummary')->name('revenue_summary');
        });

        // Messaging
        Route::prefix('isp/messaging')->name('isp.messaging.')->controller(\App\Http\Controllers\Admin\MessagingController::class)->group(function () {
            Route::get('/sms', 'sms')->name('sms');
            Route::post('/sms/send', 'sendSms')->name('sms.send');
            Route::post('/sms/bulk', 'bulkSms')->name('sms.bulk');
            Route::get('/whatsapp', 'whatsapp')->name('whatsapp');
            Route::post('/whatsapp/send', 'sendWhatsapp')->name('whatsapp.send');
            Route::post('/whatsapp/bulk', 'bulkWhatsapp')->name('whatsapp.bulk');
            Route::get('/email', 'email')->name('email');
            Route::post('/email/send', 'sendEmail')->name('email.send');
            Route::post('/email/bulk', 'bulkEmail')->name('email.bulk');
            Route::get('/logs', 'logs')->name('logs');
        });

        // Expenses
        Route::prefix('isp/expenses')->name('isp.expenses.')->controller(\App\Http\Controllers\Admin\ExpenseController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/report', 'report')->name('report');
            Route::get('/export', 'export')->name('export');
            Route::get('/{expense}/edit', 'edit')->name('edit');
            Route::put('/{expense}', 'update')->name('update');
            Route::delete('/{expense}', 'destroy')->name('destroy');
        });

        // Expense Categories
        Route::prefix('isp/expense-categories')->name('isp.expense_categories.')->controller(\App\Http\Controllers\Admin\ExpenseCategoryController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{expenseCategory}/edit', 'edit')->name('edit');
            Route::put('/{expenseCategory}', 'update')->name('update');
            Route::delete('/{expenseCategory}', 'destroy')->name('destroy');
        });

        // Access Control - Roles
        Route::prefix('isp/access/roles')->name('isp.access.roles.')->controller(\App\Http\Controllers\Admin\RoleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{role}/edit', 'edit')->name('edit');
            Route::put('/{role}', 'update')->name('update');
            Route::delete('/{role}', 'destroy')->name('destroy');
        });

        // Access Control - Workers
        Route::prefix('isp/access/users')->name('isp.access.users.')->controller(\App\Http\Controllers\Admin\WorkerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{worker}/edit', 'edit')->name('edit');
            Route::put('/{worker}', 'update')->name('update');
            Route::delete('/{worker}', 'destroy')->name('destroy');
        });

        // Maps
        Route::prefix('isp/maps')->name('isp.maps.')->controller(\App\Http\Controllers\Admin\MapController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::post('/locations', 'storeLocation')->name('locations.store');
            Route::put('/locations/{location}', 'updateLocation')->name('locations.update');
            Route::delete('/locations/{location}', 'destroyLocation')->name('locations.destroy');
        });

        // MikroTik Monitor
        Route::prefix('isp/mikrotik-monitor')->name('isp.mikrotik_monitor.')->controller(\App\Http\Controllers\Admin\MikrotikMonitorController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{router}', 'show')->name('show');
            Route::get('/{router}/data', 'getData')->name('data');
        });

        // Global router status API (used by navbar polling)
        Route::get('routers/status', [\App\Http\Controllers\Admin\MikrotikMonitorController::class, 'routerStatuses'])->name('routers.status');

        // ── Phase 3: SaaS Enhancements ────────────────────────────────────────

        // Notifications
        Route::prefix('notifications')->name('notifications.')->controller(\App\Http\Controllers\Tenant\NotificationController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/count', 'count')->name('count');
            Route::post('/mark-all-read', 'markAllAsRead')->name('mark-all-read');
            Route::post('/{id}/mark-read', 'markAsRead')->name('mark-read');
        });

        // Support Chat
        Route::prefix('support-chat')->name('support-chat.')->controller(\App\Http\Controllers\Tenant\SupportChatController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/messages', 'getMessages')->name('messages');
        });

        // Pricing / Plans
        Route::get('pricing', [\App\Http\Controllers\Tenant\PricingController::class, 'currentPlan'])->name('pricing.index');

        // Tour completion tracking
        Route::post('tour/complete', function () {
            $admin = auth('admin')->user();
            if ($admin) {
                try {
                    $admin->update(['tour_completed' => true]);
                } catch (\Exception $e) {
                    // Gracefully skip if the column doesn't exist yet (pending migration)
                    \Illuminate\Support\Facades\Log::warning('tour/complete: could not update tour_completed column.', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            return response()->json(['success' => true]);
        })->name('tour.complete');

    });
});
