<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PSPD\ClinicalRotationController as PSPDClinicalRotationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PSPD\HospitalController;
use App\Http\Controllers\PSPD\HospitalRotationController as PSPDHospitalRotationController;
use App\Http\Controllers\PSPD\LecturerKoasController;
use App\Http\Controllers\PSPd\LogbookController;
use App\Http\Controllers\PSPD\StudentKoasController;
use App\Http\Controllers\PSPd\StudentLogbookController;
use App\Http\Controllers\PSPD\StudentRotationController;

Route::middleware(['auth', 'context:pspd'])
    ->prefix('pspd')
    ->group(function () {
        Route::get('/', [HomeController::class, 'home']);
        Route::get('dashboard', [HomeController::class, 'home'])->name('dashboard.pspd');

        Route::middleware('role:admin')->group(function () {
            Route::resource('rumah-sakit', HospitalController::class);
            Route::resource('kepaniteraan', PSPDHospitalRotationController::class);
            Route::resource('stase', PSPDClinicalRotationController::class);
            Route::resource('mahasiswa-koas', StudentKoasController::class)->except(['create', 'destroy']);
            Route::get('kepaniteraan/{rotation}/assign', [StudentKoasController::class, 'create'])->name('mahasiswa-koas.assign');
            Route::delete('/mahasiswa-koas/{id}/{rotation}', [StudentKoasController::class, 'destroy'])->name('mahasiswa-koas.destroy');
            Route::resource('lecturer-koas', LecturerKoasController::class);
        });
        // routes/web.php
        Route::prefix('logbook/{status}')->group(function () {
            Route::get('/', [LogbookController::class, 'index'])->name('logbook.index');
            Route::get('/{logbook}/edit', [LogbookController::class, 'edit'])->name('logbook.edit');
            Route::put('/{logbook}', [LogbookController::class, 'update'])->name('logbook.update');
            Route::get('/{logbook}', [LogbookController::class, 'show'])->name('logbook.show');
        });
        Route::resource('student-logbook', StudentLogbookController::class);
        Route::resource('student-rotation', StudentRotationController::class);
    });
