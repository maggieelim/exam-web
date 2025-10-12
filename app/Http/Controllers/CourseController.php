<?php

namespace App\Http\Controllers;

use App\Exports\CourseParticipantsExport;
use App\Exports\CourseStudentsExport;
use App\Imports\CoursesImport;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\Semester;
use App\Models\User;
use Carbon\Carbon;
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
        $query = Course::with(['lecturers', 'courseStudents']);
        $user = auth()->user();
        $semesterId = $request->get('semester_id'); // ID dari tabel semester
        $today = Carbon::today();

        // 🔹 Cari Semester aktif berdasarkan tanggal sekarang
        $activeSemester = Semester::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        // Jika tidak ada filter semester_id, gunakan semester aktif
        if (!$semesterId && $activeSemester) {
            $semesterId = $activeSemester->id;
        }

        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();

        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */
        if ($user->hasRole('lecturer')) {
            $query->whereHas('lecturers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // Filter course berdasarkan semester_name dari semester yang dipilih
        if ($semesterId) {
            $selectedSemester = Semester::find($semesterId);
            if ($selectedSemester) {
                $semesterName = strtolower($selectedSemester->semester_name);
                if ($semesterName === 'ganjil') {
                    $query->where(function ($q) {
                        $q->where('semester', 'Ganjil')
                            ->orWhereNull('semester');
                    });
                } elseif ($semesterName === 'genap') {
                    $query->where('semester', 'Genap');
                }
            }
        }

        // Subquery untuk menghitung student berdasarkan semester_id
        $query->withCount(['courseStudents as student_count' => function ($q) use ($semesterId) {
            if ($semesterId) {
                $q->where('semester_id', $semesterId);
            }
            // Jika tidak ada filter semester_id, hitung semua student tanpa kondisi
        }]);

        // Filter berdasarkan kode blok / nama blok
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('kode_blok', 'like', '%' . $request->name . '%');
            });
        }

        // Filter berdasarkan dosen
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
            'cover'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'lecturers' => 'nullable|array',   // jika admin yang create, bisa pilih dosen
            'lecturers.*' => 'exists:users,id', // validasi ID dosen valid
        ]);

        $data = $request->only(['kode_blok', 'name']);

        // Handle upload cover jika ada
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/covers', $filename);
            $data['cover'] = 'covers/' . $filename;
        }

        // Buat course
        $course = Course::create($data);

        $user = auth()->user();
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        if ($user->hasRole('lecturer')) {
            // selalu tambahkan dirinya sendiri
            $lecturers = [$user->id];

            // kalau dia juga input dosen lain, gabungkan
            if ($request->filled('lecturers')) {
                $lecturers = array_merge($lecturers, $request->lecturers);
            }

            $course->lecturers()->sync($lecturers);
        }

        if ($user->hasRole('admin')) {
            if ($request->filled('lecturers')) {
                $course->lecturers()->sync($request->lecturers);
            }
        }


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

        return view('courses.edit', compact('course', 'lecturers', 'selectedLecturers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'kode_blok' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courses', 'kode_blok')->ignore($course->id),
            ],
            'name'      => 'required|string|max:255',
            'cover'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'lecturers' => 'nullable|array',
        ]);

        $data = $request->only(['kode_blok', 'name', 'cover']);
        $data['slug'] = Str::slug($data['name']);

        // Handle cover
        if ($request->hasFile('cover')) {
            // Hapus cover lama
            if ($course->cover && Storage::exists('public/' . $course->cover)) {
                Storage::delete('public/' . $course->cover);
            }
            $file = $request->file('cover');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/covers', $filename);
            $data['cover'] = 'covers/' . $filename;
        }

        $course->update($data);

        // Sync lecturers
        $course->lecturers()->sync($request->lecturers ?? []);

        return  redirect()->route('courses.index')->with('success', 'Course berhasil diperbarui!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        // Hapus cover jika ada
        if ($course->cover && Storage::exists('public/' . $course->cover)) {
            Storage::delete('public/' . $course->cover);
        }

        // Lepas relasi dosen
        $course->lecturers()->detach();

        // Lepas relasi mahasiswa
        $course->students()->detach();

        // Hapus semua exam terkait course
        foreach ($course->exams as $exam) {
            // Hapus semua soal di exam
            foreach ($exam->questions as $question) {
                $question->options()->delete();
                $question->delete();
            }
            $exam->delete();
        }

        // Hapus course
        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course beserta semua data terkait berhasil dihapus!');
    }
}
