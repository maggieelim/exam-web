<?php

namespace App\Http\Controllers;

use App\Exports\CourseParticipantsExport;
use App\Exports\CoursesExport;
use App\Exports\CourseStudentsExport;
use App\Imports\CoursesImport;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        $semesterId = $request->get('semester_id');

        // Cari semester aktif
        $activeSemester = Semester::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if (!$semesterId && $activeSemester) {
            $semesterId = $activeSemester->id;
        }

        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();

        // Base query
        $query = Course::query()
            ->with(['lecturers', 'courseStudents', 'courseLecturer']);

        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        if ($user->hasRole('lecturer')) {
            $lecturer = Lecturer::where('user_id', $user->id)->first();
            if ($lecturer) {
                $query->whereHas('courseLecturer', function ($q) use ($lecturer, $semesterId) {
                    $q->where('lecturer_id', $lecturer->id);
                    if ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    }
                });
            }
        }


        // 🔹 Filter semester (ganjil / genap)
        if ($semesterId) {
            $selectedSemester = Semester::find($semesterId);
            if ($selectedSemester) {
                $semesterName = strtolower($selectedSemester->semester_name);
                $query->where(function ($q) use ($semesterName) {
                    if ($semesterName === 'ganjil') {
                        $q->where('semester', 'Ganjil')->orWhere('semester', 'Ganjil/Genap');
                    } elseif ($semesterName === 'genap') {
                        $q->where('semester', 'Genap')->orWhere('semester', 'Ganjil/Genap');
                    }
                });
            }
        }

        // 🔹 Hitung jumlah student di semester terkait
        $query->withCount(['courseStudents as student_count' => function ($q) use ($semesterId) {
            if ($semesterId) {
                $q->where('semester_id', $semesterId);
            }
        }]);
        $query->withCount(['courseLecturer as lecturer_count' => function ($q) use ($semesterId) {
            if ($semesterId) {
                $q->where('semester_id', $semesterId);
            }
        }]);

        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('kode_blok', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('lecturer')) {
            $query->whereHas('lecturers', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->lecturer . '%');
            });
        }

        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'kode_blok'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $query->orderBy($sort, $dir);

        // 🔹 Pagination
        $courses = $query->paginate(15)->appends($request->all());
        return view('courses.index', compact(
            'courses',
            'sort',
            'dir',
            'semesters',
            'semesterId',
            'activeSemester'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lecturers = User::role('lecturer')->get();
        return view('courses.create', compact('lecturers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_blok' => 'required|string|max:255|unique:courses,kode_blok',
            'name'      => 'required|string|max:255',
            'lecturers' => 'nullable|array',   // jika admin yang create, bisa pilih dosen
            'lecturers.*' => 'exists:users,id', // validasi ID dosen valid
        ]);

        $data = $request->only(['kode_blok', 'name', 'semester']);
        $data['slug'] = Str::slug($data['name']);

        // Buat course
        Course::create($data);
        return redirect()->back()->with('success', 'Course berhasil dibuat!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new CoursesImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data course berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show($slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');

        $lecturers = CourseLecturer::with(['lecturer.user'])
            ->where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get();

        $students = CourseStudent::with(['student.user'])
            ->where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get();

        return view('courses.show', compact('course', 'lecturers', 'students', 'semesterId'));
    }

    public function download(Request $request, $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();

        $semesterName = str_replace(['/', '\\'], '-', $semester->semester_name);
        $yearName = str_replace(['/', '\\'], '-', $semester->academicYear->year_name);

        $fileName = "Peserta-{$slug}-{$semesterName}-{$yearName}.xlsx";


        return Excel::download(new CourseParticipantsExport($course, $semesterId), $fileName);
    }

    public function export(Request $request)
    {
        // Bisa kirim filter semester atau nama via $request
        return Excel::download(new CoursesExport($request->all()), 'courses.xlsx');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');

        $lecturers = User::role('lecturer')->get();
        $selectedLecturers = CourseLecturer::where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get();

        return view('courses.edit', compact('course', 'lecturers', 'selectedLecturers', 'semesterId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $semesterId = $request->input('semester_id');
        $request->validate([
            'kode_blok' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'kode_blok')->ignore($course->id),
            ],
            'name'      => 'required|string|max:255',
            'lecturers' => 'nullable|array',
        ]);

        $data = $request->only(['kode_blok', 'name', 'semester']);
        $data['slug'] = Str::slug($data['name']);

        $course->update($data);

        $lecturers = $request->lecturers ?? [];

        // Hanya sync untuk semester yang spesifik
        $existingLecturers = $course->lecturers()
            ->wherePivot('semester_id', $semesterId)
            ->pluck('lecturers.id')
            ->toArray();

        // Detach yang dihapus untuk semester ini
        $toRemove = array_diff($existingLecturers, $lecturers);
        if (!empty($toRemove)) {
            $course->lecturers()->detach($toRemove);
        }

        // Attach yang baru untuk semester ini
        $toAdd = array_diff($lecturers, $existingLecturers);
        foreach ($toAdd as $lecturerId) {
            $course->lecturers()->attach($lecturerId, [
                'semester_id' => $semesterId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return redirect()->route('courses.index', ['semester_id' => $semesterId])
            ->with('success', 'Course berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->lecturers()->detach();
        $course->students()->detach();

        foreach ($course->exams as $exam) {
            foreach ($exam->questions as $question) {
                $question->options()->delete();
                $question->delete();
            }
            $exam->delete();
        }

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course beserta semua data terkait berhasil dihapus!');
    }
}
