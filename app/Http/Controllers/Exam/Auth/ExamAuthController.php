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

        DB::beginTransaction();

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

            $student = Student::where('nim', $request->nim)->first();

            if (!$student) {
                return back()->with('error', 'NIM tidak ditemukan.');
            }

            $user = User::findOrFail($student->user_id);

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
                ->lockForUpdate()
                ->first();

            if ($attempt) {

                if ($attempt->status === 'completed') {
                    if ($attempt->credential_id !== $credential->id) {
                        return back()->with('error', 'Anda sudah menyelesaikan ujian ini menggunakan token yang berbeda.');
                    }
                    return back()->with('error', 'Anda sudah menyelesaikan ujian ini.');
                }

                if ($attempt->status === 'in_progress' && $attempt->credential_id !== $credential->id) {
                    return back()->with('error', 'Anda masih memiliki ujian yang sedang berlangsung.');
                }
            }

            $credential->update([
                'nim' => $student->nim,
                'is_used' => true,
                'used_at' => now()
            ]);

            DB::commit();

            session([
                'exam_credential_id' => $credential->id,
                'exam_exam_id' => $credential->exam_id,
                'exam_nim' => $student->nim
            ]);

            return redirect()->route('exam.identity');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function identity()
    {
        if (!session()->has('exam_credential_id')) {
            return redirect()->route('exam.login');
        }
        $credential = ExamCredential::findOrFail(session('exam_credential_id'));
        $student = Student::where('nim', $credential->nim)->first();
        return view('students.auth.identity', compact('credential', 'student'));
    }

    public function start(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!session()->has('exam_credential_id')) {
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
                    'exam_nim'
                ]);

                return redirect()
                    ->route('exam.examLogin')
                    ->with('error', 'Credential tidak valid.');
            }

            $student = Student::where('nim', $credential->nim)->first();

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
                    'user_id'        => $user->id,
                    'exam_id'        => $exam->id,
                    'credential_id' => $credential->id,
                    'status'         => 'in_progress',
                    'question_order' => $questionOrder->toJson(),
                    'started_at'     => now(),
                ]);
            }

            DB::commit();

            Auth::login($user);

            session([
                'context' => strtolower($student->type)
            ]);

            return redirect()->route('student.exams.do', $exam->exam_code);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai ujian. Silakan coba lagi.');
        }
    }

    public function logout()
    {
        Auth::logout();

        session()->forget([
            'exam_credential_id',
            'exam_exam_id',
            'exam_student_id'
        ]);
        return redirect()->route('exam.examLogin');
    }
}
