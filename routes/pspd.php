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
            Route::get('kepaniteraan/{rotation}/assignLecturer', [LecturerKoasController::class, 'create'])->name('lecturer-koas.assign');
            Route::delete('/mahasiswa-koas/{id}/{rotation}', [StudentKoasController::class, 'destroy'])->name('mahasiswa-koas.destroy');
        });
        Route::resource('logbook', LogbookController::class);
        Route::resource('student-logbook', StudentLogbookController::class);
    });
