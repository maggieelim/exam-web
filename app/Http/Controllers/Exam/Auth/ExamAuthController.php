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
        ]);

        $credential = ExamCredential::where('username', $request->username)->first();

        if (!$credential || !Hash::check($request->password, $credential->password)) {
            return back()->with('error', 'Username atau password salah');
        }

        $exam = Exam::findOrFail($credential->exam_id);
        if ($exam->status === 'upcoming') {
            return back()->with('error', 'Ujian belum dimulai');
        }

        if ($credential->is_used && $credential->nim) {

            $student = Student::where('nim', $credential->nim)->first();

            if ($student) {
                $attempt = ExamAttempt::where('exam_id', $credential->exam_id)
                    ->where('user_id', $student->user_id)
                    ->latest()
                    ->first();

                if ($attempt && $attempt->status === 'completed') {
                    return back()->with('error', 'Ujian sudah diselesaikan.');
                }
            }
        }

        session([
            'exam_credential_id' => $credential->id,
            'exam_exam_id' => $credential->exam_id,
        ]);

        return redirect()->route('exam.identity');
    }

    public function identity()
    {
        if (!session()->has('exam_credential_id')) {
            return redirect()->route('exam.login');
        }
        $credential = ExamCredential::findOrFail(session('exam_credential_id'));

        return view('students.auth.identity', compact('credential'));
    }

    public function start(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);

        $request->validate([
            'nim'      => 'required',
            'name'     => 'required',
            'password' => 'required|string',
        ]);

        if (!session()->has('exam_credential_id')) {
            return redirect()->route('exam.examLogin');
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
                    'exam_exam_id'
                ]);

                return redirect()
                    ->route('exam.examLogin')
                    ->with('error', 'Credential tidak valid.');
            }

            $student = Student::where('nim', $request->nim)->first();

            if (!$student) {
                DB::rollBack();
                return back()->with('error', 'NIM tidak ditemukan.');
            }

            if ($credential->nim && $credential->nim !== $student->nim) {
                DB::rollBack();
                session()->forget([
                    'exam_credential_id',
                    'exam_exam_id'
                ]);

                return redirect()
                    ->route('exam.examLogin')
                    ->with('error', 'Credential sudah digunakan oleh mahasiswa lain.');
            }

            $isRegistered = CourseStudent::where('course_id', $exam->course_id)
                ->where('student_id', $student->id)
                ->exists();

            if (!$isRegistered) {
                DB::rollBack();
                session()->forget([
                    'exam_credential_id',
                    'exam_exam_id'
                ]);

                return redirect()
                    ->route('exam.examLogin')
                    ->with('error', 'Anda tidak terdaftar pada mata kuliah ini.');
            }

            if ($exam->password !== $request->password) {
                DB::rollBack();
                return back()->with('error', 'Password ujian salah.');
            }

            $user = User::findOrFail($student->user_id);

            // Lock attempt untuk hindari double create
            $attempt = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->lockForUpdate()
                ->first();

            if ($attempt) {
                // 🚫 Sudah selesai
                if ($attempt->status === 'completed') {
                    DB::rollBack();

                    session()->forget([
                        'exam_credential_id',
                        'exam_exam_id'
                    ]);

                    return redirect()
                        ->route('exam.examLogin')
                        ->with('error', 'Ujian sudah diselesaikan.');
                }

                // 🔒 Masih ongoing tapi pakai credential beda
                if (
                    $attempt->status === 'in_progress' &&
                    $attempt->credential_id !== $credential->id
                ) {

                    DB::rollBack();

                    session()->forget([
                        'exam_credential_id',
                        'exam_exam_id'
                    ]);

                    return redirect()
                        ->route('exam.examLogin')
                        ->with('error', 'Anda masih memiliki ujian yang sedang berlangsung. Silakan lanjutkan ujian sebelumnya.');
                }
            }

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

            // Update credential setelah semua aman
            $credential->update([
                'nim'     => $student->nim,
                'is_used' => true,
                'used_at' => now()
            ]);

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
