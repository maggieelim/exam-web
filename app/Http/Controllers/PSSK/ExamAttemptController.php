<?php

namespace App\Http\Controllers\PSSK;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExamAttemptController extends Controller
{
    public function start(Request $request, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)
            ->firstOrFail();

        $request->validate([
            'password' => 'required|string',
        ]);

        if ($exam->password !== $request->password) {
            return back()->with('error', 'Wrong exam password.');
        }

        $user = auth()->user();

        // Gunakan database transaction untuk menghindari race condition
        DB::beginTransaction();
        try {
            $attempt = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('status', 'in_progress')
                ->first();

            if (!$attempt) {
                $questionOrder = DB::table('exam_questions')
                    ->where('exam_id', $exam->id)
                    ->pluck('kode_soal')
                    ->shuffle()
                    ->values();

                $attempt = ExamAttempt::create([
                    'user_id' => $user->id,
                    'exam_id' => $exam->id,
                    'status' => 'in_progress',
                    'question_order' => $questionOrder->toJson(),
                    'started_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai ujian. Silakan coba lagi.');
        }

        return redirect()->route('student.exams.do', $exam->exam_code);
    }

    public function do($exam_code, $kode_soal = null)
    {
        // Gunakan caching untuk data yang jarang berubah
        $cacheKey = "exam_{$exam_code}_data";
        $examData = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($exam_code) {
            return Exam::with([
                'questions' => function ($query) {
                    $query->select('id', 'exam_id', 'kode_soal', 'badan_soal', 'kalimat_tanya', 'image');
                },
                'questions.options' => function ($query) {
                    $query->select('id', 'exam_question_id', 'text', 'image');
                }
            ])
                ->where('exam_code', $exam_code)
                ->first(['id', 'exam_code', 'duration']);
        });

        if (!$examData) {
            abort(404);
        }

        $userId = auth()->id();

        // Optimasi query attempt dengan select spesifik
        $attempt = ExamAttempt::where('exam_id', $examData->id)
            ->where('user_id', $userId)
            ->where('status', 'in_progress')
            ->first(['id', 'status', 'total_pause_seconds', 'is_paused', 'paused_at', 'started_at', 'question_order']);

        if (!$attempt || in_array($attempt->status, ['completed', 'timeout'])) {
            return redirect()->route('student.studentExams.index')
                ->with('info', 'Ujian telah diselesaikan atau diakhiri oleh pengawas.');
        }

        // Hitung end time sekali saja
        $endTime = $attempt->started_at->copy()
            ->addMinutes($examData->duration)
            ->addSeconds($attempt->total_pause_seconds ?? 0);

        if (now()->greaterThan($endTime)) {
            $this->handleTimeout($attempt);
            return redirect()->route('student.studentExams.index', ['status' => 'previous'])
                ->with('error', 'Waktu ujian telah habis.');
        }

        // Parse question order sekali
        $questionOrder = collect(json_decode($attempt->question_order, true));

        // Urutkan questions berdasarkan question order
        $questions = $examData->questions->sortBy(function ($q) use ($questionOrder) {
            return $questionOrder->search($q->kode_soal);
        })->values();

        // Ambil current question
        $currentQuestion = $kode_soal
            ? $questions->where('kode_soal', $kode_soal)->first()
            : $questions->first();

        if (!$currentQuestion) {
            return redirect()->route('student.studentExams.index')
                ->with('error', 'Tidak ada soal dalam ujian ini.');
        }

        // Batch query untuk answers - ambil semua answers sekaligus
        $userAnswers = ExamAnswer::where('exam_id', $examData->id)
            ->where('user_id', $userId)
            ->whereNotNull('answer')
            ->get(['exam_question_id', 'answer', 'marked_doubt'])
            ->keyBy('exam_question_id');

        // Hitung answered count dari collection (lebih ringan)
        $answeredCount = $userAnswers->filter(fn($a) => !is_null($a->answer))->count();
        $totalQuestions = $questions->count();
        $allAnswered = $answeredCount >= $totalQuestions;

        // Get current saved answer
        $savedAnswer = $userAnswers->get($currentQuestion->id);

        // Get prev/next question
        $currentIndex = $questions->search(function ($item) use ($currentQuestion) {
            return $item->id === $currentQuestion->id;
        });

        $prevQuestion = $currentIndex > 0 ? $questions->get($currentIndex - 1) : null;
        $nextQuestion = $currentIndex < $totalQuestions - 1 ? $questions->get($currentIndex + 1) : null;

        return view('students.exams.do', compact(
            'examData',
            'attempt',
            'endTime',
            'currentQuestion',
            'prevQuestion',
            'nextQuestion',
            'savedAnswer',
            'allAnswered',
            'userAnswers',
            'totalQuestions',
            'answeredCount',
            'questions'
        ));
    }

    public function answer(Request $request, $exam_code, $kode_soal)
    {
        try {
            $userId = auth()->id();

            // Gunakan query builder untuk performa lebih baik
            $examId = DB::table('exams')
                ->where('exam_code', $exam_code)
                ->value('id');

            $questionId = DB::table('exam_questions')
                ->where('kode_soal', $kode_soal)
                ->where('exam_id', $examId)
                ->value('id');

            if (!$examId || !$questionId) {
                throw new \Exception('Data tidak ditemukan');
            }

            // Hitung is_correct dan score
            $isCorrect = 0;
            $score = 0;

            if ($request->answer) {
                $option = DB::table('exam_question_answers')
                    ->where('id', $request->answer)
                    ->value('is_correct');
                $isCorrect = $option ? 1 : 0;
                $score = $isCorrect ? 1 : 0;
            }

            // Gunakan upsert untuk performa lebih baik (Laravel 8+)
            DB::table('exam_answers')->updateOrInsert(
                [
                    'exam_id' => $examId,
                    'exam_question_id' => $questionId,
                    'user_id' => $userId,
                ],
                [
                    'answer' => $request->answer,
                    'marked_doubt' => $request->mark_doubt ?? 0,
                    'is_correct' => $isCorrect,
                    'score' => $score,
                    'updated_at' => now(),
                    'created_at' => DB::raw('IFNULL(created_at, NOW())'),
                ]
            );

            // Update attempt secara ringan
            DB::table('exam_attempts')
                ->where('exam_id', $examId)
                ->where('user_id', $userId)
                ->where('status', 'in_progress')
                ->update(['updated_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan',
                'marked_doubt' => $request->mark_doubt ?? 0,
                'is_correct' => $isCorrect,
                'score' => $score,
            ]);
        } catch (\Exception $e) {
            \Log::error('Answer save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban',
            ], 500);
        }
    }

    public function finish($exam_code)
    {
        try {
            $userId = auth()->id();

            DB::beginTransaction();

            $examId = DB::table('exams')
                ->where('exam_code', $exam_code)
                ->value('id');

            if (!$examId) {
                throw new \Exception('Exam not found');
            }

            $totalQuestions = DB::table('exam_questions')
                ->where('exam_id', $examId)
                ->count();

            $answeredCount = DB::table('exam_answers')
                ->where('exam_id', $examId)
                ->where('user_id', $userId)
                ->whereNotNull('answer')
                ->count();

            $status = $answeredCount >= $totalQuestions ? 'completed' : 'timeout';

            DB::table('exam_attempts')
                ->where('exam_id', $examId)
                ->where('user_id', $userId)
                ->update([
                    'finished_at' => now(),
                    'status' => $status,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route('student.studentExams.index', ['status' => 'previous'])
                ->with('success', 'Ujian selesai!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Finish exam error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyelesaikan ujian');
        }
    }

    public function checkExamStatus($exam_code)
    {
        $userId = auth()->id();

        $attempt = DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->where('exams.exam_code', $exam_code)
            ->where('exam_attempts.user_id', $userId)
            ->first(['exam_attempts.status', 'exam_attempts.is_paused', 'exam_attempts.paused_at']);

        return response()->json([
            'user' => $userId,
            'status' => $attempt->status ?? 'in_progress',
            'is_paused' => $attempt->is_paused ?? false,
            'paused_at' => $attempt->paused_at,
            'message' => ($attempt->status ?? '') === 'completed' ? 'Exam completed' : 'Exam in progress',
        ]);
    }

    public function markDoubt(Request $request, $exam_code, $kode_soal)
    {
        try {
            $userId = auth()->id();

            $examId = DB::table('exams')
                ->where('exam_code', $exam_code)
                ->value('id');

            $questionId = DB::table('exam_questions')
                ->where('kode_soal', $kode_soal)
                ->where('exam_id', $examId)
                ->value('id');

            DB::table('exam_answers')->updateOrInsert(
                [
                    'exam_id' => $examId,
                    'exam_question_id' => $questionId,
                    'user_id' => $userId,
                ],
                [
                    'marked_doubt' => 1,
                    'answer' => $request->answer,
                    'updated_at' => now(),
                    'created_at' => DB::raw('IFNULL(created_at, NOW())'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Soal ditandai sebagai ragu-ragu',
            ]);
        } catch (\Exception $e) {
            \Log::error('Mark doubt error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai soal',
            ], 500);
        }
    }

    protected function handleTimeout($attempt)
    {
        try {
            DB::table('exam_attempts')
                ->where('id', $attempt->id)
                ->update([
                    'finished_at' => now(),
                    'status' => 'timeout',
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            \Log::error('Timeout handling error: ' . $e->getMessage());
        }
    }
}
