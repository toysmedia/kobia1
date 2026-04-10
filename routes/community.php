<?php

use App\Http\Controllers\Community\AuthController;
use App\Http\Controllers\Community\CategoryController;
use App\Http\Controllers\Community\DashboardController;
use App\Http\Controllers\Community\FollowController;
use App\Http\Controllers\Community\LikeController;
use App\Http\Controllers\Community\NotificationsController;
use App\Http\Controllers\Community\PostController;
use App\Http\Controllers\Community\ProfileController;
use App\Http\Controllers\Community\ReplyController;
use App\Http\Controllers\Community\ReportController;
use App\Http\Controllers\Community\SearchController;
use App\Http\Controllers\Community\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Community Portal Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::get('/', [DashboardController::class, 'index'])->name('community.index');

Route::middleware('guest:community')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('community.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,60')->name('community.login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('community.register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,60')->name('community.register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('community.password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('community.password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('community.password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('community.password.update');
});

Route::get('/verify-email', [AuthController::class, 'verificationNotice'])->name('community.verification.notice');

// Public read-only routes
Route::get('/posts', [PostController::class, 'index'])->name('community.posts.index');
Route::get('/categories', [CategoryController::class, 'index'])->name('community.categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('community.categories.show');
Route::get('/tags', [TagController::class, 'index'])->name('community.tags.index');
Route::get('/tags/{slug}', [TagController::class, 'show'])->name('community.tags.show');
Route::get('/search', [SearchController::class, 'index'])->name('community.search');
Route::get('/users/{id}', [ProfileController::class, 'show'])->name('community.profile.show');

// Authenticated routes (MUST come before /posts/{slug} to avoid conflict)
Route::middleware(['community.auth', 'community.not-banned'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('community.logout');
    Route::get('/posts/create', [PostController::class, 'create'])->name('community.posts.create');
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:10,60')->name('community.posts.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('community.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('community.profile.update');
    Route::post('/like', [LikeController::class, 'toggle'])->name('community.like');
    Route::post('/follow', [FollowController::class, 'toggle'])->name('community.follow');
    Route::post('/report', [ReportController::class, 'store'])->middleware('throttle:5,60')->name('community.report');
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('community.notifications');
});

// Post show/edit/delete (after create route)
Route::get('/posts/{slug}', [PostController::class, 'show'])->name('community.posts.show');
Route::middleware(['community.auth', 'community.not-banned'])->group(function () {
    Route::get('/posts/{slug}/edit', [PostController::class, 'edit'])->name('community.posts.edit');
    Route::put('/posts/{slug}', [PostController::class, 'update'])->name('community.posts.update');
    Route::delete('/posts/{slug}', [PostController::class, 'destroy'])->name('community.posts.destroy');
    Route::post('/posts/{post}/replies', [ReplyController::class, 'store'])->middleware('throttle:30,60')->name('community.replies.store');
    Route::put('/replies/{reply}', [ReplyController::class, 'update'])->name('community.replies.update');
    Route::delete('/replies/{reply}', [ReplyController::class, 'destroy'])->name('community.replies.destroy');
    Route::post('/replies/{reply}/accept', [ReplyController::class, 'acceptAnswer'])->name('community.replies.accept');
});
