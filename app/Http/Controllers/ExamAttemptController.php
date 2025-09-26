<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestionAnswer;
use Auth;
use Illuminate\Http\Request;

class ExamAttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function start(Request $request, Exam $exam)
    {
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
            ]);
        }

        // Redirect ke halaman ujian
        return redirect()->route('student.exams.do', $exam->id);
    }

    public function do($examId, $questionId = null)
    {
        $exam = Exam::with('questions')->findOrFail($examId);

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Cek jika attempt sudah completed, redirect ke index
        if ($attempt->status === 'completed') {
            return redirect()->route('student.studentExams.index')
                ->with('info', 'Ujian sudah diselesaikan.');
        }

        $endTime = $attempt->created_at->copy()->addMinutes($exam->duration);

        // Pilih soal saat ini
        if ($questionId) {
            $currentQuestion = $exam->questions->where('id', $questionId)->first();
        } else {
            $currentQuestion = $exam->questions->first();
        }

        if (!$currentQuestion) {
            return redirect()->route('student.studentExams.index')
                ->with('error', 'Tidak ada soal dalam ujian ini.');
        }

        // Soal sebelumnya & selanjutnya
        $prevQuestionId = $exam->questions->where('id', '<', $currentQuestion->id)->max('id');
        $nextQuestionId = $exam->questions->where('id', '>', $currentQuestion->id)->min('id');

        // Ambil jawaban yang sudah disimpan user
        $savedAnswer = $currentQuestion->answers()->where('user_id', auth()->id())->first();
        $questionNumber = 1;
        foreach ($exam->questions as $index => $question) {
            if ($question->id == $currentQuestion->id) {
                $questionNumber = $index + 1;
                break;
            }
        }
        $totalQuestions = $exam->questions->count();
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
            'prevQuestionId',
            'nextQuestionId',
            'savedAnswer',
            'allAnswered',
            'userAnswers'
        ));
    }

    public function answer(Request $request, $examId, $questionId)
    {
        try {
            $option = ExamQuestionAnswer::find($request->answer);
            $isCorrect = $option && $option->is_correct ? 1 : 0;
            $score = $isCorrect ? 1 : 0;
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_id' => $examId,
                    'exam_question_id' => $questionId,
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
    public function finish($examId)
    {
        $exam = Exam::findOrFail($examId);

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
    public function markDoubt(Request $request, $examId, $questionId)
    {
        try {
            $answer = ExamAnswer::updateOrCreate(
                [
                    'exam_id' => $examId,
                    'exam_question_id' => $questionId,
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
