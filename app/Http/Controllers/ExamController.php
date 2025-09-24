<?php

namespace App\Http\Controllers;

use App\Imports\ExamQuestionTemplateImport;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionAnswer;
use App\Models\ExamType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $status = null)
    {
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        $user = auth()->user();
        $courses = Course::all();

        $query = Exam::with(['examType', 'course', 'creator', 'updater', 'attempts' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
            ->withCount('questions')
            ->when($user->hasRole('lecturer'), function ($q) use ($user) {
                $q->whereHas('course.lecturers', fn($q) => $q->where('users.id', $user->id));
            })
            ->when($user->hasRole('student'), function ($q) use ($user) {
                $q->whereHas('course.students', fn($q) => $q->where('users.id', $user->id));
            })
            ->when($status, function ($q) use ($status) {
                if ($status === 'previous') {
                    $q->where('exam_date', '<', now());
                } elseif ($status === 'upcoming') {
                    $q->where('exam_date', '>=', now());
                }
            })
            ->when($request->filled('title'), fn($q) => $q->where('title', 'like', '%' . $request->title . '%'))
            ->when($request->filled('course_id'), fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->filled('date_from') && $request->filled('date_to'), function ($q) use ($request) {
                $q->whereBetween('exam_date', [
                    $request->date_from . " 00:00:00",
                    $request->date_to . " 23:59:59"
                ]);
            });

        $sort = $request->get('sort', 'exam_date');
        $dir  = $request->get('dir', 'desc'); // Default desc untuk lihat yang terbaru

        $exams = $query->orderBy($sort, $dir)->paginate(10);

        // 🔀 Process each exam to determine button visibility
        $exams->getCollection()->transform(function ($exam) {
            $examTime = \Carbon\Carbon::parse($exam->exam_date);
            $examEndTime = $examTime->copy()->addMinutes($exam->duration);

            $startAllowed = now()->greaterThanOrEqualTo($examTime->copy()->subMinutes(10));
            $examEnded = now()->greaterThan($examEndTime);

            $userAttempt = $exam->attempts->first();

            $exam->has_completed = $userAttempt && $userAttempt->status === 'completed';
            $exam->has_ongoing = $userAttempt && $userAttempt->status === 'ongoing';
            $exam->show_start_button = $startAllowed && !$exam->has_completed && !$exam->has_ongoing && !$examEnded;
            $exam->exam_ended = $examEnded;

            return $exam;
        });

        if ($user->hasRole('student')) {
            return view('students.exams.index', compact('exams', 'courses', 'sort', 'dir', 'status'));
        }

        return view('exams.index', compact('exams', 'courses', 'sort', 'dir', 'status'));
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
        $exam_type = ExamType::all();
        return view('exams.create', compact('courses', 'exam_type'));
    }

    public function import(Request $request)
    {
        $this->authorize('create', Exam::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'clock' => 'required',
            'duration' => 'required|integer',
            'room' => 'nullable|string|max:100',
            'course_id' => 'required|exists:courses,id',
            'password' => 'nullable|string|max:255',
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // Gabungkan tanggal & jam
        $examDateTime = $request->exam_date . ' ' . $request->clock;

        // Simpan exam dulu
        $exam = Exam::create([
            'title' => $request->title,
            'course_id' => $request->course_id,
            'exam_type_id' => 1, // default sementara
            'exam_date' => $examDateTime,
            'room' => $request->room,
            'duration' => $request->duration,
            'password'    => $request->password,
        ]);

        Excel::import(new ExamQuestionTemplateImport($exam->id), $request->file('file'));

        return redirect()->route('exams.index')
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
        $exam = Exam::with(['course', 'examType', 'creator', 'updater'])
            ->where('exam_code', $exam_code)
            ->firstOrFail();

        // ambil query soal + opsi
        $query = $exam->questions()->with('options');

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

        return view('exams.show', compact('exam', 'questions'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $courses = Course::all();
        $exam_type = ExamType::all();
        return view('exams.edit', compact('exam', 'courses', 'exam_type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $exam_code)
    {
        $exam = Exam::where('exam_code', $exam_code)->firstOrFail();
        $request->validate([
            'title' => 'required|string|max:255',
            'exam_date' => 'required|date',
            'clock' => 'required',
            'duration' => 'required|integer|min:1',
            'room' => 'nullable|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'password'   => 'nullable|string|max:255',
        ]);

        $examDateTime = Carbon::parse($request->exam_date . ' ' . $request->clock);

        $exam->update([
            'title' => $request->title,
            'exam_date' => $examDateTime,
            'duration' => $request->duration,
            'room' => $request->room,
            'course_id' => $request->course_id,
            'updated_at' => Carbon::now(),
            'updated_by' => auth()->id(),
            'password'    => $request->password,
        ]);

        return redirect()->route('exams.edit', $exam_code)->with('success', 'Exam berhasil diperbarui');
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
