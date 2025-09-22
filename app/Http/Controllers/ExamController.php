<?php

namespace App\Http\Controllers;

use App\Imports\ExamQuestionTemplateImport;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
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
        $query = Exam::with(['examType', 'course', 'creator', 'updater'])->withCount('questions');
        $courses = Course::all();

        if ($status === 'previous') {
            $query->where('exam_date', '<', now());
        } elseif ($status === 'upcoming') {
            $query->where('exam_date', '>=', now());
        }

        // Filter berdasarkan title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter berdasarkan course (dropdown)
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Filter range tanggal
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('exam_date', [
                $request->date_from . " 00:00:00",
                $request->date_to . " 23:59:59"
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        // Sorting
        $sort = $request->get('sort', 'exam_date'); // default sort exam_date
        $dir = $request->get('dir', 'asc'); // default ascending

        $exams = $query->orderBy($sort, $dir)->paginate(10);

        return view('exams.index', compact('exams', 'courses', 'sort', 'dir', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // ambil semua course
        $courses = Course::all();
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
        ]);

        // Import soal untuk exam ini
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
    public function show(string $id)
    {
        //
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
