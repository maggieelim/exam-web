<?php

namespace App\Http\Controllers;

use App\Models\CourseLecturer;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Lecturer;
use DB;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class OngoingExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function ongoing(Request $request, $exam_code)
    {
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        $agent = new Agent();
        $user = auth()->user();
        $lecturer = Lecturer::where('user_id', $user->id)->first();

        $exam = Exam::with(['course', 'creator', 'updater', 'attempts.user.student'])
            ->withCount('questions', 'attempts')
            ->where('exam_code', $exam_code)
            ->firstOrFail();

        if (!$user->hasRole('admin') && !$user->hasRole('koordinator') && !$lecturer) {
            abort(403, 'Unauthorized access to this exam.');
        }

        $attemptsQuery = $exam->attempts()->with([
            'user.student',
            'answers' => function ($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            },
        ])->where('exam_id', $exam->id);

        if ($request->filled('status')) {
            $attemptsQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $attemptsQuery->whereHas('user.student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('dir', 'desc');

        if (in_array($sort, ['nim', 'name'])) {
            $attemptsQuery
                ->join('users', 'exam_attempts.user_id', '=', 'users.id')
                ->leftJoin('students', 'users.id', '=', 'students.user_id')
                ->select('exam_attempts.*')
                ->orderBy($sort === 'nim' ? 'students.nim' : 'users.name', $dir);
        } else {
            $attemptsQuery->orderBy($sort, $dir);
        }

        $attempts = $attemptsQuery->orderBy($sort, $dir)->paginate(25)->withQueryString();

        $attempts->getCollection()->transform(function ($attempt) use ($exam) {
            $student = $attempt->user->student;
            $answeredCount = $exam->answers->where('user_id', $attempt->user_id)->where('answer', !null)->count();

            $totalQuestions = $exam->questions->count();

            $status = $attempt->status;
            $statusBadge = $this->getAttemptStatusBadge($attempt);

            $timeSpent = null;
            if ($attempt->created_at && $attempt->updated_at) {
                $timeSpent = $attempt->updated_at->diff($attempt->created_at)->format('%H:%I:%S');
            }

            return [
                'id' => $attempt->id,
                'user_id' => $attempt->user_id,
                'student_name' => $attempt->user->name,
                'nim' => $student ? $student->nim : 'N/A',
                'answered_count' => $answeredCount,
                'total_questions' => $totalQuestions,
                'progress_percentage' => $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0,
                'status' => $status,
                'status_badge' => $statusBadge,
                'started_at' => $attempt->created_at,
                'updated_at' => $attempt->updated_at,
                'time_spent' => $timeSpent,
                'is_active' => $this->isAttemptActive($attempt),
                'can_retake' => $this->canRetakeExam($attempt, $exam),
            ];
        });

        $stats = [
            'total_participants' => $exam->attempts_count,
            'active_participants' => $exam->attempts()->where('status', 'in_progress')->count(),
            'completed_participants' => $exam->attempts()->where('status', 'completed')->count(),
            'average_progress' => $exam->attempts->count() > 0 ? round($attempts->getcollection()->avg('progress_percentage')) : 0,
        ];

        $availableStatuses = [
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'idle' => 'Idle',
            'timeout' => 'Timeout',
        ];
        if ($agent->isMobile()) {
            return view('exams.ongoing.index_mobile', compact('exam', 'attempts', 'stats', 'sort', 'dir', 'availableStatuses'));
        }
        return view('exams.ongoing.index', compact('exam', 'attempts', 'stats', 'sort', 'dir', 'availableStatuses'));
    }

    private function getAttemptStatusBadge($attempt)
    {
        $statusConfig = [
            'in_progress' => ['class' => 'badge bg-warning', 'text' => 'Active'],
            'completed' => ['class' => 'badge bg-success', 'text' => 'Completed'],
            'idle' => ['class' => 'badge bg-secondary', 'text' => 'Idle'],
            'timedout' => ['class' => 'badge bg-danger', 'text' => 'Timed Out'],
        ];

        $status = $attempt->status;
        $config = $statusConfig[$status] ?? ['class' => 'badge bg-secondary', 'text' => $status];
        if ($status === 'in_progress') {
            $lastActivity = now()->diffInMinutes($attempt->updated_at);
            if ($lastActivity > 5) {
                $config = ['class' => 'badge bg-danger', 'text' => 'Idle'];
            }
        }
        return $config;
    }

    private function isAttemptActive($attempt)
    {
        if ($attempt->status !== 'in_progress') {
            return false;
        }
        if (!$attempt->updated_at) {
            return false;
        }
        return now()->diffInMinutes($attempt->updated_at) <= 5;
    }

    private function canRetakeExam($attempt, $exam)
    {
        if ($attempt->status !== 'completed') {
            return false;
        }

        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        $retakeAllowed = auth()->user()->hasRole('admin') || $exam->course->lecturers->contains(auth()->id());
        return $retakeAllowed;
    }

    public function resetAttempt(Request $request, $exam_code, $attempt_id)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $lecturer = CourseLecturer::where('course_id', $exam->course_id)->where('semester_id', $exam->semester_id)->get();
        $attempt = ExamAttempt::where('id', $attempt_id)->where('exam_id', $exam->id)->firstOrFail();

        // Authorization check
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$lecturer) {
            abort(403, 'Unauthorized action.');
        }

        $studentName = $attempt->user->name;

        try {
            DB::beginTransaction();

            ExamAnswer::where('exam_id', $exam->id)->where('user_id', $attempt->user_id)->delete();

            $attempt->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Exam attempt for {$studentName} has been deleted successfully. Student can start a new attempt.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to delete exam attempt: ' . $e->getMessage());
        }
    }

    public function endAttempt($examCode, $attemptId)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $attempt = ExamAttempt::where('exam_id', $exam->id)->where('id', $attemptId)->firstOrFail();

        if ($attempt->status === 'completed') {
            return redirect()->back()->with('error', 'Attempt sudah selesai.');
        }

        $attempt->update([
            'status' => 'completed',
            'finished_at' => now(), // opsional, kalau ada kolom ended_at
        ]);

        return redirect()->back()->with('success', 'Attempt berhasil diakhiri.');
    }

    /**
     * Bulk reset attempts
     */
    public function bulkResetAttempts(Request $request, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $attemptIds = $request->input('attempt_ids', []);
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        // Authorization check
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$exam->course->lecturers->contains($user->id)) {
            abort(403, 'Unauthorized action.');
        }

        if (empty($attemptIds)) {
            return redirect()->back()->with('error', 'No attempts selected.');
        }

        try {
            DB::beginTransaction();

            $attempts = ExamAttempt::whereIn('id', $attemptIds)->where('exam_id', $exam->id)->get();

            foreach ($attempts as $attempt) {
                // Delete answers
                ExamAnswer::where('exam_id', $exam->id)->where('user_id', $attempt->user_id)->delete();

                // Reset attempt
                $attempt->update([
                    'status' => 'not_started',
                    'started_at' => null,
                    'completed_at' => null,
                    'score' => null,
                    'feedback' => null,
                ]);
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', "Successfully reset {$attempts->count()} exam attempts.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to reset exam attempts: ' . $e->getMessage());
        }
    }
}
