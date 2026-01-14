<?php

namespace App\Http\Controllers\PSSK;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Student;
use Carbon\Carbon;
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
            'course',
            'questions.category',
            'attempts' => fn($q) => $q->where('user_id', $user->id),
        ])
            ->where('exam_code', $examCode)
            ->firstOrFail();

        $attempt = $exam->attempts->firstOrFail();
        $exam->formatted_date = Carbon::parse($attempt->started_at)->format('l, d M Y');

        $student = Student::where('user_id', $user->id)->firstOrFail();

        $allUserAnswers = ExamAnswer::with('question.category')
            ->where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->get();

        $totalQuestions = $exam->questions->count();

        $questionsByCategory = $exam->questions->groupBy('category_id');
        $answersByCategory   = $allUserAnswers->groupBy(fn($ans) => $ans->question->category_id ?? 'uncategorized');

        $categoriesResult = $questionsByCategory->map(function ($questions, $categoryId) use ($answersByCategory) {
            $answers       = $answersByCategory->get($categoryId, collect());
            $totalCorrect  = $answers->where('is_correct', true)->count();
            $totalQuestion = $questions->count();

            return [
                'category_id'    => $categoryId,
                'category_name'  => $questions->first()->category->name ?? 'Uncategorized',
                'total_correct'  => $totalCorrect,
                'total_wrong'    => $totalQuestion - $totalCorrect,
                'total_score'    => $answers->sum('score'),
                'total_question' => $totalQuestion,
                'percentage'     => $totalQuestion > 0 ? round(($totalCorrect / $totalQuestion) * 100, 2) : 0,
            ];
        })->values();

        // Tambah kategori uncategorized jika ada
        if ($answersByCategory->has('uncategorized')) {
            $uncat = $answersByCategory->get('uncategorized');

            $categoriesResult->push([
                'category_id'    => null,
                'category_name'  => 'Uncategorized',
                'total_correct'  => $uncat->where('is_correct', true)->count(),
                'total_wrong'    => $uncat->count() - $uncat->where('is_correct', true)->count(),
                'total_score'    => $uncat->sum('score'),
                'total_question' => $uncat->count(),
                'percentage'     => round(($uncat->where('is_correct', true)->count() / max($uncat->count(), 1)) * 100, 2),
            ]);
        }

        // Masukkan ke exam object untuk dipakai di view
        $exam->categories_result = $categoriesResult;

        return view('students.exams.show', compact(
            'exam',
            'student',
            'attempt',
            'totalQuestions'
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
