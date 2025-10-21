<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class ExamAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function start(Request $request, $exam_code)
    {
        $exam = Exam::with('questions')->where('exam_code', $exam_code)->firstOrFail();
        $questionOrder = $exam->questions->pluck('kode_soal')->shuffle()->values();

        $request->validate([
            'password' => 'required|string',
        ]);
        if ($exam->password !== $request->password) {
            return back()->with('error', 'Wrong exam password.');
        }

        $user = auth()->user();

        // Cek apakah sudah ada attempt in_progress
        $attempt = ExamAttempt::where('user_id', $user->id)->where('exam_id', $exam->id)->where('status', 'in_progress')->first();

        if (!$attempt) {
            $attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'status' => 'in_progress',
                'question_order' => $questionOrder->toJson(),
                'started_at' => now(),
                'created_at' => now(),
            ]);
        }

        // Redirect ke halaman ujian
        return redirect()->route('student.exams.do', $exam->exam_code);
    }

    public function do($exam_code, $kode_soal = null)
    {
        $exam = Exam::with([
            'questions' => function ($query) {
                $query->orderBy('id'); // Pastikan urutan konsisten
            },
        ])
            ->where('exam_code', $exam_code)
            ->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->where('status', 'in_progress') // TAMBAHKAN INI
            ->firstOrFail();

        // Cek jika attempt sudah completed, redirect ke index
        if (!$attempt || in_array($attempt->status, ['completed', 'timeout'])) {
            return redirect()->route('student.studentExams.index')->with('info', 'Ujian telah diselesaikan atau diakhiri oleh pengawas.');
        }

        $endTime = $attempt->created_at->copy()->addMinutes($exam->duration);
        // Validasi jika waktu sudah habis sebelum mulai
        if (now()->greaterThan($endTime)) {
            \Log::warning('Time already expired for attempt: ' . $attempt->id);
            $attempt->update([
                'finished_at' => now(),
                'status' => 'timeout',
            ]);
            return redirect()
                ->route('student.studentExams.index', ['status' => 'previous'])
                ->with('error', 'Waktu ujian telah habis.');
        }
        $questionOrder = collect(json_decode($attempt->question_order, true));

        $questions = $exam->questions
            ->sortBy(function ($q) use ($questionOrder) {
                return $questionOrder->search($q->kode_soal);
            })
            ->values();

        // Pilih soal saat ini
        if ($kode_soal) {
            $currentQuestion = $questions->where('kode_soal', $kode_soal)->first();
        } else {
            $currentQuestion = $questions->first();
        }

        if (!$currentQuestion) {
            return redirect()->route('student.studentExams.index')->with('error', 'Tidak ada soal dalam ujian ini.');
        }

        $currentIndex = $questions->search(function ($item) use ($currentQuestion) {
            return $item->id === $currentQuestion->id;
        });

        // Soal sebelumnya & selanjutnya
        $prevQuestion = $currentIndex > 0 ? $questions->get($currentIndex - 1) : null;
        $nextQuestion = $currentIndex < $questions->count() - 1 ? $questions->get($currentIndex + 1) : null;

        // Ambil jawaban yang sudah disimpan user
        $savedAnswer = $currentQuestion
            ->answers()
            ->where('user_id', auth()->id())
            ->first();

        $questionNumber = $currentIndex + 1;
        $totalQuestions = $questions->count();

        $answeredCount = ExamAnswer::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->whereNotNull('answer')
            ->count();

        $allAnswered = $answeredCount >= $totalQuestions;

        $userAnswers = ExamAnswer::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->pluck('answer', 'exam_question_id');

        return view('students.exams.do', compact('exam', 'attempt', 'endTime', 'questionNumber', 'currentQuestion', 'prevQuestion', 'nextQuestion', 'savedAnswer', 'allAnswered', 'userAnswers', 'totalQuestions', 'answeredCount', 'questions'));
    }

    public function answer(Request $request, $exam_code, $kode_soal)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $question = ExamQuestion::where('kode_soal', $kode_soal)->where('exam_id', $exam->id)->firstOrFail();

        try {
            $option = ExamQuestionAnswer::find($request->answer);
            $isCorrect = $option && $option->is_correct ? 1 : 0;
            $score = $isCorrect ? 1 : 0;
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'exam_question_id' => $question->id,
                    'user_id' => auth()->id(),
                ],
                [
                    'answer' => $request->answer,
                    'marked_doubt' => $request->mark_doubt ?? 0,
                    'is_correct' => $isCorrect,
                    'score' => $score,
                ],
            );
            $attempt = ExamAttempt::where('exam_id', $exam->id)
                ->where('user_id', auth()->id())
                ->where('status', 'in_progress') // Pastikan hanya update jika masih in_progress
                ->first();

            if ($attempt) {
                $attempt->touch(); // Update updated_at untuk menandakan aktivitas terakhir
            }

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan',
                'marked_doubt' => $answer->marked_doubt,
                'is_correct' => $isCorrect,
                'score' => $score,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    // Selesaikan ujian
    public function finish($exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $user = FacadesAuth::user();
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->first();

        if ($attempt) {
            $totalQuestions = $exam->questions()->count();
            $answeredCount = ExamAnswer::where('exam_id', $exam->id)->where('user_id', $user->id)->count();

            $iscomplete = $totalQuestions >= $answeredCount;
            $attempt->update([
                'finished_at' => now(),
                'status' => $iscomplete ? 'completed' : 'time_out',
            ]);
        }

        return redirect()
            ->route('student.studentExams.index', ['status' => 'previous'])
            ->with('success', 'Ujian selesai!');
    }

    // Di StudentExamController
    public function checkExamStatus($exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->first();

        return response()->json([
            'status' => $attempt->status ?? 'in_progress',
            'message' => $attempt->status === 'completed' ? 'Exam has been completed' : 'Exam in progress',
        ]);
    }
    // Mark as doubt (tombol ragu-ragu)
    public function markDoubt(Request $request, $exam_code, $kode_soal)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $question = ExamQuestion::where('kode_soal', $kode_soal)->where('exam_id', $exam->id)->firstOrFail();

        try {
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'exam_question_id' => $question->id,
                    'user_id' => auth()->id(),
                ],
                [
                    'marked_doubt' => 1,
                    'answer' => $request->answer, // Tetap simpan jawaban jika ada
                ],
            );

            return response()->json([
                'success' => true,
                'message' => 'Soal ditandai sebagai ragu-ragu',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menandai soal: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
