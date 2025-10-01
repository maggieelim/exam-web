<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class ExamResultsController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function studentIndex(Request $request)
    {
        $user = auth()->user();
        $courses = Course::all();

        $exams = Exam::with([
            'course',
            'creator',
            'updater',
            'answers' => fn($q) => $q->where('user_id', $user->id),
        ])
            ->withCount('questions')
            ->whereHas(
                'attempts',
                fn($q) =>
                $q->where('user_id', $user->id)
                    ->where('status', 'completed')
            )
            ->when(
                $request->title,
                fn($q) =>
                $q->where('title', 'like', "%{$request->title}%")
            )
            ->when(
                $request->course_id,
                fn($q) =>
                $q->where('course_id', $request->course_id)
            )
            ->when(
                $request->date_from && $request->date_to,
                fn($q) =>
                $q->whereBetween('exam_date', [
                    "{$request->date_from} 00:00:00",
                    "{$request->date_to} 23:59:59"
                ])
            )->where('is_published', true)
            ->orderBy($request->get('sort', 'id'), $request->get('dir', 'desc'))
            ->paginate(10);

        // mapping hasil skor dari relasi
        $exams->getCollection()->each(function ($exam) {
            // Kelompokkan jawaban berdasarkan category_id
            $groupedAnswers = $exam->answers->groupBy(function ($ans) {
                return $ans->question->category_id ?? 'all'; // kalau null jadi 'all'
            });

            $exam->categories_result = $groupedAnswers->map(function ($answers, $categoryId) use ($exam) {
                $totalCorrect  = $answers->where('is_correct', 1)->count();
                $totalWrong    = $answers->where('is_correct', 0)->count();
                $totalScore    = $answers->sum('score');
                $totalQuestion = $answers->count();

                return [
                    'category_id'    => $categoryId === 'all' ? null : $categoryId,
                    'category_name'  => $categoryId === 'all'
                        ? 'Keseluruhan'
                        : optional($answers->first()->question->category)->name,
                    'total_correct'  => $totalCorrect,
                    'total_wrong'    => $totalWrong,
                    'total_score'    => $totalScore,
                    'total_question' => $totalQuestion,
                    'percentage'     => $totalQuestion > 0
                        ? round(($totalCorrect / $totalQuestion) * 100, 2)
                        : 0,
                ];
            })->values();
        });

        $sort = $request->get('sort', 'exam_date');
        $dir  = $request->get('dir', 'desc');

        return view('students.results.index', compact('exams', 'courses', 'sort', 'dir'));
    }

    public function indexLecturer(Request $request, $status = 'ungraded')
    {
        $user = auth()->user(); // dosen yang login
        $courses = Course::whereHas('lecturers', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->get();

        $query = Exam::with(['course', 'attempts'])
            ->withCount('questions')
            ->whereHas('course.lecturers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->where('status', 'ended');

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'ungraded') {
            $query->where('is_published', false);
        }
        $sort = $request->get('sort', 'exam_date');
        $dir  = $request->get('dir', 'desc');

        $exams = $query->orderBy('exam_date', 'desc')->paginate(10);

        return view('lecturer.grading.index', compact('exams', 'status', 'courses', 'sort', 'dir'));
    }

    public function grade($examCode)
    {
        $exam = Exam::with([
            'course.lecturers',
            'questions.category',
            'attempts.user',
            'answers.question.category',
        ])->where('exam_code', $examCode)->withCount('questions')->withCount('attempts')
            ->firstOrFail();

        /** @var \Illuminate\Pagination\LengthAwarePaginator $attempts */

        $attempts = ExamAttempt::with(['user', 'answers.question.category'])
            ->where('exam_id', $exam->id)
            ->paginate(10);

        $results = $attempts->map(function ($attempt) use ($exam) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);

            $groupedAnswers = $userAnswers->groupBy(function ($ans) {
                return $ans->question->category_id ?? 'all';
            });

            $categoriesResult = $groupedAnswers->map(function ($answers, $categoryId) {
                $totalCorrect  = $answers->where('is_correct', 1)->count();
                $totalWrong    = $answers->where('is_correct', 0)->count();
                $totalScore    = $answers->sum('score');
                $totalQuestion = $answers->count();

                return [
                    'category_id'    => $categoryId === 'all' ? null : $categoryId,
                    'category_name'  => $categoryId === 'all'
                        ? 'Keseluruhan'
                        : optional($answers->first()->question->category)->name,
                    'total_correct'  => $totalCorrect,
                    'total_wrong'    => $totalWrong,
                    'total_score'    => $totalScore,
                    'total_question' => $totalQuestion,
                    'percentage'     => $totalQuestion > 0
                        ? round(($totalCorrect / $totalQuestion) * 100, 2)
                        : 0,
                ];
            })->values();

            return (object)[
                'student'           => $attempt->user,
                'attempt_status'    => $attempt->status,
                'categories_result' => $categoriesResult,
                'answers'           => $userAnswers,
                'total_answered'    => $userAnswers->count(),
                'total_score'       => $userAnswers->sum('score'),
            ];
        });

        return view('lecturer.grading.grade', compact('exam', 'results', 'attempts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($examCode, $nim)
    {
        $exam = Exam::with([
            'questions.category',
            'attempts.user.student',   // ikut load student
        ])
            ->where('exam_code', $examCode)
            ->firstOrFail();

        // Cari student berdasarkan NIM
        $student = Student::where('nim', $nim)->firstOrFail();
        $user = $student->user;

        // Ambil attempt mahasiswa
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt) {
            return redirect()->back()->with('error', 'Mahasiswa belum mengikuti ujian ini.');
        }
        // Ambil jawaban mahasiswa
        $userAnswers = ExamAnswer::with(['question'])
            ->where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->get();


        $totalQuestions = $exam->questions->count();
        $totalCorrect   = $userAnswers->where('is_correct', 1)->count();
        $totalScore     = $userAnswers->sum('score');
        $percentage     = $totalQuestions > 0
            ? round(($totalCorrect / $totalQuestions) * 100, 2)
            : 0;

        // Hitung per kategori
        $groupedAnswers = $userAnswers->groupBy(function ($ans) {
            return $ans->question->category_id ?? 'all';
        });

        $categoriesResult = $groupedAnswers->map(function ($answers, $categoryId) {
            $totalCorrect  = $answers->where('is_correct', 1)->count();
            $totalWrong    = $answers->where('is_correct', 0)->count();
            $totalScore    = $answers->sum('score');
            $totalQuestion = $answers->count();

            return [
                'category_id'    => $categoryId === 'all' ? null : $categoryId,
                'category_name'  => $categoryId === 'all'
                    ? 'Keseluruhan'
                    : optional($answers->first()->question->category)->name,
                'total_correct'  => $totalCorrect,
                'total_wrong'    => $totalWrong,
                'total_score'    => $totalScore,
                'total_question' => $totalQuestion,
                'percentage'     => $totalQuestion > 0
                    ? round(($totalCorrect / $totalQuestion) * 100, 2)
                    : 0,
            ];
        })->values();

        return view('lecturer.grading.feedback', compact(
            'exam',
            'attempt',
            'userAnswers',
            'totalQuestions',
            'totalCorrect',
            'totalScore',
            'percentage',
            'categoriesResult',
            'student', // biar bisa tampilkan nim di view
            'user'     // kalau butuh akses ke data user juga
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $examCode, $nim)
    {
        $student = Student::where('nim', $nim)->firstOrFail();
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $student->user_id)
            ->firstOrFail();

        // simpan feedback keseluruhan
        $attempt->feedback = $request->input('overall_feedback');
        $attempt->save();

        // simpan feedback per soal
        if ($request->has('feedback')) {
            foreach ($request->input('feedback') as $questionId => $feedback) {
                $answer = ExamAnswer::where('exam_question_id', $questionId)
                    ->where('exam_id', $exam->id)
                    ->where('user_id', $student->user_id)
                    ->first();

                if ($answer) {
                    $answer->feedback = $feedback;
                    $answer->save();
                }
            }
        }

        return back()->with('success', 'Feedback Berhasil Disimpan!');
    }

    public function publish($examCode)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();

        // Update exam
        $exam->update(['is_published' => true]);

        // Update semua attempts terkait
        $exam->attempts()->update(['grading_status' => 'published']);

        return back()->with('success', 'Exam berhasil dipublish!');
    }

    public function show($examCode, Request $request)
    {
        $exam = Exam::with([
            'course.lecturers',
            'questions.category',
            'attempts.user',
            'answers.user',
            'answers.question.category',
            'answers.question.options' // Tambahkan options untuk analisis pilihan jawaban
        ])->where('exam_code', $examCode)->withCount('questions')->withCount('attempts')
            ->firstOrFail();

        $activeTab = $request->get('tab', 'results');

        /** @var \Illuminate\Pagination\LengthAwarePaginator $attempts */
        $attempts = ExamAttempt::with(['user', 'answers.question.category'])
            ->where('exam_id', $exam->id)
            ->paginate(10);

        $results = $attempts->map(function ($attempt) use ($exam) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);

            $groupedAnswers = $userAnswers->groupBy(function ($ans) {
                return $ans->question->category_id ?? 'all';
            });

            $categoriesResult = $groupedAnswers->map(function ($answers, $categoryId) {
                $totalCorrect  = $answers->where('is_correct', 1)->count();
                $totalWrong    = $answers->where('is_correct', 0)->count();
                $totalScore    = $answers->sum('score');
                $totalQuestion = $answers->count();

                return [
                    'category_id'    => $categoryId === 'all' ? null : $categoryId,
                    'category_name'  => $categoryId === 'all'
                        ? 'Keseluruhan'
                        : optional($answers->first()->question->category)->name,
                    'total_correct'  => $totalCorrect,
                    'total_wrong'    => $totalWrong,
                    'total_score'    => $totalScore,
                    'total_question' => $totalQuestion,
                    'percentage'     => $totalQuestion > 0
                        ? round(($totalCorrect / $totalQuestion) * 100, 2)
                        : 0,
                ];
            })->values();

            return (object)[
                'student'           => $attempt->user,
                'attempt_status'    => $attempt->status,
                'categories_result' => $categoriesResult,
                'answers'           => $userAnswers,
                'total_answered'    => $userAnswers->count(),
                'total_score'       => $userAnswers->sum('score'),
            ];
        });

        // ANALYTICS DATA - hanya jika ada attempts
        $analytics = [];
        $questionAnalysis = [];
        $rankingData = [];

        if ($exam->attempts_count > 0) {
            // 1. Analisis per soal
            $questionAnalysis = $exam->questions->map(function ($question) use ($exam) {
                $totalStudents = $exam->attempts->count();
                $answersForQuestion = $exam->answers->where('exam_question_id', $question->id);
                $correctAnswers = $answersForQuestion->where('is_correct', true)->count();

                // Persentase jawaban benar
                $correctPercentage = $totalStudents > 0
                    ? round(($correctAnswers / $totalStudents) * 100, 2)
                    : 0;

                // Analisis pilihan jawaban
                $optionAnalysis = [];
                if ($question->options) {
                    foreach ($question->options as $option) {
                        $optionAnswers = $answersForQuestion->where('selected_option', $option->id)->count();
                        $optionPercentage = $totalStudents > 0
                            ? round(($optionAnswers / $totalStudents) * 100, 2)
                            : 0;

                        $optionAnalysis[] = [
                            'option_id' => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct' => $option->is_correct,
                            'count' => $optionAnswers,
                            'percentage' => $optionPercentage
                        ];
                    }
                }

                // Indeks daya pembeda
                $discriminationIndex = $this->calculateDiscriminationIndex($exam, $question);

                // Tingkat kesulitan
                $difficultyLevel = $this->getDifficultyLevel($correctAnswers, $totalStudents);

                return [
                    'question_id' => $question->id,
                    'question_text' => $question->badan_soal,
                    'correct_percentage' => $correctPercentage,
                    'correct_count' => $correctAnswers,
                    'total_students' => $totalStudents,
                    'option_analysis' => $optionAnalysis,
                    'discrimination_index' => $discriminationIndex,
                    'difficulty_level' => $difficultyLevel
                ];
            });

            // 2. Data ranking siswa
            $rankingData = $exam->attempts->map(function ($attempt) use ($exam) {
                $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
                $totalQuestions = $exam->questions_count;
                $correctAnswers = $userAnswers->where('is_correct', true)->count();
                $scorePercentage = $totalQuestions > 0
                    ? round(($correctAnswers / $totalQuestions) * 100, 2)
                    : 0;

                return [
                    'student_id' => $attempt->user->id,
                    'student_name' => $attempt->user->name,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions,
                    'score_percentage' => $scorePercentage,
                    'attempt_status' => $attempt->status,
                    'completed_at' => $attempt->completed_at
                ];
            })->sortByDesc('score_percentage')->values()->map(function ($item, $index) {
                $item['rank'] = $index + 1;
                return $item;
            });

            // 3. Statistik umum
            $analytics = [
                'total_students' => $exam->attempts_count,
                'average_score' => $rankingData->avg('score_percentage') ?? 0,
                'highest_score' => $rankingData->max('score_percentage') ?? 0,
                'lowest_score' => $rankingData->min('score_percentage') ?? 0,
                'completion_rate' => $exam->attempts_count > 0
                    ? round(($exam->attempts->where('status', 'completed')->count() / $exam->attempts_count) * 100, 2)
                    : 0
            ];

            // 4. Data untuk chart
            $chartData = $this->prepareChartData($questionAnalysis, $exam);
        }

        return view('lecturer.grading.show.index', compact(
            'activeTab',
            'exam',
            'results',
            'attempts',
            'analytics',
            'questionAnalysis',
            'rankingData',
            'chartData' // Pastikan variabel ini didefinisikan
        ));
    }

    private function calculateDiscriminationIndex($exam, $question)
    {
        $totalStudents = $exam->attempts->count();
        if ($totalStudents < 10) return 0; // Need minimum students for accurate calculation

        // Sort students by total score
        $sortedAttempts = $exam->attempts->sortByDesc(function ($attempt) use ($exam) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
            return $userAnswers->where('is_correct', true)->count();
        });

        // Get top 27% and bottom 27%
        $groupSize = (int)($totalStudents * 0.27);
        if ($groupSize < 1) $groupSize = 1;

        $topGroup = $sortedAttempts->take($groupSize);
        $bottomGroup = $sortedAttempts->take(-$groupSize);

        // Calculate correct answers in each group for this specific question
        $topCorrect = $topGroup->filter(function ($attempt) use ($exam, $question) {
            $answer = $exam->answers
                ->where('user_id', $attempt->user_id)
                ->where('exam_question_id', $question->id)
                ->first();
            return $answer && $answer->is_correct;
        })->count();

        $bottomCorrect = $bottomGroup->filter(function ($attempt) use ($exam, $question) {
            $answer = $exam->answers
                ->where('user_id', $attempt->user_id)
                ->where('exam_question_id', $question->id)
                ->first();
            return $answer && $answer->is_correct;
        })->count();

        // Discrimination Index = (Upper Group % Correct) - (Lower Group % Correct)
        $discriminationIndex = ($topCorrect / $groupSize) - ($bottomCorrect / $groupSize);

        return round($discriminationIndex, 3);
    }

    private function getDifficultyLevel($correctAnswers, $totalStudents)
    {
        if ($totalStudents === 0) return 'Tidak ada data';

        $percentage = ($correctAnswers / $totalStudents) * 100;

        if ($percentage >= 80) return 'Mudah';
        if ($percentage >= 60) return 'Sedang';
        if ($percentage >= 40) return 'Cukup Sulit';
        return 'Sulit';
    }

    private function prepareChartData($questionAnalysis, $exam)
    {
        // Data untuk grafik kesulitan soal
        $difficultyData = [
            'Mudah' => 0,
            'Sedang' => 0,
            'Cukup Sulit' => 0,
            'Sulit' => 0
        ];

        foreach ($questionAnalysis as $analysis) {
            $difficultyData[$analysis['difficulty_level']]++;
        }

        // Data untuk grafik discrimination index
        $discriminationRanges = [
            'Excellent (>0.4)' => 0,
            'Good (0.3-0.39)' => 0,
            'Fair (0.2-0.29)' => 0,
            'Poor (0.1-0.19)' => 0,
            'Very Poor (<0.1)' => 0
        ];

        foreach ($questionAnalysis as $analysis) {
            $di = $analysis['discrimination_index'];
            if ($di > 0.4) $discriminationRanges['Excellent (>0.4)']++;
            elseif ($di >= 0.3) $discriminationRanges['Good (0.3-0.39)']++;
            elseif ($di >= 0.2) $discriminationRanges['Fair (0.2-0.29)']++;
            elseif ($di >= 0.1) $discriminationRanges['Poor (0.1-0.19)']++;
            else $discriminationRanges['Very Poor (<0.1)']++;
        }

        // Data untuk grafik score distribution
        $scoreRanges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0
        ];

        foreach ($exam->attempts as $attempt) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
            $totalQuestions = $exam->questions_count;
            $percentage = $totalQuestions > 0
                ? round(($userAnswers->where('is_correct', true)->count() / $totalQuestions) * 100, 2)
                : 0;

            if ($percentage <= 20) $scoreRanges['0-20']++;
            elseif ($percentage <= 40) $scoreRanges['21-40']++;
            elseif ($percentage <= 60) $scoreRanges['41-60']++;
            elseif ($percentage <= 80) $scoreRanges['61-80']++;
            else $scoreRanges['81-100']++;
        }

        return [
            'difficulty' => $difficultyData,
            'discrimination' => $discriminationRanges,
            'scores' => $scoreRanges
        ];
    }
    public function destroy(string $id)
    {
        //
    }
}
