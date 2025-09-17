<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseStudent;
use App\Http\Controllers\CourseStudentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => 'auth'], function () {

	Route::get('/', [HomeController::class, 'home']);
	Route::get('dashboard', function () {
		return view('dashboard');
	})->name('dashboard');

	Route::get('billing', function () {
		return view('billing');
	})->name('billing');


	Route::get('rtl', function () {
		return view('rtl');
	})->name('rtl');

	Route::get('user-management', function () {
		return view('laravel-examples/user-management');
	})->name('user-management');

	Route::get('tables', function () {
		return view('tables');
	})->name('tables');

	Route::get('virtual-reality', function () {
		return view('virtual-reality');
	})->name('virtual-reality');

	Route::get('static-sign-in', function () {
		return view('static-sign-in');
	})->name('sign-in');

	Route::get('static-sign-up', function () {
		return view('static-sign-up');
	})->name('sign-up');

	Route::get('/logout', [SessionsController::class, 'destroy']);
	Route::get('/user-profile', [InfoUserController::class, 'create']);
	Route::post('/user-profile', [InfoUserController::class, 'store']);
	Route::get('/login', function () {
		return view('dashboard');
	})->name('sign-up');
});


Route::get('profile', [ProfileController::class, 'index'])->name('profile');

Route::group(['middleware' => 'guest'], function () {
	Route::get('/register', [RegisterController::class, 'create']);
	Route::post('/register', [RegisterController::class, 'store']);
	Route::get('/login', [SessionsController::class, 'create']);
	Route::post('/session', [SessionsController::class, 'store']);
	Route::get('/login/forgot-password', [ResetController::class, 'create']);
	Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
	Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
	Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});

// ================= ADMIN =================
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
	Route::get('/users/{type?}', [UserController::class, 'indexAdmin'])
		->name('users.index');
	Route::get('/users/{type}/create', [UserController::class, 'create'])->name('users.create');
	Route::post('/users/{type}/store', [UserController::class, 'store'])->name('users.store');
	Route::post('/users/{type}/import', [UserController::class, 'import'])->name('users.import');
	Route::get('/users/{type}/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
	Route::post('/users/{type}/update/{id}', [UserController::class, 'update'])->name('users.update');
	Route::get('/users/{type}/{id}', [UserController::class, 'show'])->name('users.show');
	Route::delete('/users/{type}/{id}', [UserController::class, 'destroy'])->name('users.destroy');



	Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
	Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
	Route::post('/courses/store', [CourseController::class, 'store'])->name('courses.store');
	Route::post('/courses/import', [CourseController::class, 'import'])->name('courses.import');
	Route::get('/courses/edit/{course}', [CourseController::class, 'edit'])->name('courses.edit');
	Route::put('/courses/update/{course}', [CourseController::class, 'update'])->name('courses.update');
	Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

	Route::get('/course/students', [CourseStudentController::class, 'index'])->name('courses.indexStudent');
	Route::get('/course/students/edit/{slug}', [CourseStudentController::class, 'edit'])->name('courses.editStudent');
	Route::post('/course/{slug}/add-student', [CourseStudentController::class, 'store'])->name('courses.addStudent');

	Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
	Route::view('/reports', 'admin.reports.index')->name('reports');
});

// ================= LECTURER =================
Route::middleware(['auth'])->prefix('lecturer')->name('lecturer.')->group(function () {
	Route::view('/exams', 'lecturer.exams.index')->name('exams');
	Route::view('/questions', 'lecturer.questions.index')->name('questions');
	Route::view('/exams', 'lecturer.exams.index')->name('exams');
	Route::view('/grading', 'lecturer.grading.index')->name('grading');
	Route::view('/reports', 'lecturer.reports.index')->name('reports');
	Route::get('/students', [UserController::class, 'indexLecturer'])->name('students');
});

// ================= STUDENT =================
Route::middleware(['auth'])->prefix('student')->name('student.')->group(function () {
	Route::view('/exams', 'student.exams.index')->name('exams');
	Route::view('/history', 'student.history.index')->name('history');
	Route::view('/results', 'student.results.index')->name('results');
});

Route::get('/login', function () {
	return view('session/login-session');
})->name('login');
