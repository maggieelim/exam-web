<?php

use App\Http\Controllers\AttendanceSessionsController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ==================== CONTEXT SWITCH ====================
Route::get('/set-context/{type}', function ($type) {
    if (!in_array($type, ['pssk', 'pspd'])) abort(404);
    session(['context' => $type]);
    return redirect($type . '/dashboard');
})->name('set.context');

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'role:admin', 'context'])->group(function () {

    // PSSK ADMIN
    Route::prefix('pssk/admin')->middleware('context:pssk')->name('pssk.admin.')->group(function () {
        Route::prefix('users/{type}')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'indexAdmin'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::get('download-template', [UserController::class, 'downloadTemplate'])->name('download-template');
            Route::get('export', [UserController::class, 'export'])->name('export');
            Route::post('store', [UserController::class, 'store'])->name('store');
            Route::post('import', [UserController::class, 'import'])->name('import');
            Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [UserController::class, 'update'])->name('update');
            Route::get('{id}', [UserController::class, 'show'])->name('show');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
        });
        Route::get('schedules', [AttendanceSessionsController::class, 'lecturersSchedule'])->name('schedules');
        Route::resource('semester', SemesterController::class);
    });

    // PSPD ADMIN
    Route::prefix('pspd/admin')->middleware('context:pspd')->name('pspd.admin.')->group(function () {
        Route::prefix('users/{type}')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'indexAdmin'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::get('download-template', [UserController::class, 'downloadTemplate'])->name('download-template');
            Route::get('export', [UserController::class, 'export'])->name('export');
            Route::post('store', [UserController::class, 'store'])->name('store');
            Route::post('import', [UserController::class, 'import'])->name('import');
            Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit');
            Route::post('update/{id}', [UserController::class, 'update'])->name('update');
            Route::get('{id}', [UserController::class, 'show'])->name('show');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
        });

        Route::resource('semester', SemesterController::class);
    });
});

// ==================== AUTH & DASHBOARD ====================
Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'home']);

    Route::get('static-sign-in', fn() => view('static-sign-in'))->name('sign-in');
    Route::get('static-sign-up', fn() => view('static-sign-up'))->name('sign-up');

    Route::get('logout', [SessionsController::class, 'destroy']);
    Route::get('user-profile', [InfoUserController::class, 'create']);
    Route::post('user-profile', [InfoUserController::class, 'store']);
});

Route::middleware('guest')->group(function () {
    Route::controller(RegisterController::class)->group(function () {
        Route::get('register', 'create');
        Route::post('register', 'store');
    });

    Route::controller(SessionsController::class)->group(function () {
        Route::get('login', 'create');
        Route::post('session', 'store');
    });

    Route::controller(ResetController::class)->group(function () {
        Route::get('login/forgot-password', 'create')->name('password.request');
        Route::post('forgot-password', 'sendEmail')->name('password.email');
        Route::get('reset-password/{token}', 'resetPass')->name('password.reset');
    });

    Route::post('reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});

Route::view('login', 'session/login-session')->name('login');
