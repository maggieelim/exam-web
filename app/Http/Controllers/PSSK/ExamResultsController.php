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
use App\Models\ExamStatistics;
use App\Models\Lecturer;
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

        $rankingData = $this->buildRankingResultsOptimized($exam, request(), true);
        return Excel::download(new ExamResultsExport($rankingData), $fileName);
    }

    public function downloadQuestions($examCode)
    {
        $exam = Exam::with(['course'])
            ->where('exam_code', $examCode)
            ->firstOrFail();

        $fileName = 'Question_Analysis_' .
            str_replace(' ', '_', $exam->title) .
            '_' . $exam->course->slug . '.xlsx';

        $analysis = ExamStatistics::with('question.options')
            ->where('exam_id', $exam->id)
            ->orderBy('exam_question_id')
            ->get();

        return Excel::download(
            new ExamQuestionsAnalysisExport($analysis),
            $fileName
        );
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
                'question_text' => filled($question->badan_soal)
                    ? $question->badan_soal
                    : $question->kalimat_tanya,
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
        $perPage = 20;
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
        $coordinator = CourseCoordinator::where('course_id', $exam->course_id)->where('semester_id', $exam->semester_id)->where('role', 'koordinator')->first();
        $results = $this->buildRankingResultsOptimized($exam, request(), true);

        $results = collect($results);

        $results->chunk(50)->each(function ($chunk) use ($exam, $coordinator) {
            foreach ($chunk as $result) {
                $userId = $result['student']->id;
                $user = $exam->attempts->firstWhere('user_id', $userId)?->user;
                if ($user && $user->email) {

                    Mail::to($user->email)->queue(
                        new ExamPublishedNotification(
                            $exam,
                            $user,
                            $result['categories_result'],
                            $coordinator
                        )
                    );
                }
            }
        });
        return back()->with('success', 'Exam berhasil dipublish! Email notifikasi sedang dikirim ke mahasiswa.');
    }

    public function show($examCode, Request $request)
    {
        // Load hanya yang diperlukan untuk halaman utama
        $exam = Exam::with([
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

        $exists = ExamStatistics::where('exam_id', $exam->id)->exists();

        if (!$exists) {
            app(ExamStatisticsController::class)->generate($exam);
        }
        $statisticsQuery = ExamStatistics::where('exam_id', $exam->id);

        $statsCount = ExamStatistics::where('exam_id', $exam->id)->count();

        if ($statsCount !== $exam->questions_count) {
            app(ExamStatisticsController::class)->generate($exam);
        }

        // Filter difficulty level
        if ($request->filled('difficulty_level')) {
            $statisticsQuery->where('difficulty_level', $request->difficulty_level);
        }

        // Sorting
        $sort = $request->get('sort', 'exam_question_id');
        $dir = $request->get('dir', 'asc');

        $allowedSort = [
            'exam_question_id',
            'correct_percentage',
            'discrimination_index',
            'difficulty_level',
            'total_students',
            'correct_count'
        ];

        if (in_array($sort, $allowedSort)) {
            $statisticsQuery->orderBy($sort, $dir);
        }

        // Pagination langsung dari database (LEBIH CEPAT)
        $questionAnalysisPaginator = $statisticsQuery
            ->paginate(20)
            ->appends($request->query())
            ->withPath(url()->current());

        $optionsAnalysis = $questionAnalysisPaginator
            ->getCollection()
            ->pluck('options_summary', 'exam_question_id');

        $analytics = $this->buildAnalyticsOptimized($exam, $rankingPaginator);
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

    public function buildRankingResultsOptimized($exam, Request $request, $forExport = false)
    {
        $totalAttempts = $exam->attempts()->count();
        if ($totalAttempts === 0) {
            return new LengthAwarePaginator(collect(), 0, 20, 1);
        }

        $perPage = $forExport ? $totalAttempts : 20;
        $page = $forExport ? 1 : $request->get('page', 1);
        $nameFilter = $request->get('name');
        $sortField = $request->get('sort', 'rank');
        $sortDir = $request->get('dir', 'desc');

        // ===============================
        // QUERY UTAMA (NO SUBQUERY PER ROW)
        // ===============================
        $attemptsQuery = ExamAttempt::query()
            ->select(
                'exam_attempts.*',
                'users.name as user_name',
                'students.nim as student_nim',
                DB::raw('COUNT(exam_answers.id) as total_answered'),
                DB::raw('SUM(CASE WHEN exam_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_count')
            )
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->leftJoin('students', 'students.user_id', '=', 'users.id')
            ->leftJoin('exam_answers', function ($join) use ($exam) {
                $join->on('exam_answers.user_id', '=', 'exam_attempts.user_id')
                    ->where('exam_answers.exam_id', '=', $exam->id);
            })
            ->where('exam_attempts.exam_id', $exam->id)
            ->groupBy(
                'exam_attempts.id',
                'users.name',
                'students.nim'
            );

        $rankQuery = clone $attemptsQuery;

        $rankData = $rankQuery
            ->orderByRaw('correct_count DESC')
            ->get()
            ->values();

        $rankMap = [];
        foreach ($rankData as $index => $row) {
            $rankMap[$row->user_id] = $index + 1;
        }

        if ($nameFilter) {
            $attemptsQuery->where(function ($q) use ($nameFilter) {
                $q->where('users.name', 'like', "%{$nameFilter}%")
                    ->orWhere('students.nim', 'like', "%{$nameFilter}%");
            });
        }

        // COUNT via subquery (hindari error correct_count)
        $total = DB::table(DB::raw("({$attemptsQuery->toSql()}) as sub"))
            ->mergeBindings($attemptsQuery->getQuery())
            ->count();

        // SORT
        $sortable = [
            'nim'   => 'students.nim',
            'name'  => 'users.name',
            'score' => 'correct_count',
            'rank'  => 'correct_count',
        ];

        if (isset($sortable[$sortField])) {
            $attemptsQuery->orderBy($sortable[$sortField], $sortDir);
        } else {
            $attemptsQuery->OrderByDesc('correct_count');
        }

        $attempts = $forExport
            ? $attemptsQuery->get()
            : $attemptsQuery->forPage($page, $perPage)->get();

        // ===============================
        // AMBIL SEMUA ANSWER SEKALI (NO N+1)
        // ===============================
        $userIds = $attempts->pluck('user_id')->unique();

        $allAnswers = ExamAnswer::with('question.category')
            ->where('exam_id', $exam->id)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $questions = $exam->questions()
            ->select('id', 'category_id')
            ->get();

        $questionsByCategory = $questions->groupBy('category_id');
        $totalQuestions = $questions->count();

        $categories = $exam->questions()
            ->with('category')
            ->get()
            ->pluck('category')
            ->unique('id')
            ->filter()
            ->values();

        // ===============================
        // TRANSFORM (TANPA QUERY LAGI)
        // ===============================
        $rankMap = $rankMap;
        $results = $attempts->map(function ($attempt) use (
            $rankMap,
            $allAnswers,
            $categories,
            $questionsByCategory,
            $totalQuestions,
            $exam
        ) {

            $userAnswers = $allAnswers->get($attempt->user_id, collect());

            $feedbackCount = $userAnswers->whereNotNull('feedback')->count();
            $totalScore = $userAnswers->sum('score');

            $categoriesResult = collect();

            foreach ($categories as $category) {
                $categoryId = $category->id;

                $answersInCategory = $userAnswers->filter(function ($answer) use ($categoryId) {
                    return optional($answer->question)->category_id == $categoryId;
                });

                $totalCorrect = $answersInCategory->where('is_correct', true)->count();
                $totalQuestionsInCategory = isset($questionsByCategory[$categoryId])
                    ? $questionsByCategory[$categoryId]->count()
                    : 0;

                $categoriesResult->push([
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                    'total_correct' => $totalCorrect,
                    'total_wrong' => max($totalQuestionsInCategory - $totalCorrect, 0),
                    'total_score' => $answersInCategory->sum('score'),
                    'total_question' => $totalQuestionsInCategory,
                    'percentage' => $totalQuestionsInCategory > 0
                        ? round(($totalCorrect / $totalQuestionsInCategory) * 100, 2)
                        : 0,
                ]);
            }

            return [
                'rank' => $rankMap[$attempt->user_id] ?? null,
                'student' => (object)[
                    'id' => $attempt->user_id,
                    'name' => $attempt->user_name
                ],
                'student_data' => (object)[
                    'nim' => $attempt->student_nim
                ],
                'attempt' => $attempt,
                'answers' => $userAnswers,
                'categories_result' => $categoriesResult,
                'total_answered' => $attempt->total_answered ?? 0,
                'total_questions' => $totalQuestions,
                'correct_answers' => $attempt->correct_count ?? 0,
                'total_score' => $totalScore,
                'score_percentage' => $totalQuestions > 0
                    ? round((($attempt->correct_count ?? 0) / $totalQuestions) * 100, 2)
                    : 0,
                'completed_at' => $attempt->completed_at,
                'feedback' => $feedbackCount,
                'grading_status' => $attempt->grading_status,
            ];
        })->values();

        if ($forExport) {
            return $results;
        }

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function buildAnalyticsOptimized($exam)
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

        $attempts = ExamAttempt::where('exam_id', $exam->id)->get();

        $totalStudents = $attempts->count();

        if ($totalStudents === 0) {
            return [
                'total_students' => 0,
                'total_question' => $totalQuestions,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'completion_rate' => 0,
            ];
        }

        $correctCounts = DB::table('exam_answers')
            ->select('user_id', DB::raw('COUNT(*) as correct_count'))
            ->where('exam_id', $exam->id)
            ->where('is_correct', 1)
            ->groupBy('user_id')
            ->pluck('correct_count', 'user_id');

        $percentages = $attempts->map(function ($attempt) use ($correctCounts, $totalQuestions) {
            $correct = $correctCounts[$attempt->user_id] ?? 0;

            return ($correct / $totalQuestions) * 100;
        });


        return [
            'total_students' => $totalStudents,
            'total_question' => $totalQuestions,
            'average_score' => round($percentages->avg(), 2),
            'highest_score' => round($percentages->max(), 2),
            'lowest_score' => round($percentages->min(), 2),
            'completion_rate' => round(
                ($attempts->where('status', 'completed')->count() / $totalStudents) * 100,
                2
            ),
        ];
    }

    private function prepareChartDataOptimized($exam)
    {
        $stats = ExamStatistics::where('exam_id', $exam->id)->get();

        // Difficulty
        $difficultyData = [
            'Easy' => 0,
            'Medium' => 0,
            'Hard' => 0
        ];

        foreach ($stats as $stat) {
            if (isset($difficultyData[$stat->difficulty_level])) {
                $difficultyData[$stat->difficulty_level]++;
            }
        }

        // Discrimination
        $discriminationData = [
            'Excellent (>0.4)' => 0,
            'Good (0.3-0.39)' => 0,
            'Fair (0.2-0.29)' => 0,
            'Poor (0.1-0.19)' => 0,
            'Very Poor (<0.1)' => 0,
        ];

        foreach ($stats as $stat) {
            $di = $stat->discrimination_index;

            if ($di > 0.4) $discriminationData['Excellent (>0.4)']++;
            elseif ($di >= 0.3) $discriminationData['Good (0.3-0.39)']++;
            elseif ($di >= 0.2) $discriminationData['Fair (0.2-0.29)']++;
            elseif ($di >= 0.1) $discriminationData['Poor (0.1-0.19)']++;
            else $discriminationData['Very Poor (<0.1)']++;
        }

        // Score Distribution
        $scoreRanges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0
        ];

        $attempts = ExamAttempt::where('exam_id', $exam->id)->get();

        $correctCounts = DB::table('exam_answers')
            ->select('user_id', DB::raw('COUNT(*) as correct_count'))
            ->where('exam_id', $exam->id)
            ->where('is_correct', 1)
            ->groupBy('user_id')
            ->pluck('correct_count', 'user_id');

        foreach ($attempts as $attempt) {

            $correct = $correctCounts[$attempt->user_id] ?? 0;

            $percentage = $exam->questions_count > 0
                ? round(($correct / $exam->questions_count) * 100, 2)
                : 0;

            if ($percentage <= 20) $scoreRanges['0-20']++;
            elseif ($percentage <= 40) $scoreRanges['21-40']++;
            elseif ($percentage <= 60) $scoreRanges['41-60']++;
            elseif ($percentage <= 80) $scoreRanges['61-80']++;
            else $scoreRanges['81-100']++;
        }
        return [
            'difficulty' => $difficultyData,
            'discrimination' => $discriminationData,
            'scores' => $scoreRanges,
        ];
    }
}
