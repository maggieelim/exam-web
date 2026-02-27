<?php

use App\Http\Controllers\Exam\Auth\ExamAuthController;

Route::domain(env('EXAM_DOMAIN'))->group(function () {

    Route::middleware('exam.guest')->group(function () {
        Route::get('/login', [ExamAuthController::class, 'showLogin'])
            ->name('examLogin');

        Route::post('/login', [ExamAuthController::class, 'login'])
            ->name('examLogin.post');
    });

    Route::middleware('exam.auth')->group(function () {
        Route::get('/identity', [ExamAuthController::class, 'identity'])
            ->name('identity');

        Route::get('/logout', [ExamAuthController::class, 'logout'])
            ->name('logout');
    });
});
