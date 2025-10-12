<?php

namespace App\Http\Controllers;

use App\Imports\ExamQuestionTemplateImport;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function start(Exam $exam)
    {
        if ($exam->status === 'upcoming') {
            $exam->update(['status' => 'ongoing']);
        }
        return redirect()->route('exams.index', ['status' => 'ongoing'])
            ->with('success', 'exam started successfully.');
    }

    public function end(Exam $exam)
    {
        if ($exam->status !== 'ended') {
            $exam->update(['status' => 'ended']);
        }

        $exam->attempts()
            ->where('status', 'ongoing')
            ->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);
        return redirect()->route('exams.index', ['status' => 'previous'])
            ->with('success', 'exam ended successfully.');
    }

    public function index(Request $request, $status = null)
    {
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        $user = auth()->user();
        $courses = Course::all();

        // Base query
        $query = Exam::with([
            'course',
            'creator',
            'updater',
            'attempts' => fn($q) => $q->where('user_id', $user->id)
        ])
            ->withCount('questions');

        // Cek role untuk courses
        if ($user->hasRole('lecturer')) {
            $courses = Course::whereHas('lecturers', fn($q) => $q->where('users.id', $user->id))->get();
        } elseif ($user->hasRole('student')) {
            $courses = Course::whereHas('students', fn($q) => $q->where('users.id', $user->id))->get();
        }

        // Status filter dari request/URL
        $this->applyStatusFilter($query, $user, $status);

        // Additional filters
        $query->when($request->filled('title'), fn($q) => $q->where('title', 'like', "%{$request->title}%"))
            ->when($request->filled('course_id'), fn($q) => $q->where('course_id', $request->course_id));

        // Sorting + pagination
        $exams = $query->orderBy(
            $request->get('sort', 'exam_date'),
            $request->get('dir', 'desc')
        )->paginate(10);

        // Mapping status ended → previous di tiap exam
        $exams->getCollection()->transform(function ($exam) {
            if ($exam->status === 'ended') {
                $exam->status = 'previous';
            }
            return $this->mapExamAttributes($exam);
        });

        // Pilih view
        $view = $user->hasRole('student') ? 'students.exams.index' : 'exams.index';

        return view($view, compact('exams', 'courses', 'status'))
            ->with(['sort' => $request->get('sort', 'exam_date'), 'dir' => $request->get('dir', 'desc')]);
    }

    /**
     * Apply status filter based on role
     */
    private function applyStatusFilter($query, $user, &$status)
    {
        if ($user->hasRole('lecturer|admin')) {
            $query->when($status, function ($q) use ($status) {
                $map = [
                    'previous' => 'ended',
                    'upcoming' => 'upcoming',
                    'ongoing'  => 'ongoing',
                ];
                if (isset($map[$status])) {
                    $q->where('status', $map[$status]);
                }
            });
        } elseif ($user->hasRole('student')) {
            if ($status === 'previous') {
                $query->where(
                    fn($q) => $q->where('status', 'ended')
                        ->orWhereHas(
                            'attempts',
                            fn($sub) => $sub
                                ->where('user_id', $user->id)
                                ->where('status', 'completed')
                        )
                );
            } else {
                // fallback: upcoming + ongoing
                $query->whereIn('status', ['upcoming', 'ongoing'])
                    ->whereDoesntHave(
                        'attempts',
                        fn($sub) => $sub
                            ->where('user_id', $user->id)
                            ->where('status', 'completed')
                    );
            }
        }
    }

    /**
     * Transform attributes for exam
     */
    private function mapExamAttributes($exam)
    {
        $examStart = Carbon::parse($exam->exam_date);
        $examEnd   = $examStart->copy()->addHours(6);

        $userAttempt = $exam->attempts->first();
        $examEnded   = $exam->status === 'ended';

        $exam->has_completed = $userAttempt && $userAttempt->status === 'completed';
        $exam->has_ongoing   = $userAttempt && $userAttempt->status === 'ongoing';
        $exam->show_start_button = !$exam->has_completed && !$exam->has_ongoing
            && $exam->status === 'ongoing' && !$examEnded;

        $exam->exam_ended      = $examEnded;
        $exam->exam_end_time   = $examEnd;
        $exam->exam_start_time = $examStart;

        return $exam;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        $user = auth()->user();
        if ($user->hasRole('lecturer')) {
            $courses = Course::whereHas('lecturers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->get();
        } else {
            $courses = Course::all();
        }
        return view('exams.create', compact('courses'));
    }

    public function import(Request $request)
    {
        $this->authorize('create', Exam::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'duration' => 'required|integer',
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
            'room' => $request->room,
            'duration' => $request->duration,
            'password'    => $request->password,
        ]);

        Excel::import(new ExamQuestionTemplateImport($exam->id), $request->file('file'));

        return redirect()->route('exams.index', ['status' => 'upcoming'])
            ->with('success', 'Soal berhasil diimport dari Excel');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}


    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $exam_code)
    {
        $exam = Exam::with(['course', 'creator', 'updater'])
            ->where('exam_code', $exam_code)
            ->firstOrFail();

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
                $q->where('badan_soal', 'like', "%{$search}%")
                    ->orWhere('kalimat_tanya', 'like', "%{$search}%");
            });
        }

        // ambil data hasil query
        $questions = $query->paginate(10)->withQueryString();
        return view('exams.show', compact('exam', 'questions', 'status'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($status, string $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $courses = Course::all();
        return view('exams.edit', compact('status', 'exam', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $status, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'room' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'password'   => 'nullable|string|max:255',
        ]);

        $exam->update([
            'title' => $request->title,
            'exam_date' => $request->exam_date,
            'duration' => $request->duration,
            'room' => $request->room,
            'course_id' => $request->course_id,
            'updated_at' => Carbon::now(),
            'updated_by' => auth()->id(),
            'password'    => $request->password,
        ]);

        return redirect()->route('exams.edit', [$status, $exam_code])->with('success', 'Exam berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($examCode)
    {
        $exam = Exam::where('exam_code', $examCode)->firstOrFail();
        $questions = ExamQuestion::where('exam_id', $exam->id)->get();

        foreach ($questions as $question) {
            $question->options()->delete();
        }

        ExamQuestion::where('exam_id', $exam->id)->delete();

        $exam->delete();

        return redirect()->route('exams.index')
            ->with('success', 'Exam beserta semua soal berhasil dihapus!');
    }
}
