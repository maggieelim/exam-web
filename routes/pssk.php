<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PSSK\{
    CourseController,
    CourseStudentController,
    CourseLecturerController,
    CourseScheduleController,
    CoursePemicuController,
    CoursePlenoController,
    CoursePracticumController,
    CourseSkillsLabController,
    GroupController,
    ExamController,
    ExamAttemptController,
    ExamQuestionController,
    ExamResultsController,
    OngoingExamController,
    StudentExamResultsController,
    TutorGradingController
};
use App\Http\Controllers\{
    AttendanceSessionsController,
    AttendanceReportController,
    HomeController,
    StudentAttendanceController,
};

Route::middleware(['auth', 'context:pssk'])->prefix('pssk')->group(function () {
    Route::get('/', [HomeController::class, 'home']);
    Route::get('dashboard', [HomeController::class, 'home'])->name('dashboard.pssk');

    Route::middleware(['role:admin,koordinator'])->name('admin.')
        ->group(function () {
            /* ----- Course Students & Groups ----- */
            Route::get('/courses/students', [CourseStudentController::class, 'index'])->name('courses.indexStudent');
            Route::post('/courses/{slug}/add-student', [CourseStudentController::class, 'store'])->name('courses.addStudent');
            Route::delete('/courses/{course:slug}/student/{studentId}', [CourseStudentController::class, 'destroy'])->name('courses.student.destroy');

            Route::get('/courses/{course}/bentuk_kelompok', [CourseStudentController::class, 'createKelompok'])->name('courses.createKelompok');
            Route::post('/courses/{course}/bentuk_kelompok', [CourseStudentController::class, 'updateKelompok'])->name('courses.updateKelompok');
            Route::post('/courses/{slug}/update-kelompok-manual', [CourseStudentController::class, 'updateKelompokManual'])->name('courses.updateKelompokManual');

            Route::get('/courses/{course}/bentuk_group', [CourseStudentController::class, 'createGroup'])->name('courses.createGroup');
            Route::post('/courses/{course}/bentuk_group', [GroupController::class, 'updateGroup'])->name('courses.updateGroup');

            /* ----- Lecturers ----- */
            Route::post('/courses/{course}/updateLecturer', [CourseLecturerController::class, 'update'])->name('courses.updateLecturer');
            Route::get('/course/{course}/addLecturer', [CourseLecturerController::class, 'edit'])->name('courses.addLecturer');
            Route::post('/course/{course}/assignLecturer', [CourseLecturerController::class, 'addLecturer'])->name('courses.assignLecturer');
            Route::get('/course/{course}/get-lecturers', [CourseLecturerController::class, 'getLecturersByActivity'])->name('courses.get-lecturers-by-activity');

            /* ----- Course Schedules ----- */
            Route::get('/course', [CourseScheduleController::class, 'index'])->name('course.index');
            Route::get('/course/{course}/schedule/create', [CourseScheduleController::class, 'create'])->name('course.create');
            Route::post('/course/{course}/schedule', [CourseScheduleController::class, 'store'])->name('course.store');
            Route::get('/course/schedule/{schedule}', [CourseScheduleController::class, 'show'])->name('course.show');
            Route::post('/course/schedule/{schedule}/update-schedules', [CourseScheduleController::class, 'updateSchedules'])->name('course.updateSchedules');
            Route::delete('/course/schedules/{id}', [CourseScheduleController::class, 'destroy'])->name('course.destroySchedules');

            Route::post('/course/schedule/praktikum', [CoursePracticumController::class, 'update'])->name('course.assignPracticum');
            Route::post('/course/schedule/pemicu', [CoursePemicuController::class, 'update'])->name('course.assignPemicu');
            Route::post('/course/schedule/pleno', [CoursePlenoController::class, 'update'])->name('course.assignPleno');
            Route::post('/course/schedule/skillLab', [CourseSkillsLabController::class, 'update'])->name('course.assignSkillLab');

            /* ----- Download ----- */
            Route::prefix('/courses/{course}/semester/{semesterId}')
                ->group(function () {
                    Route::get('/download-practicum-assignment', [CoursePracticumController::class, 'downloadExcel'])->name('courses.downloadPracticumAssignment');
                    Route::get('/download-daftarSiswa', [CourseStudentController::class, 'downloadExcel'])->name('courses.downloadDaftarSiswa');
                    Route::get('/download-pemicu', [CoursePemicuController::class, 'downloadExcel'])->name('courses.downloadPemicu');
                    Route::get('/download-pleno', [CoursePlenoController::class, 'downloadExcel'])->name('courses.downloadPleno');
                    Route::get('/download-skillsLab', [CourseSkillsLabController::class, 'downloadExcel'])->name('courses.downloadSkillsLab');
                    Route::get('/download-kelas', [CourseScheduleController::class, 'downloadExcel'])->name('courses.downloadPerkuliahan');
                });
        });

    /* ================= KOORDINATOR (EXAM RESULTS) ================= */
    Route::middleware(['role:koordinator'])
        ->name('lecturer.')
        ->group(function () {
            Route::get('/{status?}', [ExamResultsController::class, 'indexLecturer'])->where('status', '(ungraded|graded|published)')->name('results.index');
            Route::prefix('/{exam_code}')
                ->group(function () {
                    Route::post('/publish', [ExamResultsController::class, 'publish'])->name('results.publish');
                    Route::get('/download', [ExamResultsController::class, 'download'])->name('results.download');
                    Route::get('/questions/download', [ExamResultsController::class, 'downloadQuestions'])->name('results.downloadQuestions');
                });
            Route::get('/exams/previous/published/{exam_code}', [ExamResultsController::class, 'grade'])->name('grade.published');
            Route::get('/exams/previous/ungraded/{exam_code}', [ExamResultsController::class, 'grade'])->name('grade.ungraded');
            Route::get('/exams/previous/published/analytics/{exam_code}', [ExamResultsController::class, 'show'])->name('results.show.published');
            Route::get('/exams/previous/ungraded/analytics/{exam_code}', [ExamResultsController::class, 'show'])->name('results.show.ungraded');
            Route::get('/exams/previous/published/{exam_code}/{nim}', [ExamResultsController::class, 'edit'])->name('feedback.published');
            Route::get('/exams/previous/ungraded/{exam_code}/{nim}', [ExamResultsController::class, 'edit'])->name('feedback.ungraded');
            Route::post('/exams/previous/{exam_code}/{nim}/feedback', [ExamResultsController::class, 'update'])->name('feedback.update');
        });

    // ================= SHARED ADMIN & LECTURER =================
    Route::middleware(['role:admin,lecturer,koordinator'])->group(function () {
        Route::prefix('tutors')->group(function () {
            Route::get('/', [TutorGradingController::class, 'index'])->name('tutors');
            Route::get('/{course}/{kelompok}', [TutorGradingController::class, 'show'])->name('tutors.detail');
            Route::get('/{pemicu}/{student}/edit', [TutorGradingController::class, 'edit'])->name('tutors.edit');
            Route::post('/{pemicu}/{student}', [TutorGradingController::class, 'update'])->name('tutors.update');
            Route::get('/{course}/{kelompok}/download', [TutorGradingController::class, 'downloadExcel'])->name('tutors.download');
        });

        Route::get('/course/pemicu/nilai/{id}', [CoursePemicuController::class, 'allPemicu'])->name('course.getAllPemicu');
        Route::get(
            '/course/pemicu/nilai/{id}/{semester}/downloadAll',
            [CoursePemicuController::class, 'downloadAllPemicu']
        )->name('course.downloadAllPemicu');
        Route::get('/course/pemicu/nilai/{id1}/{id2}', [CoursePemicuController::class, 'nilai'])->name('course.nilaiPemicu');
        Route::get('/course/pemicu/nilai/{id1}/{id2}/download', [CoursePemicuController::class, 'downloadPemicu'])->name('course.downloadNilai');
        //attendance
        Route::resource('attendance', AttendanceSessionsController::class);
        Route::get('/attendance/{attendanceCode}/qr-code', [AttendanceSessionsController::class, 'getQrCode']);
        Route::get('/attendances/json', [AttendanceSessionsController::class, 'getEvents'])->name('attendances.json');

        Route::get('/attendances/report', [AttendanceReportController::class, 'indexLecturer'])->name('attendances.reportLecturer');
        Route::get('/courses/attendances/report/{course}', [AttendanceReportController::class, 'index'])->name('attendances.report');
        Route::get('/courses/attendances/report/{course}/{session}', [AttendanceReportController::class, 'show'])->name('attendances.report.show');
        Route::get('/courses/export/attendances/report/{course}/{session}', [AttendanceReportController::class, 'exportAttendanceReport'])->name('attendances.report.export');

        // courses
        Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/{course}/download', [CourseController::class, 'download'])->name('courses.download');
        Route::get('/courses/export', [CourseController::class, 'export'])->name('courses.export');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses/store', [CourseController::class, 'store'])->name('courses.store');
        Route::post('/courses/import', [CourseController::class, 'import'])->name('courses.import');
        Route::get('/courses/edit/{course}', [CourseController::class, 'edit'])->name('courses.edit');
        Route::get('/courses/edit_koordinator/{course}', [CourseController::class, 'editKoor'])->name('courses.editKoor');
        Route::post('/courses/edit_koordinator/{course}', [CourseController::class, 'updateKoor'])->name('courses.updateKoor');
        Route::post('/courses/update/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('/courses/students/edit/{slug}', [CourseStudentController::class, 'edit'])->name('courses.editStudent');

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
        Route::post('exams/new-question/{exam_code}', [ExamQuestionController::class, 'newQuestionModal'])->name('exams.newQuestion');
        Route::put('exams/{exam_code}/questions/{question}', [ExamQuestionController::class, 'update'])->name('exams.questions.update');
        Route::post('exams/{exam_code}/questions/update-excel', [ExamQuestionController::class, 'updateByExcel'])->name('exams.questions.updateByExcel');
        Route::post('exams/{exam_code}/questions/update-word', [ExamQuestionController::class, 'updateByWord'])->name('exams.questions.updateByWord');
        Route::delete('/exams/{examCode}/questions/{question}', [ExamQuestionController::class, 'destroy'])->name('exams.questions.destroy');
        Route::get('/exams/{examCode}/questions/download', [ExamQuestionController::class, 'export'])->name('exams.questions.download');
    });

    // ================= STUDENT =================
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
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
        Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/{attendanceSession}', [StudentAttendanceController::class, 'showAttendanceForm'])->name('attendance.form');
        Route::post('/attendance/{attendanceSession}', [StudentAttendanceController::class, 'submitAttendance'])->name('attendance.submit');
    });
});
