<?php

namespace App\Http\Controllers\Exam\Auth;

use App\Http\Controllers\Controller;
use App\Models\CourseStudent;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamCredential;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Symfony\Component\Clock\now;

class ExamAuthController extends Controller
{
    public function showLogin()
    {
        return view('students.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'nim' => 'required'
        ]);

        try {

            $credential = ExamCredential::where('username', $request->username)->first();

            if (!$credential || !Hash::check($request->password, $credential->password)) {
                return back()->with('error', 'Username atau password salah');
            }

            $exam = Exam::findOrFail($credential->exam_id);

            if ($exam->status === 'upcoming') {
                return back()->with('error', 'Ujian belum dimulai');
            }

            if ($exam->status === 'ended') {
                return back()->with('error', 'Ujian sudah berakhir');
            }

            $student = Student::with('user')
                ->where('nim', $request->nim)
                ->first();

            if (!$student) {
                return back()->with('error', 'NIM tidak ditemukan.');
            }

            $user = $student->user;

            $nimExists = ExamCredential::where('exam_id', $credential->exam_id)
                ->where('nim', $student->nim)
                ->where('id', '!=', $credential->id)
                ->exists();

            if ($nimExists) {
                return back()->with('error', 'Anda sudah memiliki credential untuk ujian ini.');
            }

            if ($credential->nim && $credential->nim !== $student->nim) {
                return back()->with('error', 'Credential sudah digunakan oleh mahasiswa lain.');
            }

            $isRegistered = CourseStudent::where('course_id', $exam->course_id)
                ->where('student_id', $student->id)
                ->exists();

            if (!$isRegistered) {
                return back()->with('error', 'Anda tidak terdaftar pada blok ini.');
            }

            $attempt = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->first();

            if ($attempt) {

                if ($attempt->status === 'completed' || $attempt->status === 'timeout') {
                    if ($attempt->credential_id !== $credential->id) {
                        return back()->with('error', 'Anda sudah menyelesaikan ujian ini menggunakan token yang berbeda.');
                    }
                    return back()->with('error', 'Anda sudah menyelesaikan ujian ini.');
                }

                if ($attempt->status === 'in_progress' && $attempt->credential_id !== $credential->id) {
                    return back()->with('error', 'Anda masih memiliki ujian yang sedang berlangsung.');
                }
            }

            // simpan sementara
            session([
                'exam_credential_id' => $credential->id,
                'exam_exam_id' => $credential->exam_id,
                'exam_nim_temp' => $student->nim
            ]);

            return redirect()->route('exam.identity');
        } catch (\Exception $e) {

            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function identity()
    {
        if (!session()->has('exam_credential_id')) {
            return redirect()->route('exam.login');
        }
        $credential = ExamCredential::findOrFail(session('exam_credential_id'));
        if ($credential->nim) {
            $student = Student::where('nim', $credential->nim)->first();
        } else {
            $student = Student::where('nim', session('exam_nim_temp'))->first();
        }
        return view('students.auth.identity', compact('credential', 'student'));
    }

    public function start(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!session()->has('exam_credential_id') || !session()->has('exam_nim_temp')) {
            return redirect()->route('exam.examLogin');
        }

        if ($request->password !== $exam->password) {
            return back()->with('error', 'Password ujian salah.');
        }

        DB::beginTransaction();

        try {
            $credential = ExamCredential::where('id', session('exam_credential_id'))
                ->where('exam_id', $exam->id)
                ->lockForUpdate()
                ->first();

            if (!$credential) {
                DB::rollBack();
                session()->forget([
                    'exam_credential_id',
                    'exam_exam_id',
                    'exam_nim_temp'
                ]);

                return redirect()
                    ->route('exam.examLogin')
                    ->with('error', 'Credential tidak valid.');
            }

            $nim = session('exam_nim_temp');
            $nimExists = ExamCredential::where('exam_id', $exam->id)
                ->where('nim', $nim)
                ->where('id', '!=', $credential->id)
                ->exists();

            if ($nimExists) {
                DB::rollBack();
                return redirect()->route('exam.examLogin')
                    ->with('error', 'NIM sudah memiliki credential lain.');
            }

            // kunci credential ke nim
            if (!$credential->nim) {
                $credential->update([
                    'nim' => $nim,
                    'is_used' => true,
                    'used_at' => now()
                ]);
            }

            $student = Student::where('nim', $nim)->first();
            $user = User::findOrFail($student->user_id);

            // Lock attempt untuk hindari double create
            $attempt = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->lockForUpdate()
                ->first();

            if (!$attempt) {
                $questionOrder = $exam->questions
                    ->pluck('kode_soal')
                    ->shuffle()
                    ->values();

                $attempt = ExamAttempt::create([
                    'user_id' => $user->id,
                    'exam_id' => $exam->id,
                    'credential_id' => $credential->id,
                    'status' => 'in_progress',
                    'question_order' => $questionOrder->toJson(),
                    'started_at' => now(),
                ]);
            }

            DB::commit();

            Auth::login($user);

            session([
                'exam_nim' => $nim,
                'context' => strtolower($student->type)
            ]);

            session()->forget('exam_nim_temp');

            return redirect()->route('student.exams.do', $exam->exam_code);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai ujian. Silakan coba lagi.');
        }
    }

    public function logout()
    {
        $success = session('success');
        $info = session('info');
        $error = session('error');

        Auth::logout();

        session()->forget([
            'exam_credential_id',
            'exam_exam_id',
            'exam_nim',
            'exam_nim_temp'
        ]);

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('exam.examLogin')->with([
            'success' => $success,
            'info' => $info,
            'error' => $error,
        ]);
    }
}
