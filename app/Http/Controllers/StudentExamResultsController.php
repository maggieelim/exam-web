<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentExamResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $courses = Course::whereHas('students', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        })->get();

        $exams = Exam::with([
            'course',
            'creator',
            'updater',
            'questions.category', // Tambahkan ini untuk menghindari N+1
            'answers' => function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->with('question.category'); // Eager load question dan category
            },
            'attempts' => function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'completed');
            }
        ])
            ->withCount('questions')
            ->whereHas('attempts', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'completed');
            })
            ->when($request->title, function ($q) use ($request) {
                return $q->where('title', 'like', "%{$request->title}%");
            })
            ->when($request->course_id, function ($q) use ($request) {
                return $q->where('course_id', $request->course_id);
            })
            ->when($request->date_from && $request->date_to, function ($q) use ($request) {
                return $q->whereBetween('exam_date', [
                    "{$request->date_from} 00:00:00",
                    "{$request->date_to} 23:59:59"
                ]);
            })
            ->where('is_published', true)
            ->orderBy($request->get('sort', 'exam_date'), $request->get('dir', 'desc'))
            ->paginate(10);

        $exams->getCollection()->transform(function ($exam) {
            $questionsByCategory = $exam->questions->groupBy('category_id');
            $answersByCategory = $exam->answers->groupBy(function ($answer) {
                return $answer->question->category_id ?? 'uncategorized';
            });

            $categoriesResult = collect();

            // Process each category
            foreach ($questionsByCategory as $categoryId => $questions) {
                $categoryAnswers = $answersByCategory->get($categoryId, collect());
                $totalCorrect = $categoryAnswers->where('is_correct', true)->count();
                $totalQuestions = $questions->count();

                $categoriesResult->push([
                    'category_id'    => $categoryId,
                    'category_name'  => $questions->first()->category->name ?? 'Uncategorized',
                    'total_correct'  => $totalCorrect,
                    'total_wrong'    => $totalQuestions - $totalCorrect,
                    'total_score'    => $categoryAnswers->sum('score'),
                    'total_question' => $totalQuestions,
                    'percentage'     => $totalQuestions > 0
                        ? round(($totalCorrect / $totalQuestions) * 100, 2)
                        : 0,
                ]);
            }

            // Handle uncategorized answers (jika ada)
            $uncategorizedAnswers = $answersByCategory->get('uncategorized', collect());
            if ($uncategorizedAnswers->isNotEmpty()) {
                $totalCorrect = $uncategorizedAnswers->where('is_correct', true)->count();
                $totalQuestions = $uncategorizedAnswers->count(); // Karena tidak ada question data

                $categoriesResult->push([
                    'category_id'    => null,
                    'category_name'  => 'Uncategorized',
                    'total_correct'  => $totalCorrect,
                    'total_wrong'    => $totalQuestions - $totalCorrect,
                    'total_score'    => $uncategorizedAnswers->sum('score'),
                    'total_question' => $totalQuestions,
                    'percentage'     => $totalQuestions > 0
                        ? round(($totalCorrect / $totalQuestions) * 100, 2)
                        : 0,
                ]);
            }
            $exam->categories_result = $categoriesResult;
            return $exam;
        });

        $sort = $request->get('sort', 'exam_date');
        $dir  = $request->get('dir', 'desc');

        return view('students.results.index', compact('exams', 'courses', 'sort', 'dir'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function edit(Request $request, $exam_code) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function show($examCode)
    {
        $user = auth()->user();
        $exam = Exam::with([
            'questions.category',
            'questions.options',
            'attempts.user.student',
        ])
            ->where('exam_code', $examCode)
            ->firstOrFail();

        $student = Student::where('user_id', $user->id)->firstOrFail();
        $user = $student->user;

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Ambil semua jawaban mahasiswa
        $allUserAnswers = ExamAnswer::with(['question'])
            ->where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->get();

        // Total soal pada exam
        $totalQuestions = $exam->questions->count();

        // Perhitungan hasil per kategori
        $questionsByCategory = $exam->questions->groupBy('category_id');
        $answersByCategory = $allUserAnswers->groupBy(function ($answer) {
            return $answer->question->category_id ?? 'uncategorized';
        });
        $categoriesResult = collect();
        foreach ($questionsByCategory as $categoryId => $questions) {
            $categoryAnswers = $answersByCategory->get($categoryId, collect());
            $totalCorrect = $categoryAnswers->where('is_correct', true)->count();
            $totalQ = $questions->count();
            $categoriesResult->push([
                'category_id'    => $categoryId,
                'category_name'  => $questions->first()->category->name ?? 'Uncategorized',
                'total_correct'  => $totalCorrect,
                'total_wrong'    => $totalQ - $totalCorrect,
                'total_score'    => $categoryAnswers->sum('score'),
                'total_question' => $totalQ,
                'percentage'     => $totalQ > 0 ? round(($totalCorrect / $totalQ) * 100, 2) : 0,
            ]);
        }
        // Handle uncategorized answers (jika ada)
        $uncategorizedAnswers = $answersByCategory->get('uncategorized', collect());
        if ($uncategorizedAnswers->isNotEmpty()) {
            $totalCorrect = $uncategorizedAnswers->where('is_correct', true)->count();
            $totalQ = $uncategorizedAnswers->count();
            $categoriesResult->push([
                'category_id'    => null,
                'category_name'  => 'Uncategorized',
                'total_correct'  => $totalCorrect,
                'total_wrong'    => $totalQ - $totalCorrect,
                'total_score'    => $uncategorizedAnswers->sum('score'),
                'total_question' => $totalQ,
                'percentage'     => $totalQ > 0 ? round(($totalCorrect / $totalQ) * 100, 2) : 0,
            ]);
        }
        $exam->categories_result = $categoriesResult;

        // Filter questions berdasarkan status jawaban & feedback
        $filteredQuestions = $exam->questions->filter(function ($question) use ($allUserAnswers) {
            $userAnswer = $allUserAnswers->firstWhere('exam_question_id', $question->id);
            $isAnswered = !is_null($userAnswer);
            $isCorrect = $userAnswer ? $userAnswer->is_correct : false;
            $hasFeedback = $userAnswer && !empty($userAnswer->feedback);

            $answerStatus = request('answer_status');
            $feedbackStatus = request('feedback_status');

            // Filter berdasarkan status jawaban
            if ($answerStatus && $answerStatus !== 'all') {
                switch ($answerStatus) {
                    case 'correct':
                        if (!$isAnswered || !$isCorrect) return false;
                        break;
                    case 'incorrect':
                        if (!$isAnswered || $isCorrect) return false;
                        break;
                    case 'not_answered':
                        if ($isAnswered) return false;
                        break;
                }
            }

            // Filter berdasarkan status feedback
            if ($feedbackStatus && $feedbackStatus !== 'all') {
                switch ($feedbackStatus) {
                    case 'with_feedback':
                        if (!$hasFeedback) return false;
                        break;
                    case 'without_feedback':
                        if ($hasFeedback) return false;
                        break;
                }
            }

            return true;
        });

        // Siapkan data untuk view
        $questionsData = [];
        foreach ($filteredQuestions as $index => $question) {
            $userAnswer = $allUserAnswers->firstWhere('exam_question_id', $question->id);
            $isAnswered = !is_null($userAnswer);
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
        $paginatedQuestions = new LengthAwarePaginator(
            $currentItems,
            count($questionsData),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query()
            ]
        );

        return view('students.exams.show', compact(
            'exam',
            'attempt',
            'allUserAnswers',
            'paginatedQuestions',
            'student',
            'user',
            'totalQuestions',
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
