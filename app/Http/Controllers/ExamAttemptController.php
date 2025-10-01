<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use Auth;
use Illuminate\Http\Request;

class ExamAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function start(Request $request, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();

        $request->validate([
            'password' => 'required|string',
        ]);
        if ($exam->password !== $request->password) {
            return back()->with('error', 'Wrong exam password.');
        }

        $user = auth()->user();

        // Cek apakah sudah ada attempt in_progress
        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->first();

        if (!$attempt) {
            $attempt = ExamAttempt::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'status'  => 'in_progress',
                'created_at' => now()
            ]);
        }

        // Redirect ke halaman ujian
        return redirect()->route('student.exams.do', $exam->exam_code);
    }

    public function do($exam_code, $kode_soal = null)
    {
        $exam = Exam::with(['questions' => function ($query) {
            $query->orderBy('id'); // Pastikan urutan konsisten
        }])->where('exam_code', $exam_code)->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->where('status', 'in_progress') // TAMBAHKAN INI
            ->firstOrFail();

        // Cek jika attempt sudah completed, redirect ke index
        if ($attempt->status === 'completed') {
            return redirect()->route('student.studentExams.index')
                ->with('info', 'Ujian sudah diselesaikan.');
        }

        $endTime = $attempt->created_at->copy()->addMinutes($exam->duration);
        // Validasi jika waktu sudah habis sebelum mulai
        if (now()->greaterThan($endTime)) {
            \Log::warning('Time already expired for attempt: ' . $attempt->id);
            $attempt->update([
                'finished_at' => now(),
                'status' => 'completed'
            ]);
            return redirect()->route('student.studentExams.index')
                ->with('error', 'Waktu ujian telah habis.');
        }

        $questions = $exam->questions->sortBy('id');

        // Pilih soal saat ini
        if ($kode_soal) {
            $currentQuestion = $questions->where('kode_soal', $kode_soal)->first();
        } else {
            $currentQuestion = $questions->first();
        }

        if (!$currentQuestion) {
            return redirect()->route('student.studentExams.index')
                ->with('error', 'Tidak ada soal dalam ujian ini.');
        }

        $currentIndex = $questions->search(function ($item) use ($currentQuestion) {
            return $item->id === $currentQuestion->id;
        });

        // Soal sebelumnya & selanjutnya
        $prevQuestion = $currentIndex > 0 ? $questions->get($currentIndex - 1) : null;
        $nextQuestion = $currentIndex < ($questions->count() - 1) ? $questions->get($currentIndex + 1) : null;

        // Ambil jawaban yang sudah disimpan user
        $savedAnswer = $currentQuestion->answers()->where('user_id', auth()->id())->first();

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

        return view('students.exams.do', compact(
            'exam',
            'attempt',
            'endTime',
            'questionNumber',
            'currentQuestion',
            'prevQuestion',
            'nextQuestion',
            'savedAnswer',
            'allAnswered',
            'userAnswers',
            'totalQuestions',
            'answeredCount'
        ));
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
                    'user_id' => auth()->id()
                ],
                [
                    'answer' => $request->answer,
                    'marked_doubt' => $request->mark_doubt ?? 0,
                    'is_correct' => $isCorrect,
                    'score' => $score
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan',
                'marked_doubt' => $answer->marked_doubt,
                'is_correct' => $isCorrect,
                'score' => $score
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    // Selesaikan ujian
    public function finish($exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($attempt) {
            $attempt->update([
                'finished_at' => now(),
                'status' => 'completed'
            ]);
        }

        return redirect()->route('student.studentExams.index', ['status' => 'upcoming'])
            ->with('success', 'Ujian selesai!');
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
                    'user_id' => auth()->id()
                ],
                [
                    'marked_doubt' => 1,
                    'answer' => $request->answer // Tetap simpan jawaban jika ada
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Soal ditandai sebagai ragu-ragu'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai soal: ' . $e->getMessage()
            ], 500);
        }
    }
}
