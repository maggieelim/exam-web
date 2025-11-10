<?php

use App\Http\Controllers\AttendanceSessionsController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseLecturerController;
use App\Http\Controllers\CoursePemicuController;
use App\Http\Controllers\CoursePlenoController;
use App\Http\Controllers\CoursePracticumController;
use App\Http\Controllers\CourseScheduleController;
use App\Http\Controllers\CourseSkillsLabController;
use App\Http\Controllers\CourseStudentController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamQuestionController;
use App\Http\Controllers\ExamResultsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\OngoingExamController;
use App\Http\Controllers\PracticumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SkillslabController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentExamResultsController;

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

// ==================== AUTH & DASHBOARD ====================
Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'home']);
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::get('static-sign-in', fn() => view('static-sign-in'))->name('sign-in');
    Route::get('static-sign-up', fn() => view('static-sign-up'))->name('sign-up');

    Route::get('logout', [SessionsController::class, 'destroy']);
    Route::get('user-profile', [InfoUserController::class, 'create']);
    Route::post('user-profile', [InfoUserController::class, 'store']);
});

Route::get('profile', [ProfileController::class, 'index'])->name('profile');

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

// ================= ADMIN ONLY =================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('users/{type}')
            ->name('users.')
            ->group(function () {
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

        Route::get('/courses/students', [CourseStudentController::class, 'index'])->name('courses.indexStudent');
        Route::get('/courses/students/edit/{slug}', [CourseStudentController::class, 'edit'])->name('courses.editStudent');
        Route::post('/courses/{slug}/add-student', [CourseStudentController::class, 'store'])->name('courses.addStudent');
        Route::delete('/courses/{course:slug}/student/{studentId}', [CourseStudentController::class, 'destroy'])->name('courses.student.destroy');
        Route::get('/courses/{course}/bentuk_kelompok', [CourseStudentController::class, 'createKelompok'])->name('courses.createKelompok');
        Route::post('/courses/{course}/bentuk_kelompok', [CourseStudentController::class, 'updateKelompok'])->name('courses.updateKelompok');
        Route::post('/courses/{slug}/update-kelompok-manual', [CourseStudentController::class, 'updateKelompokManual'])->name('courses.updateKelompokManual');
        Route::get('/courses/{course}/bentuk_group', [CourseStudentController::class, 'createGroup'])->name('courses.createGroup');
        Route::post('/courses/{course}/bentuk_group', [GroupController::class, 'updateGroup'])->name('courses.updateGroup');
        Route::post('/courses/{course}/updateLecturer', [CourseLecturerController::class, 'update'])->name('courses.updateLecturer');
        Route::get('/course/{course}/addLecturer', [CourseLecturerController::class, 'edit'])->name('courses.addLecturer');
        Route::post('/course/{course}/assignLecturer', [CourseLecturerController::class, 'addLecturer'])->name('courses.assignLecturer');

        Route::get('/course/{course}/schedule/create', [CourseScheduleController::class, 'create'])->name('course.create');
        Route::post('/course/{course}/schedule', [CourseScheduleController::class, 'store'])->name('course.store');
        Route::get('/course', [CourseScheduleController::class, 'index'])->name('course.index');
        Route::get('/course/schedule/{schedule}', [CourseScheduleController::class, 'show'])->name('course.show');
        Route::post('/course/schedule/{schedule}/update-schedules', [CourseScheduleController::class, 'updateSchedules'])->name('course.updateSchedules');
        Route::delete('/course/schedules/{id}', [CourseScheduleController::class, 'destroy'])->name('course.destroySchedules');
        Route::post('/course/schedule/praktikum', [CoursePracticumController::class, 'update'])->name('course.assignPracticum');
        Route::post('/course/schedule/pemicu', [CoursePemicuController::class, 'update'])->name('course.assignPemicu');
        Route::post('/course/schedule/pleno', [CoursePlenoController::class, 'update'])->name('course.assignPleno');
        Route::post('/course/schedule/skillLab', [CourseSkillsLabController::class, 'update'])->name('course.assignSkillLab');
        Route::get('/course/{course}/get-lecturers', [CourseLecturerController::class, 'getLecturersByActivity'])
    ->name('courses.get-lecturers-by-activity');
    });

Route::middleware(['auth', 'role:lecturer,koordinator,admin'])
    ->prefix('lecturer')
    ->name('lecturer.')
    ->group(function () {
        Route::get('/{status?}', [ExamResultsController::class, 'indexLecturer'])
            ->where('status', '(ungraded|graded|published)')
            ->name('results.index');
        Route::get('/published/{exam_code}', [ExamResultsController::class, 'grade'])->name('grade.published');
        Route::get('/ungraded/{exam_code}', [ExamResultsController::class, 'grade'])->name('grade.ungraded');
        Route::get('/published/analytics/{exam_code}', [ExamResultsController::class, 'show'])->name('results.show.published');
        Route::get('/ungraded/analytics/{exam_code}', [ExamResultsController::class, 'show'])->name('results.show.ungraded');
        Route::post('/{exam_code}/publish', [ExamResultsController::class, 'publish'])->name('results.publish');
        Route::get('/published/{exam_code}/{nim}', [ExamResultsController::class, 'edit'])->name('feedback.published');
        Route::get('/ungraded/{exam_code}/{nim}', [ExamResultsController::class, 'edit'])->name('feedback.ungraded');
        Route::post('/{exam_code}/{nim}/feedback', [ExamResultsController::class, 'update'])->name('feedback.update');
        Route::get('/results/{exam_code}/download', [ExamResultsController::class, 'download'])->name('results.download');
        Route::get('/results/{exam_code}/quetions/download', [ExamResultsController::class, 'downloadQuestions'])->name('results.downloadQuestions');
    });

// ================= SHARED ADMIN & LECTURER =================
Route::middleware(['auth', 'role:admin,lecturer,koordinator'])->group(function () {
    //attendance
    Route::resource('attendance', AttendanceSessionsController::class);
    Route::get('/attendance/{attendanceCode}/qr-code', [AttendanceSessionsController::class, 'getQrCode']);
    // courses
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}/download', [CourseController::class, 'download'])->name('courses.download');
    Route::get('/courses/export', [CourseController::class, 'export'])->name('courses.export');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses/store', [CourseController::class, 'store'])->name('courses.store');
    Route::post('/courses/import', [CourseController::class, 'import'])->name('courses.import');
    Route::get('/courses/edit/{course}', [CourseController::class, 'edit'])->name('courses.edit');
    Route::post('/courses/update/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // exams
    Route::get('/exams/{status?}', [ExamController::class, 'index'])
        ->where('status', '(previous|upcoming|ongoing)')
        ->name('exams.index');
    Route::get('/exams/upcoming/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams/store', [ExamController::class, 'import'])->name('exams.import');
    Route::get('/exams/upcoming/{exam_code}', [ExamController::class, 'show'])->name('exams.show.upcoming');
    Route::get('/exams/ongoing/{exam_code}', [ExamController::class, 'show'])->name('exams.show.ongoing');
    Route::get('/exams/previous/{exam_code}', [ExamController::class, 'show'])->name('exams.show.previous');
    Route::get('/exams/{status}/edit/{exam_code}', [ExamController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{status}/update/{exam_code}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam_code}', [ExamController::class, 'destroy'])->name('exams.destroy');

    Route::put('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
    Route::put('/exams/{exam}/end', [ExamController::class, 'end'])->name('exams.end');

    Route::get('/exams/ongoing/participants/{exam_code}', [OngoingExamController::class, 'ongoing'])->name('exams.ongoing');
    Route::get('/exams/ongoing/{exam_code}/retake/{user_id}', [OngoingExamController::class, 'resetAttempt'])->name('exams.retake');
    Route::get('/exams/ongoing/{exam_code}/end/{user_id}', [OngoingExamController::class, 'endAttempt'])->name('exams.endAttempt');

    // exam questions
    Route::get('exams/upcoming/questions/{exam_code}', [ExamQuestionController::class, 'index'])->name('exams.questions.upcoming');
    Route::get('exams/ongoing/questions/{exam_code}', [ExamQuestionController::class, 'index'])->name('exams.questions.ongoing');
    Route::get('exams/previous/questions/{exam_code}', [ExamQuestionController::class, 'index'])->name('exams.questions.previous');
    Route::put('exams/{exam_code}/questions/{question}', [ExamQuestionController::class, 'update'])->name('exams.questions.update');
    Route::post('exams/{exam_code}/questions/update-excel', [ExamQuestionController::class, 'updateByExcel'])->name('exams.questions.updateByExcel');
    Route::delete('/exams/{examCode}/questions/{question}', [ExamQuestionController::class, 'destroy'])->name('exams.questions.destroy');
    Route::get('/exams/{examCode}/questions/download', [ExamQuestionController::class, 'export'])->name('exams.questions.download');
});

// ================= STUDENT =================
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/exams/{status?}', [ExamController::class, 'index'])
            ->where('status', '(previous|upcoming|ongoing)')
            ->name('studentExams.index');
        // Route untuk mengecek status exam
        Route::get('/exams/{exam_code}/check-status', [ExamAttemptController::class, 'checkExamStatus'])->name('exams.check-status');
        Route::get('/exams/previous/results/{exam_code}', [StudentExamResultsController::class, 'show'])->name('results.show');
        Route::post('/exams/{exam_code}/start', [ExamAttemptController::class, 'start'])->name('exams.start');
        Route::get('/exams/{exam_code}/{kode_soal?}', [ExamAttemptController::class, 'do'])->name('exams.do');
        Route::post('/exams/{exam_code}/{kode_soal}/answer', [ExamAttemptController::class, 'answer'])->name('exams.answer');
        Route::post('/exams/{exam_code}/finish', [ExamAttemptController::class, 'finish'])->name('exams.finish');

        Route::get('/results', [StudentExamResultsController::class, 'index'])->name('results.index');
        //attendance
        Route::get('/attendance/{attendanceSession}', [StudentAttendanceController::class, 'showAttendanceForm'])->name('attendance.form');
        Route::post('/attendance/{attendanceSession}', [StudentAttendanceController::class, 'submitAttendance'])->name('attendance.submit');
    });

Route::view('login', 'session/login-session')->name('login');
