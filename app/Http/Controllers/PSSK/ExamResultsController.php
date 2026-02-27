<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\ExamQuestionsAnalysisExport;
use App\Exports\ExamResultsExport;
use App\Http\Controllers\Controller;
use App\Mail\ExamPublishedNotification;
use App\Models\CourseCoordinator;
use App\Models\CourseLecturer;
use App\Models\DifficultyLevel;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\Student;
use App\Services\SemesterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function examQueryForLecturer($user, $status = null)
    {
        $coordinated = CourseCoordinator::where('lecturer_id', $user->id)->pluck('course_id');
        $query = Exam::with(['course', 'attempts', 'semester'])
            ->withCount('questions')
            ->whereIn('course_id', $coordinated)
            ->where('status', 'ended');

        if ($status === 'published') {
            $query->where('is_published', true);
        }
        if ($status === 'ungraded') {
            $query->where('is_published', false);
        }

        return $query;
    }

    public function indexLecturer(Request $request, $status = 'ungraded')
    {
        $user = auth()->user(); // dosen yang login
        $lecturer = Lecturer::where('user_id', $user->id)->first();
        $today = Carbon::today();
        $semesterId = $request->get('semester_id');
        $agent = new Agent();

        $activeSemester = SemesterService::active();

        if (!$semesterId && $activeSemester) {
            $semesterId = $activeSemester->id;
        }

        $semesters = SemesterService::list();

        if (!$lecturer) {
            return redirect()->back()->with('error', 'Data dosen tidak ditemukan.');
        }
        $courses = CourseLecturer::where('lecturer_id', $lecturer->id)->with('course')->get()->pluck('course')->unique('id')->values();

        $query = $this->examQueryForLecturer($lecturer, $status);

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'ungraded') {
            $query->where('is_published', false);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $query->when($request->filled('title'), fn($q) => $q->where('title', 'like', '%' . $request->title . '%'))->when($request->filled('course_id'), fn($q) => $q->where('course_id', $request->course_id));

        $sort = $request->get('sort', 'exam_date');
        $dir = $request->get('dir', 'desc');

        $exams = $query->orderBy('exam_date', 'desc')->paginate(10)->appends($request->query());

        if ($agent->isMobile()) {
            return view('pssk.grading.mobile.index_mobile', compact('exams', 'status', 'courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
        }
        return view('pssk.grading.index', compact('exams', 'status', 'courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
    }

    public function download($examCode)
    {
        $exam = Exam::with(['course', 'attempts.user.student', 'answers.user.student', 'answers.question'])
            ->where('exam_code', $examCode)
            ->firstOrFail();

        $fileName = 'Hasil_' . str_replace(' ', '_', $exam->title) . '_Blok_' . $exam->course->slug . '.xlsx';

        return Excel::download(new ExamResultsExport($exam), $fileName);
    }

    public function downloadQuestions($examCode)
    {
        $exam = Exam::with(['course.lecturers', 'questions.category', 'questions.options', 'attempts.user.student', 'answers.user.student', 'answers.question.category', 'answers.question.options'])
            ->where('exam_code', $examCode)
            ->withCount('questions')
            ->withCount('attempts')
            ->firstOrFail();

        $fileName = 'Question_Analysis_' . str_replace(' ', '_', $exam->title) . '_' . $exam->course->slug . '.xlsx';
        return Excel::download(new ExamQuestionsAnalysisExport($exam), $fileName);
    }

    public function grade($examCode, Request $request)
    {
        $exam = Exam::with(['course.lecturers', 'course.coordinators.lecturer.user', 'questions.category', 'attempts.user.student', 'answers.question.category'])
            ->where('exam_code', $examCode)
            ->withCount('questions')
            ->withCount('attempts')
            ->firstOrFail();

        $lecturers = $exam->course->coordinators;
        $attemptsQuery = ExamAttempt::with(['user.student', 'answers.question.category'])->where('exam_id', $exam->id);

        if ($request->filled('search')) {
            $search = $request->search;

            $attemptsQuery->where(function ($query) use ($search) {
                $query
                    ->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user.student', function ($q) use ($search) {
                        $q->where('nim', 'like', "%{$search}%");
                    });
            });
        }
        /** @var \Illuminate\Pagination\LengthAwarePaginator $attempts */
        $sort = $request->get('sort', 'finished_at');
        $dir = $request->get('dir', 'desc');

        $allowedSorts = ['finished_at', 'grading_status'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'finished_at';
        $dir = in_array(strtolower($dir), ['asc', 'desc']) ? $dir : 'desc';

        $attemptsQuery->orderBy($sort, $dir);
        $attempts = $attemptsQuery->paginate(10);

        $results = $this->buildRankingResultsOptimized($exam, $request);
        $sort = $request->get('sort', 'rank');
        $dir = $request->get('dir', 'asc');

        // Lakukan sorting berdasarkan pilihan user
        if ($results->isNotEmpty()) {
            $results = match ($sort) {
                'nim' => $results->sortBy(fn($q) => $q['student_data']->nim ?? '', SORT_NATURAL, $dir === 'desc'),
                'name' => $results->sortBy(fn($q) => $q['student']->name ?? '', SORT_NATURAL, $dir === 'desc'),
                'feedback' => $results->sortBy(fn($q) => $q['feedback'] ?? 0, SORT_NUMERIC, $dir === 'desc'),
                default => $results->sortBy(fn($q) => $q['rank'] ?? 0, SORT_NUMERIC, $dir === 'desc'),
            };
        }

        $status = $this->determineStatus($exam);
        return view('pssk.grading.grade', compact('exam', 'results', 'attempts', 'status', 'sort', 'dir', 'lecturers'));
    }

    public function edit($examCode, $nim)
    {
        $exam = Exam::with(['questions.category', 'questions.options', 'attempts.user.student'])
            ->where('exam_code', $examCode)
            ->firstOrFail();

        $student = Student::where('nim', $nim)->firstOrFail();
        $user = $student->user;

        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $user->id)->firstOrFail();

        // Ambil semua jawaban mahasiswa
        $allUserAnswers = ExamAnswer::with(['question'])
            ->where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->get();

        // Filter questions berdasarkan status jawaban
        $filteredQuestions = $exam->questions->filter(function ($question) use ($allUserAnswers) {
            $userAnswer = $allUserAnswers->firstWhere('exam_question_id', $question->id);
            $isAnswered = $userAnswer && !is_null($userAnswer->answer);
            $isCorrect = $userAnswer ? $userAnswer->is_correct : false;

            $answerStatus = request('answer_status');

            if (!$answerStatus) {
                return true; // Tampilkan semua jika tidak ada filter
            }

            switch ($answerStatus) {
                case 'correct':
                    return $isAnswered && $isCorrect;
                case 'incorrect':
                    return $isAnswered && !$isCorrect;
                case 'not_answered':
                    return !$isAnswered;
                default:
                    return true;
            }
        });

        // Siapkan data untuk view
        $questionsData = [];
        foreach ($filteredQuestions as $index => $question) {
            $userAnswer = $allUserAnswers->firstWhere('exam_question_id', $question->id);
            $isAnswered = $userAnswer && !is_null($userAnswer->answer);
            $isCorrect = $userAnswer ? $userAnswer->is_correct : false;
            $studentAnswerId = $userAnswer ? $userAnswer->answer : null;

            // Siapkan data options
            $optionsData = [];
            if ($question->options->count() > 0) {
                foreach ($question->options as $option) {
                    $isStudentAnswer = $studentAnswerId == $option->id;
                    $isCorrectOption = $option->is_correct;

                    $optionsData[] = [
                        'option' => $option->option,
                        'text' => $option->text,
                        'is_correct' => $isCorrectOption,
                        'is_student_answer' => $isStudentAnswer,
                        'image' => $option->image,
                    ];
                }
            }

            $questionsData[] = [
                'id' => $question->id,
                'number' => $index + 1,
                'body' => $question->badan_soal,
                'question_text' => $question->kalimat_tanya,
                'image' => $question->image,
                'category' => $question->category?->name ?? 'Tidak ada kategori',
                'is_answered' => $isAnswered,
                'is_correct' => $isCorrect,
                'student_feedback' => $userAnswer ? $userAnswer->feedback : '',
                'options' => $optionsData,
            ];
        }

        // Pagination manual
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = array_slice($questionsData, ($currentPage - 1) * $perPage, $perPage);
        $paginatedQuestions = new LengthAwarePaginator($currentItems, count($questionsData), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);

        $status = $this->determineStatus($exam);
        return view('pssk.grading.feedback', compact('exam', 'attempt', 'allUserAnswers', 'paginatedQuestions', 'student', 'user', 'status'));
    }

    public function update(Request $request, $examCode, $nim)
    {
        $student = Student::where('nim', $nim)->firstOrFail();
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('user_id', $student->user_id)->firstOrFail();

        // simpan feedback keseluruhan
        $attempt->feedback = $request->input('overall_feedback');
        $attempt->save();

        // simpan feedback per soal
        if ($request->has('feedback')) {
            foreach ($request->input('feedback') as $questionId => $feedback) {
                $answer = ExamAnswer::where('exam_question_id', $questionId)->where('exam_id', $exam->id)->where('user_id', $student->user_id)->first();

                if ($answer) {
                    $answer->feedback = $feedback;
                    $answer->save();
                } elseif (!$answer) {
                    // Jika answer tidak ada, buat baru
                    $answer = ExamAnswer::create([
                        'exam_question_id' => $questionId,
                        'exam_id' => $exam->id,
                        'user_id' => $student->user_id,
                        'feedback' => $feedback,
                        'answer' => null, // atau null
                        'is_correct' => false,
                        'score' => 0,
                        'marked_doubt' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Feedback Berhasil Disimpan!',
                'data' => [
                    'overall_feedback' => $attempt->feedback,
                    'updated_at' => now()->format('d-m-Y H:i:s'),
                ],
            ]);
        }
        return back()->with('success', 'Feedback Berhasil Disimpan!');
    }

    public function publish($examCode)
    {
        $exam = Exam::where('exam_code', $examCode)
            ->with(['attempts.user', 'course'])
            ->firstOrFail();
        $exam->update(['is_published' => true]);
        $exam->attempts()->update(['grading_status' => 'published']);

        foreach ($exam->attempts as $attempt) {
            $user = $attempt->user;
            if ($user && $user->email) {
                Mail::to($user->email)->queue(new ExamPublishedNotification($exam, $user));
            }
        }

        return back()->with('success', 'Exam berhasil dipublish! Email notifikasi sedang dikirim ke mahasiswa.');
    }

    public function show($examCode, Request $request)
    {
        // Load hanya yang diperlukan untuk halaman utama
        $exam = Exam::with([
            'course.lecturers',
            'questions' => function ($q) {
                $q->with(['category', 'options']);
            }
        ])
            ->withCount('questions')
            ->withCount('attempts')
            ->where('exam_code', $examCode)
            ->firstOrFail();

        // Gunakan query terpisah untuk data besar
        $difficultyLevel = DifficultyLevel::pluck('name');
        $activeTab = $request->get('tab', 'results');

        // Paginate ranking results
        $rankingPaginator = $this->buildRankingResultsOptimized($exam, $request);

        // Analisis soal dengan pagination dan query teroptimasi
        $allQuestionAnalysis = $this->analyzeQuestionsOptimized($exam, $request);

        // Filter berdasarkan difficulty level
        if ($request->filled('difficulty_level')) {
            $allQuestionAnalysis = $allQuestionAnalysis->filter(function ($q) use ($request) {
                return $q['difficulty_level'] === $request->difficulty_level;
            })->values();
        }

        // Sorting
        $sort = $request->get('sort', 'question_id');
        $dir = $request->get('dir', 'asc');

        if ($sort && $allQuestionAnalysis->isNotEmpty()) {
            $allQuestionAnalysis = $allQuestionAnalysis->sortBy(
                function ($q) use ($sort) {
                    return $q[$sort] ?? null;
                },
                SORT_REGULAR,
                $dir === 'desc'
            )->values();
        }

        // Pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $allQuestionAnalysis->slice(($currentPage - 1) * $perPage, $perPage);

        $questionAnalysisPaginator = new LengthAwarePaginator(
            $currentItems,
            $allQuestionAnalysis->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $optionsAnalysis = $currentItems->pluck('options', 'question_id') ?? collect();

        // Analytics dengan data terbatas
        $analytics = $this->buildAnalyticsOptimized($exam, $rankingPaginator);

        // Chart data menggunakan query terpisah
        $chartData = $this->prepareChartDataOptimized($exam, $request);

        $status = $this->determineStatus($exam);
        $agent = new Agent();

        if ($agent->isMobile()) {
            return view('pssk.grading.show.mobile.index_mobile', compact(
                'activeTab',
                'exam',
                'rankingPaginator',
                'analytics',
                'questionAnalysisPaginator',
                'optionsAnalysis',
                'chartData',
                'status',
                'sort',
                'dir',
                'difficultyLevel'
            ));
        }
        return view('pssk.grading.show.index', compact(
            'activeTab',
            'exam',
            'rankingPaginator',
            'analytics',
            'questionAnalysisPaginator',
            'optionsAnalysis',
            'chartData',
            'status',
            'sort',
            'dir',
            'difficultyLevel'
        ));
    }

    private function determineStatus($exam)
    {
        return $exam->status === 'ended' ? ($exam->is_published ? 'published' : 'ungraded') : $exam->status;
    }

    public function buildRankingResultsOptimized($exam, Request $request)
    {
        $totalAttempts = $exam->attempts()->count();
        if ($totalAttempts === 0) {
            return new LengthAwarePaginator(collect(), 0, 10, 1);
        }

        $perPage = 10;
        $page = $request->get('page', 1);
        $nameFilter = $request->get('name');
        $sortField = $request->get('sort', 'rank');
        $sortDir = $request->get('dir', 'asc');

        // Query dengan joins untuk efisiensi
        $attemptsQuery = ExamAttempt::query()
            ->select(
                'exam_attempts.*',
                'users.name as user_name',
                'users.id as user_id',
                'students.nim as student_nim',
                \DB::raw('(SELECT COUNT(*) FROM exam_answers 
                      WHERE exam_answers.exam_id = exam_attempts.exam_id 
                      AND exam_answers.user_id = exam_attempts.user_id 
                      AND exam_answers.is_correct = 1) as correct_count'),
                \DB::raw('(SELECT COUNT(*) FROM exam_answers 
                      WHERE exam_answers.exam_id = exam_attempts.exam_id 
                      AND exam_answers.user_id = exam_attempts.user_id) as total_answered')
            )
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->leftJoin('students', 'students.user_id', '=', 'users.id')
            ->where('exam_attempts.exam_id', $exam->id);

        // Filter nama/NIM
        if ($nameFilter) {
            $attemptsQuery->where(function ($q) use ($nameFilter) {
                $q->where('users.name', 'like', "%{$nameFilter}%")
                    ->orWhere('students.nim', 'like', "%{$nameFilter}%");
            });
        }

        // Hitung total untuk pagination
        $total = $attemptsQuery->count();

        // Sorting dan pagination
        switch ($sortField) {
            case 'rank':
            case 'score':
                $attemptsQuery->orderBy('correct_count', $sortDir === 'asc' ? 'desc' : 'asc'); // rank ascending = score descending
                break;
            case 'nim':
                $attemptsQuery->orderBy('students.nim', $sortDir);
                break;
            case 'name':
                $attemptsQuery->orderBy('users.name', $sortDir);
                break;
            default:
                $attemptsQuery->orderBy('correct_count', 'desc');
        }

        $attempts = $attemptsQuery->paginate($perPage);

        // Ambil semua kategori soal untuk referensi
        $categories = $exam->questions()
            ->with('category')
            ->get()
            ->pluck('category')
            ->unique('id')
            ->filter()
            ->values();

        // Transform hasil dengan rank
        $rank = ($page - 1) * $perPage + 1;
        $results = $attempts->map(function ($attempt) use ($exam, &$rank, $categories) {
            // Ambil jawaban user
            $userAnswers = ExamAnswer::where('exam_id', $exam->id)
                ->where('user_id', $attempt->user_id)
                ->with('question.category')
                ->get();

            $feedbackCount = $userAnswers->whereNotNull('feedback')->count();

            // Hitung total score
            $totalScore = $userAnswers->sum('score');

            // Buat categories_result
            $categoriesResult = collect();

            foreach ($categories as $category) {
                $categoryId = $category->id;
                $answersInCategory = $userAnswers->filter(function ($answer) use ($categoryId) {
                    return $answer->question && $answer->question->category_id == $categoryId;
                });

                $totalCorrectInCategory = $answersInCategory->where('is_correct', true)->count();
                $totalQuestionsInCategory = $exam->questions()
                    ->where('category_id', $categoryId)
                    ->count();

                $categoriesResult->push([
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                    'total_correct' => $totalCorrectInCategory,
                    'total_wrong' => max($totalQuestionsInCategory - $totalCorrectInCategory, 0),
                    'total_score' => $answersInCategory->sum('score'),
                    'total_question' => $totalQuestionsInCategory,
                    'percentage' => $totalQuestionsInCategory > 0
                        ? round(($totalCorrectInCategory / $totalQuestionsInCategory) * 100, 2)
                        : 0,
                ]);
            }

            // Tambahkan kategori untuk soal tanpa kategori
            $uncategorizedAnswers = $userAnswers->filter(function ($answer) {
                return !$answer->question || !$answer->question->category_id;
            });

            if ($uncategorizedAnswers->count() > 0 || $exam->questions()->whereNull('category_id')->count() > 0) {
                $totalCorrectUncategorized = $uncategorizedAnswers->where('is_correct', true)->count();
                $totalQuestionsUncategorized = $exam->questions()->whereNull('category_id')->count();

                $categoriesResult->push([
                    'category_id' => null,
                    'category_name' => 'Uncategorized',
                    'total_correct' => $totalCorrectUncategorized,
                    'total_wrong' => max($totalQuestionsUncategorized - $totalCorrectUncategorized, 0),
                    'total_score' => $uncategorizedAnswers->sum('score'),
                    'total_question' => $totalQuestionsUncategorized,
                    'percentage' => $totalQuestionsUncategorized > 0
                        ? round(($totalCorrectUncategorized / $totalQuestionsUncategorized) * 100, 2)
                        : 0,
                ]);
            }

            return [
                'rank' => $rank++,
                'student' => (object)[
                    'id' => $attempt->user_id,
                    'name' => $attempt->user_name
                ],
                'student_data' => (object)[
                    'nim' => $attempt->student_nim
                ],
                'attempt' => $attempt,
                'answers' => $userAnswers,
                'categories_result' => $categoriesResult, // Tambahkan ini!
                'total_answered' => $attempt->total_answered ?? 0,
                'total_questions' => $exam->questions_count,
                'correct_answers' => $attempt->correct_count ?? 0,
                'total_score' => $totalScore,
                'score_percentage' => $exam->questions_count > 0
                    ? round((($attempt->correct_count ?? 0) / $exam->questions_count) * 100, 2)
                    : 0,
                'completed_at' => $attempt->completed_at,
                'feedback' => $feedbackCount,
                'grading_status' => $attempt->grading_status,
            ];
        })->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function buildAnalyticsOptimized($exam, $rankingPaginator)
    {
        $totalQuestions = $exam->questions_count;

        if ($totalQuestions == 0) {
            return [
                'total_students' => 0,
                'total_question' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'completion_rate' => 0,
            ];
        }

        // Gunakan raw query dengan nilai langsung atau binding yang konsisten
        $stats = DB::select("
        SELECT 
            COUNT(*) as total_students,
            AVG(
                (SELECT COUNT(*) FROM exam_answers 
                 WHERE exam_answers.exam_id = exam_attempts.exam_id 
                 AND exam_answers.user_id = exam_attempts.user_id 
                 AND exam_answers.is_correct = 1) * 100.0 / ?
            ) as avg_score,
            MAX(
                (SELECT COUNT(*) FROM exam_answers 
                 WHERE exam_answers.exam_id = exam_attempts.exam_id 
                 AND exam_answers.user_id = exam_attempts.user_id 
                 AND exam_answers.is_correct = 1) * 100.0 / ?
            ) as max_score,
            MIN(
                (SELECT COUNT(*) FROM exam_answers 
                 WHERE exam_answers.exam_id = exam_attempts.exam_id 
                 AND exam_answers.user_id = exam_attempts.user_id 
                 AND exam_answers.is_correct = 1) * 100.0 / ?
            ) as min_score,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count 
        FROM exam_attempts 
        WHERE exam_id = ?
    ", [$totalQuestions, $totalQuestions, $totalQuestions, $exam->id]);

        $stats = $stats[0]; // DB::select mengembalikan array

        return [
            'total_students' => $stats->total_students ?? 0,
            'total_question' => $totalQuestions,
            'average_score' => round($stats->avg_score ?? 0, 2),
            'highest_score' => round($stats->max_score ?? 0, 2),
            'lowest_score' => round($stats->min_score ?? 0, 2),
            'completion_rate' => $stats->total_students > 0
                ? round(($stats->completed_count / $stats->total_students) * 100, 2)
                : 0,
        ];
    }
    public function analyzeQuestionsOptimized($exam, Request $request)
    {
        $totalStudents = $exam->attempts()->count();

        if ($totalStudents === 0) {
            return collect();
        }

        // Ambil data dengan chunking
        $questions = $exam->questions()->with(['options', 'category'])->get();

        return $questions->map(function ($question) use ($exam, $totalStudents) {
            // Hitung jawaban benar menggunakan query langsung
            $correctCount = ExamAnswer::where('exam_id', $exam->id)
                ->where('exam_question_id', $question->id)
                ->where('is_correct', true)
                ->count();

            $correctPercentage = $totalStudents ? round(($correctCount / $totalStudents) * 100, 2) : 0;

            // Analisis opsi
            $options = $question->options->map(function ($opt) use ($exam, $question, $totalStudents) {
                $count = ExamAnswer::where('exam_id', $exam->id)
                    ->where('exam_question_id', $question->id)
                    ->where('answer', $opt->id)
                    ->count();

                return [
                    'option_id' => $opt->id,
                    'option_text' => $opt->text,
                    'is_correct' => $opt->is_correct,
                    'count' => $count,
                    'percentage' => $totalStudents ? round(($count / $totalStudents) * 100, 2) : 0,
                ];
            })->values();

            // Hitung discrimination index
            $discriminationIndex = $this->calculateDiscriminationIndexOptimized($exam, $question);

            return [
                'question_id' => $question->id,
                'question_text' => $question->badan_soal,
                'image' => $question->image,
                'question' => $question->kalimat_tanya,
                'correct_percentage' => $correctPercentage,
                'correct_count' => $correctCount,
                'total_students' => $totalStudents,
                'options' => $options,
                'discrimination_index' => $discriminationIndex, // Tambahkan ini!
                'difficulty_level' => $this->getDifficultyLevel($correctCount, $totalStudents),
                'is_anulir' => $question->is_anulir ?? false,
            ];
        });
    }

    private function calculateDiscriminationIndexOptimized($exam, $question)
    {
        $totalStudents = $exam->attempts_count;

        // Jika jumlah siswa kurang dari 10, return 0 karena tidak representatif
        if ($totalStudents < 10) {
            return 0;
        }

        $groupSize = max(1, round($totalStudents * 0.27));

        // Ambil top students berdasarkan total jawaban benar
        $topUserIds = DB::table('exam_attempts')
            ->where('exam_id', $exam->id)
            ->select('user_id')
            ->selectSub(function ($query) use ($exam) {
                $query->from('exam_answers')
                    ->whereRaw('exam_answers.user_id = exam_attempts.user_id')
                    ->where('exam_answers.exam_id', $exam->id)
                    ->where('exam_answers.is_correct', true)
                    ->selectRaw('COUNT(*)');
            }, 'correct_count')
            ->orderBy('correct_count', 'desc')
            ->limit($groupSize)
            ->pluck('user_id')
            ->toArray();

        // Ambil bottom students
        $bottomUserIds = DB::table('exam_attempts')
            ->where('exam_id', $exam->id)
            ->select('user_id')
            ->selectSub(function ($query) use ($exam) {
                $query->from('exam_answers')
                    ->whereRaw('exam_answers.user_id = exam_attempts.user_id')
                    ->where('exam_answers.exam_id', $exam->id)
                    ->where('exam_answers.is_correct', true)
                    ->selectRaw('COUNT(*)');
            }, 'correct_count')
            ->orderBy('correct_count', 'asc')
            ->limit($groupSize)
            ->pluck('user_id')
            ->toArray();

        // Hitung jawaban benar untuk top dan bottom group
        $topCorrect = ExamAnswer::where('exam_id', $exam->id)
            ->where('exam_question_id', $question->id)
            ->whereIn('user_id', $topUserIds)
            ->where('is_correct', true)
            ->count();

        $bottomCorrect = ExamAnswer::where('exam_id', $exam->id)
            ->where('exam_question_id', $question->id)
            ->whereIn('user_id', $bottomUserIds)
            ->where('is_correct', true)
            ->count();

        // Discrimination Index = (Upper Group % Correct) - (Lower Group % Correct)
        $topPercentage = $groupSize > 0 ? $topCorrect / $groupSize : 0;
        $bottomPercentage = $groupSize > 0 ? $bottomCorrect / $groupSize : 0;
        $discriminationIndex = $topPercentage - $bottomPercentage;

        return round($discriminationIndex, 3);
    }
    private function getDifficultyLevel($correctAnswers, $totalStudents)
    {
        static $difficultyCache = null;

        if ($difficultyCache === null) {
            $difficultyCache = DifficultyLevel::pluck('name')->toArray();
        }

        if ($totalStudents === 0) {
            return 'N/A';
        }

        $ratio = $correctAnswers / $totalStudents;
        return DifficultyLevel::forRatio($ratio)->value('name') ?? 'N/A';
    }

    private function prepareChartDataOptimized($exam)
    {
        // Grafik kesulitan soal
        $difficultyData = [
            'Easy' => 0,
            'Medium' => 0,
            'Hard' => 0
        ];

        $totalStudents = $exam->attempts()->count();

        if ($totalStudents > 0) {
            // Ambil semua soal dengan hitungan jawaban benar
            $questions = ExamQuestion::where('exam_id', $exam->id)
                ->withCount(['answers as correct_count' => function ($q) use ($exam) {
                    $q->where('exam_id', $exam->id)->where('is_correct', true);
                }])
                ->get();

            foreach ($questions as $question) {
                $ratio = $totalStudents > 0 ? $question->correct_count / $totalStudents : 0;

                // Tentukan tingkat kesulitan
                if ($ratio >= 0.76) {
                    $difficultyData['Easy']++;
                } elseif ($ratio >= 0.21) {
                    $difficultyData['Medium']++;
                } else {
                    $difficultyData['Hard']++;
                }
            }
        }

        // Grafik distribusi skor
        $scoreRanges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0
        ];

        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->withCount(['answers as correct_count' => function ($q) {
                $q->where('is_correct', true);
            }])
            ->get();

        foreach ($attempts as $attempt) {
            $totalQuestions = $exam->questions_count;
            $correct = $exam->answers->where('user_id', $attempt->user_id)->where('is_correct', true)->count();
            $percentage = $totalQuestions ? round(($correct / $totalQuestions) * 100, 2) : 0;

            if ($percentage <= 20) $scoreRanges['0-20']++;
            elseif ($percentage <= 40) $scoreRanges['21-40']++;
            elseif ($percentage <= 60) $scoreRanges['41-60']++;
            elseif ($percentage <= 80) $scoreRanges['61-80']++;
            else $scoreRanges['81-100']++;
        }

        // Grafik discrimination index - hitung untuk sample jika jumlah siswa banyak
        $discriminationData = [
            'Excellent (>0.4)' => 0,
            'Good (0.3-0.39)' => 0,
            'Fair (0.2-0.29)' => 0,
            'Poor (0.1-0.19)' => 0,
            'Very Poor (<0.1)' => 0,
        ];

        // Hitung discrimination index hanya jika jumlah siswa tidak terlalu banyak
        // untuk menghindari performance issue
        if ($exam->attempts_count <= 100) { // Batasi hanya untuk 100 siswa atau kurang
            $questions = $exam->questions;
            foreach ($questions as $question) {
                $di = $this->calculateDiscriminationIndexOptimized($exam, $question);

                if ($di > 0.4) {
                    $discriminationData['Excellent (>0.4)']++;
                } elseif ($di >= 0.3) {
                    $discriminationData['Good (0.3-0.39)']++;
                } elseif ($di >= 0.2) {
                    $discriminationData['Fair (0.2-0.29)']++;
                } elseif ($di >= 0.1) {
                    $discriminationData['Poor (0.1-0.19)']++;
                } else {
                    $discriminationData['Very Poor (<0.1)']++;
                }
            }
        }

        return [
            'difficulty' => $difficultyData,
            'discrimination' => $discriminationData,
            'scores' => $scoreRanges,
        ];
    }
}
