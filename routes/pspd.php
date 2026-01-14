<?php

use App\Http\Controllers\ClinicalRotationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HospitalRotationController;
use App\Http\Controllers\PSPD\ClinicalRotationController as PSPDClinicalRotationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PSPD\HospitalController;
use App\Http\Controllers\PSPD\HospitalRotationController as PSPDHospitalRotationController;
use App\Http\Controllers\PSPD\StudentKoasController;

Route::middleware(['auth', 'context:pspd'])
    ->prefix('pspd')
    ->group(function () {
        Route::get('/', [HomeController::class, 'home']);
        Route::get('dashboard', [HomeController::class, 'home'])->name('dashboard.pspd');

        Route::middleware('role:admin')->group(function () {
            Route::resource('rumah-sakit', HospitalController::class);
            Route::resource('kepaniteraan', PSPDHospitalRotationController::class);
            Route::resource('stase', PSPDClinicalRotationController::class);
            Route::resource('mahasiswa-koas', StudentKoasController::class);
        });
    });
