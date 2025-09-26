<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAnswer;
use Illuminate\Http\Request;

class ExamResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
            )
            ->orderBy($request->get('sort', 'exam_date'), $request->get('dir', 'desc'))
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
