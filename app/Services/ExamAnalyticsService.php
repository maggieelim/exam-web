<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\DifficultyLevel;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamAnalyticsService
{
  public function buildRankingResults(Exam $exam, $request = null)
  {
    if ($exam->attempts->isEmpty()) {
      return new LengthAwarePaginator(collect(), 0, 10, 1);
    }

    $nameFilter = $request?->get('name');
    $sortField = $request?->get('sort', 'rank');
    $sortDir   = $request?->get('dir', 'asc');

    $ranking = $exam->attempts->map(function ($attempt) use ($exam) {
      $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
      $totalQuestions = $exam->questions_count;
      $correctAnswers = $userAnswers->where('is_correct', true)->count();
      $feedback = $userAnswers->whereNotNull('feedback')->count();
      $scorePercentage = $totalQuestions ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

      $groupedAnswers = $userAnswers->groupBy(fn($a) => $a->question->category_id ?? 'uncategorized');
      $categories = $exam->questions->pluck('category')->unique('id')->values();

      $categoriesResult = $categories->map(function ($cat) use ($groupedAnswers, $exam) {
        $answers = $groupedAnswers->get($cat?->id, collect());
        $totalCorrect  = $answers->where('is_correct', 1)->count();
        $totalQuestion = $exam->questions->where('category_id', $cat?->id)->count();

        return [
          'category_id'    => $cat?->id,
          'category_name'  => $cat?->name ?? 'Uncategorized',
          'total_correct'  => $totalCorrect,
          'total_wrong'    => max($totalQuestion - $totalCorrect, 0),
          'total_score'    => $answers->sum('score'),
          'total_question' => $totalQuestion,
          'percentage'     => $totalQuestion ? round(($totalCorrect / $totalQuestion) * 100, 2) : 0,
        ];
      })->values();

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
      $ranking = $ranking->filter(function ($item) use ($nameFilterLower) {
        $nameMatch = strtolower($item['student']->name ?? '');
        $nimMatch = strtolower($item['student_data']->nim ?? '');
        return str_contains($nameMatch, $nameFilterLower) || str_contains($nimMatch, $nameFilterLower);
      })->values();
    }

    $ranking = $this->sortRanking($ranking, $sortField, $sortDir);

    $page = request('page', 1);
    $perPage = 10;
    $paged = $ranking->slice(($page - 1) * $perPage, $perPage)->values();

    return new LengthAwarePaginator(
      $paged,
      $ranking->count(),
      $perPage,
      $page,
      ['path' => request()->url(), 'query' => request()->query()]
    );
  }

  private function sortRanking($ranking, $sortField, $sortDir)
  {
    $ranking = match ($sortField) {
      'rank' => $sortDir === 'asc' ? $ranking->sortBy('rank') : $ranking->sortByDesc('rank'),
      'score' => $sortDir === 'asc' ? $ranking->sortBy('score_percentage') : $ranking->sortByDesc('score_percentage'),
      'nim'  => $sortDir === 'asc' ? $ranking->sortBy(fn($r) => $r['student_data']->nim ?? '') : $ranking->sortByDesc(fn($r) => $r['student_data']->nim ?? ''),
      'name' => $sortDir === 'asc' ? $ranking->sortBy(fn($r) => $r['student']->name ?? '') : $ranking->sortByDesc(fn($r) => $r['student']->name ?? ''),
      default => $ranking->sortBy('rank'),
    };

    return $ranking->values()->map(fn($item, $index) => array_merge($item, ['rank' => $index + 1]));
  }

  public function analyzeQuestions(Exam $exam)
  {
    $totalStudents = $exam->attempts->count();

    return $exam->questions->map(function ($question) use ($exam, $totalStudents) {
      $answers = $exam->answers->where('exam_question_id', $question->id);
      $correct = $answers->where('is_correct', true)->count();
      $correctPercentage = $totalStudents ? round(($correct / $totalStudents) * 100, 2) : 0;

      $options = $question->options->map(function ($opt) use ($answers, $totalStudents) {
        $count = $answers->where('answer', $opt->id)->count();
        return [
          'option_id' => $opt->id,
          'option_text' => $opt->text,
          'is_correct' => $opt->is_correct,
          'count' => $count,
          'percentage' => $totalStudents ? round(($count / $totalStudents) * 100, 2) : 0
        ];
      })->values();

      return [
        'question_id' => $question->id,
        'question_text' => $question->badan_soal,
        'question' => $question->kalimat_tanya,
        'correct_percentage' => $correctPercentage,
        'correct_count' => $correct,
        'total_students' => $totalStudents,
        'options' => $options,
        'discrimination_index' => $this->calculateDiscriminationIndex($exam, $question),
        'difficulty_level' => $this->getDifficultyLevel($correct, $totalStudents)
      ];
    });
  }

  public function prepareChartData($questionAnalysis, Exam $exam)
  {
    $difficultyData = collect(['Easy', 'Medium', 'Fair', 'Hard'])
      ->mapWithKeys(fn($level) => [$level => $questionAnalysis->where('difficulty_level', $level)->count()])
      ->toArray();

    $discriminationData = [
      'Excellent (>0.4)' => 0,
      'Good (0.3-0.39)'  => 0,
      'Fair (0.2-0.29)'  => 0,
      'Poor (0.1-0.19)'  => 0,
      'Very Poor (<0.1)' => 0,
    ];

    $questionAnalysis->each(function ($q) use (&$discriminationData) {
      $di = $q['discrimination_index'];
      match (true) {
        $di > 0.4   => $discriminationData['Excellent (>0.4)']++,
        $di >= 0.3  => $discriminationData['Good (0.3-0.39)']++,
        $di >= 0.2  => $discriminationData['Fair (0.2-0.29)']++,
        $di >= 0.1  => $discriminationData['Poor (0.1-0.19)']++,
        default     => $discriminationData['Very Poor (<0.1)']++,
      };
    });

    $scoreRanges = [
      '0-20'   => 0,
      '21-40'  => 0,
      '41-60'  => 0,
      '61-80'  => 0,
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
        default           => '81-100',
      };

      $scoreRanges[$range]++;
    });

    return [
      'difficulty'    => $difficultyData,
      'discrimination' => $discriminationData,
      'scores'        => $scoreRanges,
    ];
  }

  private function calculateDiscriminationIndex($exam, $question)
  {
    $totalStudents = $exam->attempts->count();
    if ($totalStudents < 10) return 0;

    $studentScores = [];
    foreach ($exam->attempts as $attempt) {
      $userAnswers = $exam->answers->where('user_id', $attempt->user_id);
      $score = $userAnswers->where('is_correct', true)->count();
      $studentScores[$attempt->user_id] = $score;
    }
    arsort($studentScores);
    $userIds = array_keys($studentScores);

    $groupSize = max(1, round($totalStudents * 0.27));

    $topUserIds = array_slice($userIds, 0, $groupSize);
    $bottomUserIds = array_slice($userIds, -$groupSize);

    $topCorrect = $exam->answers
      ->whereIn('user_id', $topUserIds)
      ->where('exam_question_id', $question->id)
      ->where('is_correct', true)
      ->count();

    $bottomCorrect = $exam->answers
      ->whereIn('user_id', $bottomUserIds)
      ->where('exam_question_id', $question->id)
      ->where('is_correct', true)
      ->count();

    return round(($topCorrect / $groupSize) - ($bottomCorrect / $groupSize), 3);
  }

  private function getDifficultyLevel($correctAnswers, $totalStudents)
  {
    if ($totalStudents === 0) return 'N/A';
    $ratio = $correctAnswers / $totalStudents;
    return DifficultyLevel::forRatio($ratio)->value('name') ?? 'N/A';
  }
}
