<?php

namespace App\Http\Controllers;

use App\Imports\ExamQuestionTemplateImport;
use App\Models\Course;
use App\Models\CourseCoordinator;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Services\SemesterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{

    public function start(Exam $exam)
    {
        $errors = [];

        if ($exam->questions()->count() === 0) {
            $errors[] = "There are no questions yet.";
        }
        if (is_null($exam->exam_date)) {
            $errors[] = 'Exam date cannot be empty.';
        }
        if (is_null($exam->duration)) {
            $errors[] = 'Duration cannot be empty.';
        }
        if (is_null($exam->password) || $exam->password === '') {
            $errors[] = 'Password cannot be empty.';
        }
        if (!empty($errors)) {
            return back()->withErrors($errors);
        }
        if ($exam->status === 'upcoming') {
            $exam->update(['status' => 'ongoing']);
        }

        return redirect()
            ->route('exams.index', ['status' => 'ongoing'])
            ->with('success', 'Exam started successfully.');
    }

    public function end(Exam $exam)
    {
        $activeAttempts = $exam->attempts()->where('status', 'in_progress')->count();

        if ($activeAttempts > 0) {
            return back()->withErrors(["There are still $activeAttempts student(s) currently taking the exam. You cannot end this exam yet."]);
        }

        if ($exam->status !== 'ended') {
            $exam->update(['status' => 'ended']);
        }

        $exam->attempts()->where('status', 'ongoing')
            ->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);
        return redirect()
            ->route('exams.index', ['status' => 'previous'])
            ->with('success', 'exam ended successfully.');
    }

    public function index(Request $request, $status = null)
    {
        $agent = new Agent();
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        $user = auth()->user();

        // ===== Semester handling =====
        $activeSemester = SemesterService::active();
        $semesterId = $request->get('semester_id') ?? optional($activeSemester)->id;
        $semesters = SemesterService::list();

        // ===== Base Exam Query =====
        $query = Exam::query()
            ->with([
                'course:id,name',
                'creator:id,name',
                'updater:id,name',
                'semester:id,semester_name',
                'attempts' => fn($q) => $q->where('user_id', $user->id),
            ])
            ->withCount('questions');

        $courses = collect();

        // ===== ROLE BASED FILTER =====
        if ($user->hasRole('koordinator')) {
            $lecturerId = Lecturer::where('user_id', $user->id)->value('id');

            $courseIds = CourseCoordinator::where('lecturer_id', $lecturerId)
                ->pluck('course_id');

            $courses = Course::whereIn('id', $courseIds)->get();
            $query->whereIn('course_id', $courseIds);
        } elseif ($user->hasRole('student')) {
            $courses = CourseStudent::where('user_id', $user->id)
                ->with('course')
                ->get()
                ->pluck('course');

            $query->whereNotNull('exam_date')
                ->whereHas(
                    'course.courseStudents',
                    fn($q) =>
                    $q->where('user_id', $user->id)
                );
        } else {
            $courses = Course::whereHas('exams')->get();
        }

        // ===== STATUS & SEMESTER =====
        $this->applyStatusFilter($query, $user, $status);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        // ===== SEARCH FILTER =====
        $query
            ->when(
                $request->filled('title'),
                fn($q) => $q->where('title', 'like', "%{$request->title}%")
            )
            ->when(
                $request->filled('course_id'),
                fn($q) => $q->where('course_id', $request->course_id)
            );

        // ===== SORTING (safe) =====
        $allowedSorts = ['exam_date', 'title', 'created_at'];
        $sort = in_array($request->get('sort'), $allowedSorts)
            ? $request->get('sort')
            : 'exam_date';

        $dir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        // ===== PAGINATION =====
        $exams = $query
            ->orderBy($sort, $dir)
            ->paginate(15)
            ->appends($request->query());

        // ===== TRANSFORM =====
        $exams->through(function ($exam) {
            if ($exam->status === 'ended') {
                $exam->status = 'previous';
            }
            return $this->mapExamAttributes($exam);
        });

        // ===== VIEW =====
        $view = $user->hasRole('student')
            ? 'students.exams.index'
            : 'exams.index';

        $viewMobile = $user->hasRole('student')
            ? 'students.exams.index'
            : 'exams.mobile.index_mobile';

        if ($agent->isMobile()) {
            return view($viewMobile, compact(
                'exams',
                'courses',
                'status',
                'semesters',
                'semesterId',
                'activeSemester'
            ));
        }

        return view($view, compact(
            'exams',
            'courses',
            'status',
            'semesters',
            'semesterId',
            'activeSemester'
        ))->with(compact('sort', 'dir'));
    }

    private function applyStatusFilter($query, $user, &$status)
    {
        if ($user->hasRole('lecturer|admin|koordinator')) {
            $query->when($status, function ($q) use ($status) {
                $map = [
                    'previous' => 'ended',
                    'upcoming' => 'upcoming',
                    'ongoing' => 'ongoing',
                ];
                if (isset($map[$status])) {
                    $q->where('status', $map[$status]);
                }
            });
        } elseif ($user->hasRole('student')) {
            if ($status === 'previous') {
                $query->where(fn($q) => $q->where('status', 'ended')->orWhereHas('attempts', fn($sub) => $sub->where('user_id', $user->id)->where('status', 'completed')));
            } else {
                // fallback: upcoming + ongoing
                $query->whereIn('status', ['upcoming', 'ongoing'])->whereDoesntHave('attempts', fn($sub) => $sub->where('user_id', $user->id)->where('status', 'completed'));
            }
        }
    }

    private function mapExamAttributes($exam)
    {
        $examStart = Carbon::parse($exam->exam_date);
        $examEnd = $examStart->copy()->addHours(6);

        $userAttempt = $exam->attempts->first();
        $examEnded = $exam->status === 'ended';

        $exam->has_completed = $userAttempt && $userAttempt->status === 'completed';
        $exam->has_ongoing = $userAttempt && $userAttempt->status === 'ongoing';
        $exam->show_start_button = !$exam->has_completed && !$exam->has_ongoing && $exam->status === 'ongoing' && !$examEnded;

        $exam->exam_ended = $examEnded;
        $exam->exam_end_time = $examEnd;
        $exam->exam_start_time = $examStart;

        return $exam;
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $today = now();

        $semesters = SemesterService::list();
        $activeSemester = SemesterService::active();

        if (!$activeSemester) {
            return back()->with('error', 'Tidak ada semester aktif saat ini.');
        }

        $lecturer = Lecturer::where('user_id', $user->id)->first();
        if ($user->hasRole('lecturer') && $lecturer) {
            $courses = CourseLecturer::with('course')->where('lecturer_id', $lecturer->id)->where('semester_id', $activeSemester->id)->get()->map(fn($cl) => $cl->course); // ambil model Course dari pivot
        } elseif ($user->hasRole('koordinator')) {
            $courses = CourseCoordinator::with('course')->where('lecturer_id', $lecturer->id)->where('semester_id', $activeSemester->id)->get()->map(fn($cl) => $cl->course);
        } else {
            $courses = Course::where('semester', $activeSemester->semester_name)->orWhere('semester', 'Ganjil/Genap')->get();
        }

        return view('exams.create', compact('courses', 'semesters', 'activeSemester'));
    }

    public function import(Request $request)
    {
        $this->authorize('create', Exam::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'duration' => 'required|integer',
            'semester_id' => 'required|integer',
            'room' => 'nullable|string|max:100',
            'course_id' => 'required|exists:courses,id',
            'password' => 'nullable|string|max:255',
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // Simpan exam dulu
        $exam = Exam::create([
            'title' => $request->title,
            'course_id' => $request->course_id,
            'exam_date' => $request->exam_date,
            'semester_id' => $request->semester_id,
            'room' => $request->room,
            'duration' => $request->duration,
            'password' => $request->password,
        ]);

        Excel::import(new ExamQuestionTemplateImport($exam->id), $request->file('file'));

        return redirect()
            ->route('exams.index', ['status' => 'upcoming'])
            ->with('success', 'Soal berhasil diimport dari Excel');
    }

    public function store(Request $request) {}

    public function show(Request $request, string $exam_code)
    {
        $agent = new Agent();
        $exam = Exam::with(['course', 'creator', 'updater'])
            ->where('exam_code', $exam_code)
            ->firstOrFail();
        $total_participants = CourseStudent::where('course_id', $exam->course_id)->where('semester_id', $exam->semester_id)->count();
        // ambil query soal + opsi
        $query = $exam->questions()->with('options');
        if ($exam->status === 'ended') {
            $status = 'previous';
        } else {
            $status = $exam->status; // upcoming / ongoing
        }
        // 🔍 Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('badan_soal', 'like', "%{$search}%")->orWhere('kalimat_tanya', 'like', "%{$search}%");
            });
        }

        // ambil data hasil query
        $questions = $query->paginate(15)->withQueryString();

        if ($agent->isMobile()) {
            return view('exams.mobile.show_mobile', compact('exam', 'questions', 'status', 'total_participants'));
        }
        return view('exams.show', compact('exam', 'questions', 'status', 'total_participants'));
    }

    public function edit($status, string $exam_code)
    {
        $user = auth()->user();
        $lecturer = Lecturer::where('user_id', $user->id)->first();

        if ($user->hasRole('koordinator')) {
            $courses = Course::with('coordinators')
                ->whereHas('coordinators', function ($q) use ($lecturer) {
                    $q->where('lecturer_id', $lecturer->id);
                })
                ->get();
        } elseif ($user->hasRole('admin')) {
            $courses = Course::all();
        }

        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();

        return view('exams.edit', compact('status', 'exam', 'courses'));
    }

    public function update(Request $request, $status, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'room' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'password' => 'nullable|string|max:255',
        ]);

        $exam->update([
            'title' => $request->title,
            'exam_date' => $request->exam_date,
            'duration' => $request->duration,
            'room' => $request->room,
            'course_id' => $request->course_id,
            'updated_at' => Carbon::now(),
            'updated_by' => auth()->id(),
            'password' => $request->password,
        ]);

        return redirect()
            ->route('exams.edit', [$status, $exam_code])
            ->with('success', 'Exam berhasil diperbarui');
    }

    public function destroy($examCode)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $questions = ExamQuestion::where('exam_id', $exam->id)->get();

        foreach ($questions as $question) {
            $question->options()->delete();
        }

        ExamQuestion::where('exam_id', $exam->id)->delete();

        $exam->delete();

        return redirect()->route('exams.index', ['status' => 'upcoming'])->with('success', 'Exam beserta semua soal berhasil dihapus!');
    }
}
