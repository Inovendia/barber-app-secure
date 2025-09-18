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
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

// ユーザー用
Route::get('/', fn() => view('welcome'));
Route::middleware(['auth', 'verified'])->get('/dashboard', fn() => view('dashboard'))->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//top用
Route::get('/', function () {
    return view('top');
});

Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/support', 'support')->name('support');

//

require __DIR__.'/auth.php';

// LINEミニアプリ予約関連（店舗別予約URL対応）
Route::prefix('reserve/{token}')->name('reserve.')->group(function () {
    Route::get('/form', [ReservationFormController::class, 'create'])->name('form');
    Route::post('/calendar', [ReservationFormController::class, 'calender'])->name('calender');
    Route::post('/confirmation', [ReservationFormController::class, 'showConfirmation'])->name('confirmation');
    Route::post('/complete', [ReservationFormController::class, 'store'])->name('store');
    Route::post('/resolve', [ReservationFormController::class, 'resolve'])->name('resolve');
    Route::get('/confirm', [ReservationFormController::class, 'confirm'])->name('confirm');
});

// 共通処理（予約確認・キャンセルなど）
Route::get('/reserve/verify', [ReservationFormController::class, 'verify'])->name('reserve.verify');
Route::post('/reserve/cancel', [ReservationFormController::class, 'cancel'])->name('reserve.cancel');
Route::get('/reserve/form-entry', [ReservationFormController::class, 'entry'])->name('reserve.entry');

// テストビュー（任意）
Route::get('/test-calender', fn() => view('reserve.calender'));

// LINE Webhook
Route::post('/webhook', [LineWebhookController::class, 'handle']);

// token認証用
Route::get('/reserve/my', [ReservationFormController::class, 'my'])->name('reserve.my');
//Route::post('/reserve/resolve', [ReservationFormController::class, 'resolve'])->name('reserve.resolve');


// ------------------------
// 管理者認証不要エリア
// ------------------------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

//line userID取得
//Route::post('/line/webhook', function (Request $request) {
//    Log::debug('LINE Webhook event: ' . json_encode($request->all(), JSON_UNESCAPED_UNICODE));
//    return response()->json(['status' => 'ok']);
//});



// ------------------------
// 管理者ログイン後のみアクセス可能
// ------------------------
Route::prefix('admin')->middleware('auth:admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancel'])
    ->name('reservations.cancel');

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

    Route::post('/calender_marks', [App\Http\Controllers\Admin\CalenderMarkController::class, 'bulk'])->name('calender_marks.bulk');

});

