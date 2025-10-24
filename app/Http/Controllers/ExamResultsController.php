<?php

namespace App\Http\Controllers;

use App\Exports\ExamQuestionsAnalysisExport;
use App\Exports\ExamResultsExport;
use App\Mail\ExamPublishedNotification;
use App\Models\CourseLecturer;
use App\Models\DifficultyLevel;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExamResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function examQueryForLecturer($user, $status = null)
    {
        $query = Exam::with(['course', 'attempts', 'semester'])
            ->withCount('questions')
            ->whereHas('course.lecturers', fn($q) => $q->where('lecturer_id', $user->id))
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

        $activeSemester = Semester::where('start_date', '<=', $today)->where('end_date', '>=', $today)->first();

        if (!$semesterId && $activeSemester) {
            $semesterId = $activeSemester->id;
        }

        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();

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

        return view('lecturer.grading.index', compact('exams', 'status', 'courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
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
        $exam = Exam::with(['course.lecturers', 'questions.category', 'attempts.user.student', 'answers.question.category'])
            ->where('exam_code', $examCode)
            ->withCount('questions')
            ->withCount('attempts')
            ->firstOrFail();

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
        // 🔄 Sorting untuk attempts
        $sort = $request->get('sort', 'finished_at');
        $dir = $request->get('dir', 'desc');

        $allowedSorts = ['finished_at', 'grading_status'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'finished_at';
        $dir = in_array(strtolower($dir), ['asc', 'desc']) ? $dir : 'desc';

        $attemptsQuery->orderBy($sort, $dir);
        $attempts = $attemptsQuery->paginate(10);

        $results = $this->buildRankingResults($exam, $request);
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

        return view('lecturer.grading.grade', compact('exam', 'results', 'attempts', 'status', 'sort', 'dir'));
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

        return view('lecturer.grading.feedback', compact('exam', 'attempt', 'allUserAnswers', 'paginatedQuestions', 'student', 'user', 'status'));
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
        $exam = Exam::with(['course.lecturers', 'questions.category', 'questions.options', 'attempts.user.student', 'answers.user.student', 'answers.question.category', 'answers.question.options'])
            ->where('exam_code', $examCode)
            ->withCount('questions')
            ->withCount('attempts')
            ->firstOrFail();

        $difficultyLevel = DifficultyLevel::pluck('name');
        $activeTab = $request->get('tab', 'results');

        $rankingPaginator = $this->buildRankingResults($exam, $request);

        // Dapatkan semua data question analysis
        $allQuestionAnalysis = $this->analyzeQuestions($exam);

        // Filter berdasarkan difficulty level jika ada
        if ($request->filled('difficulty_level')) {
            $allQuestionAnalysis = $allQuestionAnalysis
                ->filter(function ($q) use ($request) {
                    return $q['difficulty_level'] === $request->difficulty_level;
                })
                ->values();
        }

        $sort = $request->get('sort', 'question_id');
        $dir = $request->get('dir', 'asc');

        // Lakukan sorting
        if ($sort && $allQuestionAnalysis->isNotEmpty()) {
            $allQuestionAnalysis = $allQuestionAnalysis
                ->sortBy(
                    function ($q) use ($sort) {
                        return $q[$sort] ?? null;
                    },
                    SORT_REGULAR,
                    $dir === 'desc',
                )
                ->values();
        }

        // Buat pagination manual untuk question analysis
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $allQuestionAnalysis->slice(($currentPage - 1) * $perPage, $perPage);

        $questionAnalysisPaginator = new LengthAwarePaginator($currentItems, $allQuestionAnalysis->count(), $perPage, $currentPage, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        // Pastikan $optionsAnalysis selalu berupa collection, bahkan jika kosong
        $optionsAnalysis = $currentItems->pluck('options', 'question_id') ?? collect();

        $analytics = $this->buildAnalytics($exam, $rankingPaginator);
        $chartData = $this->prepareChartData($allQuestionAnalysis, $exam); // Gunakan semua data untuk chart
        $status = $this->determineStatus($exam);
        return view('lecturer.grading.show.index', compact('activeTab', 'exam', 'rankingPaginator', 'analytics', 'questionAnalysisPaginator', 'optionsAnalysis', 'chartData', 'status', 'sort', 'dir', 'difficultyLevel'));
    }

    private function determineStatus($exam)
    {
        return $exam->status === 'ended' ? ($exam->is_published ? 'published' : 'ungraded') : $exam->status;
    }

    public function buildRankingResults($exam, Request $request = null)
    {
        if ($exam->attempts->isEmpty()) {
            return new LengthAwarePaginator(collect(), 0, 10, 1);
        }

        $nameFilter = $request?->get('name');
        $sortField = $request?->get('sort', 'rank');
        $sortDir = $request?->get('dir', 'asc');

        $ranking = $exam->attempts->map(function ($attempt) use ($exam) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
            $totalQuestions = $exam->questions_count;
            $correctAnswers = $userAnswers->where('is_correct', true)->count();
            $feedback = $userAnswers->whereNotNull('feedback')->count();
            $scorePercentage = $totalQuestions ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

            $groupedAnswers = $userAnswers->groupBy(fn($a) => $a->question->category_id ?? 'uncategorized');
            $categories = $exam->questions->pluck('category')->unique('id')->values();

            $categoriesResult = $categories
                ->map(function ($cat) use ($groupedAnswers, $exam) {
                    $answers = $groupedAnswers->get($cat?->id, collect());
                    $totalCorrect = $answers->where('is_correct', 1)->count();
                    $totalQuestion = $exam->questions->where('category_id', $cat?->id)->count();

                    return [
                        'category_id' => $cat?->id,
                        'category_name' => $cat?->name ?? 'Uncategorized',
                        'total_correct' => $totalCorrect,
                        'total_wrong' => max($totalQuestion - $totalCorrect, 0),
                        'total_score' => $answers->sum('score'),
                        'total_question' => $totalQuestion,
                        'percentage' => $totalQuestion ? round(($totalCorrect / $totalQuestion) * 100, 2) : 0,
                    ];
                })
                ->values();

            return [
                'rank' => 0,
                'student' => $attempt->user,
                'student_data' => $attempt->user->student,
                'attempt' => $attempt,
                'answers' => $userAnswers,
                'categories_result' => $categoriesResult,
                'total_answered' => $userAnswers->count(),
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'total_score' => $userAnswers->sum('score'),
                'score_percentage' => $scorePercentage,
                'completed_at' => $attempt->completed_at,
                'feedback' => $feedback,
                'grading_status' => $attempt->grading_status,
            ];
        });

        if ($nameFilter) {
            $nameFilterLower = strtolower($nameFilter);
            $ranking = $ranking
                ->filter(function ($item) use ($nameFilterLower) {
                    $nameMatch = strtolower($item['student']->name ?? '');
                    $nimMatch = strtolower($item['student_data']->nim ?? '');
                    return str_contains($nameMatch, $nameFilterLower) || str_contains($nimMatch, $nameFilterLower);
                })
                ->values();
        }

        // 1️⃣ Rank default berdasarkan score tertinggi
        $ranking = $ranking->sortByDesc('score_percentage')->values();

        // 2️⃣ Isi rank sesuai urutan skor
        $ranking = $ranking->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        $ranking = match ($sortField) {
            'rank' => $sortDir === 'asc' ? $ranking->sortBy('rank') : $ranking->sortByDesc('rank'),
            'score' => $sortDir === 'asc' ? $ranking->sortBy('score_percentage') : $ranking->sortByDesc('score_percentage'),
            'nim' => $sortDir === 'asc' ? $ranking->sortBy(fn($r) => $r['student_data']->nim ?? '') : $ranking->sortByDesc(fn($r) => $r['student_data']->nim ?? ''),
            'name' => $sortDir === 'asc' ? $ranking->sortBy(fn($r) => $r['student']->name ?? '') : $ranking->sortByDesc(fn($r) => $r['student']->name ?? ''),
            default => $ranking->sortBy('rank'),
        };

        $ranking = $ranking->values();

        $page = request('page', 1);
        $perPage = 10;
        $paged = $ranking->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($paged, $ranking->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);
    }

    public function analyzeQuestions($exam)
    {
        $totalStudents = $exam->attempts->count();

        return $exam->questions->map(function ($question) use ($exam, $totalStudents) {
            $answers = $exam->answers->where('exam_question_id', $question->id);
            $correct = $answers->where('is_correct', true)->count();
            $correctPercentage = $totalStudents ? round(($correct / $totalStudents) * 100, 2) : 0;

            $options = $question->options
                ->map(function ($opt) use ($answers, $totalStudents) {
                    $count = $answers->where('answer', $opt->id)->count();
                    return [
                        'option_id' => $opt->id,
                        'option_text' => $opt->text,
                        'is_correct' => $opt->is_correct,
                        'count' => $count,
                        'percentage' => $totalStudents ? round(($count / $totalStudents) * 100, 2) : 0,
                    ];
                })
                ->values();

            return [
                'question_id' => $question->id,
                'question_text' => $question->badan_soal,
                'image' => $question->image,
                'question' => $question->kalimat_tanya,
                'correct_percentage' => $correctPercentage,
                'correct_count' => $correct,
                'total_students' => $totalStudents,
                'options' => $options,
                'discrimination_index' => $this->calculateDiscriminationIndex($exam, $question),
                'difficulty_level' => $this->getDifficultyLevel($correct, $totalStudents),
            ];
        });
    }

    private function buildAnalytics($exam, $rankingPaginator)
    {
        $collection = $rankingPaginator->getCollection();
        return [
            'total_students' => $exam->attempts_count,
            'total_question' => $exam->questions_count,
            'average_score' => $collection->avg('score_percentage') ?? 0,
            'highest_score' => $collection->max('score_percentage') ?? 0,
            'lowest_score' => $collection->min('score_percentage') ?? 0,
            'completion_rate' => $exam->attempts_count > 0 ? round(($exam->attempts->where('status', 'completed')->count() / $exam->attempts_count) * 100, 2) : 0,
        ];
    }

    private function calculateDiscriminationIndex($exam, $question)
    {
        $totalStudents = $exam->attempts->count();
        if ($totalStudents < 10) {
            return 0;
        } // Need minimum students for accurate calculation

        // Sort students by total score
        $studentScores = [];
        foreach ($exam->attempts as $attempt) {
            $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
            $score = $userAnswers->where('is_correct', true)->count();
            $studentScores[$attempt->user_id] = $score;
        }
        arsort($studentScores);
        $userIds = array_keys($studentScores);

        // Get top 27% and bottom 27%
        $groupSize = max(1, round($totalStudents * 0.27));

        $topUserIds = array_slice($userIds, 0, $groupSize);
        $bottomUserIds = array_slice($userIds, -$groupSize);

        $topCorrect = $exam->answers->whereIn('user_id', $topUserIds)->where('exam_question_id', $question->id)->where('is_correct', true)->count();

        $bottomCorrect = $exam->answers->whereIn('user_id', $bottomUserIds)->where('exam_question_id', $question->id)->where('is_correct', true)->count();

        // Discrimination Index = (Upper Group % Correct) - (Lower Group % Correct)
        $discriminationIndex = $topCorrect / $groupSize - $bottomCorrect / $groupSize;

        return round($discriminationIndex, 3);
    }

    private function getDifficultyLevel($correctAnswers, $totalStudents)
    {
        if ($totalStudents === 0) {
            return 'N/A';
        }
        $ratio = $correctAnswers / $totalStudents;
        return DifficultyLevel::forRatio($ratio)->value('name') ?? 'N/A';
    }

    private function prepareChartData($questionAnalysis, $exam)
    {
        // 🔹 Grafik kesulitan soal
        $difficultyData = collect(['Easy', 'Medium', 'Fair', 'Hard'])
            ->mapWithKeys(fn($level) => [$level => $questionAnalysis->where('difficulty_level', $level)->count()])
            ->toArray();

        // 🔹 Grafik discrimination index
        $discriminationData = [
            'Excellent (>0.4)' => 0,
            'Good (0.3-0.39)' => 0,
            'Fair (0.2-0.29)' => 0,
            'Poor (0.1-0.19)' => 0,
            'Very Poor (<0.1)' => 0,
        ];

        $questionAnalysis->each(function ($q) use (&$discriminationData) {
            $di = $q['discrimination_index'];
            match (true) {
                $di > 0.4 => $discriminationData['Excellent (>0.4)']++,
                $di >= 0.3 => $discriminationData['Good (0.3-0.39)']++,
                $di >= 0.2 => $discriminationData['Fair (0.2-0.29)']++,
                $di >= 0.1 => $discriminationData['Poor (0.1-0.19)']++,
                default => $discriminationData['Very Poor (<0.1)']++,
            };
        });

        // 🔹 Grafik distribusi skor
        $scoreRanges = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61-80' => 0,
            '81-100' => 0,
        ];

        $exam->attempts->each(function ($attempt) use (&$scoreRanges, $exam) {
            $totalQuestions = $exam->questions_count;
            $correct = $exam->answers->where('user_id', $attempt->user_id)->where('is_correct', true)->count();
            $percentage = $totalQuestions ? round(($correct / $totalQuestions) * 100, 2) : 0;

            $range = match (true) {
                $percentage <= 20 => '0-20',
                $percentage <= 40 => '21-40',
                $percentage <= 60 => '41-60',
                $percentage <= 80 => '61-80',
                default => '81-100',
            };

            $scoreRanges[$range]++;
        });

        return [
            'difficulty' => $difficultyData,
            'discrimination' => $discriminationData,
            'scores' => $scoreRanges,
        ];
    }
}
