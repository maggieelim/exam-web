<?php

namespace App\Http\Controllers\PSSK;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamStatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function generate(Exam $exam)
    {
        $questions = $exam->questions;
        foreach ($questions as $question) {
            $answers = ExamAnswer::where('exam_id', $exam->id)->where('exam_question_id', $question->id)->get();

            if ($answers->isEmpty()) {
                continue;
            }
            $totalStudents = $answers->pluck('user_id')->unique()->count();
            $correctCount = $answers->where('is_correct', 1)->count();
            $correctPercentage = $totalStudents > 0 ? round(($correctCount / $totalStudents) * 100, 2) : 0;
            $difficultyLevel = match (true) {
                $correctPercentage >= 75 => 'Easy',
                $correctPercentage >= 20 => 'Medium',
                default => 'Hard'
            };

            $optionSummary = DB::table('exam_answers as ea')
                ->join('exam_question_answers as eqa', 'ea.answer', '=', 'eqa.id')
                ->where('ea.exam_id', $exam->id)
                ->where('ea.exam_question_id', $question->id)
                ->select(
                    'eqa.option',
                    'eqa.is_correct',
                    DB::raw('COUNT(ea.id) as total')
                )
                ->groupBy('eqa.is_correct', 'eqa.option')
                ->orderBy('eqa.option')
                ->get()
                ->mapWithKeys(function ($row) {
                    return [
                        $row->option => [
                            'total' => $row->total,
                            'is_correct' => (bool) $row->is_correct,
                        ]
                    ];
                });

            $discriminationIndex = $this->calculateDiscrimination($exam, $question);

            ExamStatistics::updateOrCreate([
                'exam_id' => $exam->id,
                'exam_question_id' => $question->id,
            ], [
                'total_students' => $totalStudents,
                'correct_count' => $correctCount,
                'correct_percentage' => $correctPercentage,
                'discrimination_index' => $discriminationIndex,
                'difficulty_level' => $difficultyLevel,
                'options_summary' => $optionSummary,
            ]);
        }
        return true;
    }

    private function calculateDiscrimination($exam, $question)
    {
        $totalStudents = $exam->attempts_count; // atau hitung dari answers

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

    public function regenerateQuestion(Exam $exam, $question)
    {
        $answers = ExamAnswer::where('exam_id', $exam->id)
            ->where('exam_question_id', $question->id)
            ->get();

        if ($answers->isEmpty()) {
            ExamStatistics::where([
                'exam_id' => $exam->id,
                'exam_question_id' => $question->id
            ])->delete();

            return;
        }

        $totalStudents = $answers->pluck('user_id')->unique()->count();

        if ($question->is_anulir) {

            $correctCount = $totalStudents;
            $correctPercentage = 100;
            $difficultyLevel = 'Easy';
            $discriminationIndex = 0;
        } else {

            $correctCount = $answers->where('is_correct', 1)->count();

            $correctPercentage = $totalStudents > 0
                ? round(($correctCount / $totalStudents) * 100, 2)
                : 0;

            $difficultyLevel = match (true) {
                $correctPercentage >= 75 => 'Easy',
                $correctPercentage >= 20 => 'Medium',
                default => 'Hard'
            };

            $discriminationIndex = $this->calculateDiscriminationSingle(
                $exam->id,
                $question->id
            );
        }

        $optionSummary = DB::table('exam_answers as ea')
            ->join('exam_question_answers as eqa', 'ea.answer', '=', 'eqa.id')
            ->where('ea.exam_id', $exam->id)
            ->where('ea.exam_question_id', $question->id)
            ->select(
                'eqa.option',
                'eqa.is_correct',
                DB::raw('COUNT(ea.id) as total')
            )
            ->groupBy('eqa.is_correct', 'eqa.option')
            ->orderBy('eqa.option')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    $row->option => [
                        'total' => $row->total,
                        'is_correct' => (bool) $row->is_correct,
                    ]
                ];
            });

        ExamStatistics::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'exam_question_id' => $question->id,
            ],
            [
                'total_students' => $totalStudents,
                'correct_count' => $correctCount,
                'correct_percentage' => $correctPercentage,
                'discrimination_index' => $discriminationIndex,
                'difficulty_level' => $difficultyLevel,
                'options_summary' => $optionSummary,
            ]
        );
    }
    private function calculateDiscriminationSingle($examId, $questionId)
    {
        $scores = DB::table('exam_answers')
            ->select('user_id', DB::raw('SUM(is_correct) as total_score'))
            ->where('exam_id', $examId)
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->get();

        $count = $scores->count();
        if ($count < 10) return 0;

        $groupSize = (int) floor($count * 0.27);

        $topUsers = $scores->take($groupSize)->pluck('user_id');
        $bottomUsers = $scores->reverse()->take($groupSize)->pluck('user_id');

        $topCorrect = ExamAnswer::where('exam_id', $examId)
            ->where('exam_question_id', $questionId)
            ->whereIn('user_id', $topUsers)
            ->where('is_correct', 1)
            ->count();

        $bottomCorrect = ExamAnswer::where('exam_id', $examId)
            ->where('exam_question_id', $questionId)
            ->whereIn('user_id', $bottomUsers)
            ->where('is_correct', 1)
            ->count();

        return $groupSize > 0
            ? round(($topCorrect - $bottomCorrect) / $groupSize, 2)
            : 0;
    }
}
