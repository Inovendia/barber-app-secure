<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Admin\ReservationStatusController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Reserve\ReservationFormController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\Admin\AdminChangePasswordController;
use App\Http\Controllers\Admin\AdminController;


// ユーザー用
Route::get('/', fn() => view('welcome'));
Route::middleware(['auth', 'verified'])->get('/dashboard', fn() => view('dashboard'))->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// LINEミニアプリ予約関連
Route::get('/reserve', [ReservationFormController::class, 'create'])->name('reserve.form');
Route::post('/reserve', [ReservationFormController::class, 'store'])->name('reserve.store');
Route::get('/reserve/verify', [ReservationFormController::class, 'verify'])->name('reserve.verify');
Route::post('/reserve/confirmation', [ReservationFormController::class, 'showConfirmation'])->name('reserve.confirmation');
//Route::get('/reserve/complete', fn() => view('reserve.complete'))->name('reserve.complete');
Route::get('/reserve/calender', [ReservationFormController::class, 'calender'])->name('reserve.calender');
Route::get('/test-calender', fn() => view('reserve.calender'));
Route::post('/reserve/cancel', [ReservationFormController::class, 'cancel'])->name('reserve.cancel');

// routes/web.php
Route::get('/reserve/verify', [ReservationFormController::class, 'verify'])->name('reserve.verify');

// Webhook
Route::post('/webhook', [LineWebhookController::class, 'handle']);


// ------------------------
// 管理者認証不要エリア
// ------------------------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});


// ------------------------
// 管理者ログイン後のみアクセス可能
// ------------------------
Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/shop', [ShopController::class, 'edit'])->name('shop.edit');
    Route::post('/shop', [ShopController::class, 'update'])->name('shop.update');

    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');

    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::get('/reservations/calender', [ReservationController::class, 'calender'])->name('reservations.calender');

    Route::get('/admins/create', [AdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminController::class, 'store'])->name('admins.store');

    Route::get('/change-password', [AdminChangePasswordController::class, 'showForm'])->name('password.form');
    Route::post('/change-password', [AdminChangePasswordController::class, 'update'])->name('password.update');

    // ✅ 確認画面のための POST（セッションに保存）
    Route::post('/reservations/confirmation', [ReservationController::class, 'postConfirmation'])->name('reservations.confirmation.store');

    // ✅ 確認画面のための GET（セッションから表示）
    Route::get('/reservations/confirmation', [ReservationController::class, 'getConfirmation'])->name('reservations.confirmation');

    // ✅ 予約をDBに保存
    Route::post('/reservations/store', [ReservationController::class, 'store'])->name('reservations.store');

    // 予約ステータスの変更
    Route::patch('/reservations/{id}/status', [ReservationStatusController::class, 'update'])->name('reservations.updateStatus');

    Route::get('/customers/create', [App\Http\Controllers\Admin\CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [App\Http\Controllers\Admin\CustomerController::class, 'store'])->name('customers.store');

    Route::get('/notes/{type}/{id}', [App\Http\Controllers\Admin\NoteController::class, 'show'])->name('notes.show');
    Route::get('/notes/search', [App\Http\Controllers\Admin\NoteController::class, 'search'])->name('notes.search');

    Route::get('/calender-marks/create', [App\Http\Controllers\Admin\CalenderMarkController::class, 'create'])->name('calender_marks.create');
    Route::post('/calender-marks', [App\Http\Controllers\Admin\CalenderMarkController::class, 'store'])->name('calender_marks.store');

    Route::delete('/calender-marks', [App\Http\Controllers\Admin\CalenderMarkController::class, 'destroy'])->name('calender_marks.destroy');

    Route::post('/calender_marks/bulk', [CalenderMarkController::class, 'bulk'])->name('calender_marks.bulk');

});

