<?php

namespace App\Http\Controllers;

use App\Exports\CourseParticipantsExport;
use App\Exports\CoursesExport;
use App\Imports\CoursesImport;
use App\Models\Course;
use App\Models\CourseCoordinator;
use App\Models\CourseLecturer;
use App\Models\CourseLecturerActivity;
use App\Models\CourseSchedule;
use App\Models\CourseStudent;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class CourseController extends Controller
{
    private function getActiveSemester()
    {
        $today = Carbon::today();
        return Semester::where('start_date', '<=', $today)->where('end_date', '>=', $today)->first();
    }

    private function getSemesterId(Request $request)
    {
        $semesterId = $request->get('semester_id');

        if (!$semesterId) {
            $activeSemester = $this->getActiveSemester();
            $semesterId = $activeSemester ? $activeSemester->id : null;
        }

        return $semesterId;
    }

    private function applySemesterFilter($query, $semesterId)
    {
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

        return $query;
    }

    private function applyLecturerFilter($query, $semesterId)
    {
        $user = auth()->user();
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        if ($user->hasAnyRole('lecturer')) {
            $lecturer = Lecturer::where('user_id', $user->id)->first();
            if ($lecturer) {
                $query->whereHas('courseLecturer', function ($q) use ($lecturer, $semesterId) {
                    $q->where('lecturer_id', $lecturer->id);
                    if ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    }
                });
            }
        } elseif ($user->hasAnyRole('koordinator')) {
            $lecturer = Lecturer::where('user_id', $user->id)->first();
            if ($lecturer) {
                $query->whereHas('coordinators', function ($q) use ($lecturer, $semesterId) {
                    $q->where('lecturer_id', $lecturer->id);
                    if ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    }
                });
            }
        }
        return $query;
    }

    private function applyCounts($query, $semesterId)
    {
        $query->withCount([
            'courseStudents as student_count' => function ($q) use ($semesterId) {
                if ($semesterId) {
                    $q->where('semester_id', $semesterId);
                }
            },
        ]);

        $query->withCount([
            'courseLecturer as lecturer_count' => function ($q) use ($semesterId) {
                if ($semesterId) {
                    $q->where('semester_id', $semesterId);
                }
            },
        ]);

        return $query;
    }

    public function index(Request $request)
    {
        $agent = new Agent();
        $semesterId = $this->getSemesterId($request);
        $activeSemester = $this->getActiveSemester();
        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();

        // Base query
        $query = Course::query()->with(['lecturers', 'courseStudents', 'courseLecturer', 'coordinators']);

        // Apply filters
        $query = $this->applyLecturerFilter($query, $semesterId);
        $query = $this->applySemesterFilter($query, $semesterId);
        $query = $this->applyCounts($query, $semesterId);

        // Search filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')->orWhere('kode_blok', 'like', '%' . $request->name . '%');
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'kode_blok'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $query->orderBy($sort, $dir);

        // Pagination
        $courses = $query->paginate(15)->appends($request->all());
        if ($agent->isMobile()) {
            return view('courses.index_mobile', compact('courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
        }
        return view('courses.index', compact('courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
    }

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
            'name' => 'required|string|max:255',
            'lecturers' => 'nullable|array',
            'lecturers.*' => 'exists:users,id',
        ]);

        $data = $request->only(['kode_blok', 'name', 'semester']);
        $data['slug'] = Str::slug($data['name']);

        Course::create($data);
        return redirect()->back()->with('success', 'Course berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show($slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');

        $lecturers = CourseCoordinator::with(['lecturer.user'])
            ->where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->get();

        $students = CourseStudent::with(['student.user'])
            ->where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->paginate(20);

        return view('courses.show', compact('course', 'lecturers', 'students', 'semesterId'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new CoursesImport(), $request->file('file'));
            return redirect()->back()->with('success', 'Data course berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
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
        $semesterId = $this->getSemesterId($request);

        // Base query
        $query = Course::query()->with(['lecturers', 'courseStudents', 'courseLecturer']);

        // Apply filters (reuse the same methods)
        $query = $this->applyLecturerFilter($query, $semesterId);
        $query = $this->applySemesterFilter($query, $semesterId);
        $query = $this->applyCounts($query, $semesterId);

        // Search filter
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')->orWhere('kode_blok', 'like', '%' . $request->name . '%');
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'kode_blok'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        $query->orderBy($sort, $dir);

        // Get all results without pagination
        $courses = $query->get();

        // Semester data for file name
        $semester = Semester::with('academicYear')->findOrFail($semesterId);
        $semesterName = str_replace(['/', '\\'], '-', $semester->semester_name);
        $yearName = str_replace(['/', '\\'], '-', $semester->academicYear->year_name);

        $fileName = "Blok_Pembelajaran_tahun_akademik_{$semesterName}_{$yearName}.xlsx";

        return Excel::download(new CoursesExport($courses, $semesterId), $fileName);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($slug, Request $request)
    {
        $studentData = app(CourseStudentController::class)->getStudentData($request, $slug);
        $lecturerData = app(CourseLecturerController::class)->getLecturerData($request, $slug);

        $semesterId = $request->query('semester_id');
        $course = Course::where('slug', $slug)->firstOrFail();
        $semester = Semester::with('academicYear')->findOrFail($semesterId);

        // Ambil dosen pengampu dan dosen yang sudah dipilih
        $lecturers = Lecturer::all();

        $selectedLecturers = $lecturers; // duplikat variabel karena query sama

        // Ambil jadwal mata kuliah
        $courseSchedule = CourseSchedule::with(['course', 'semester'])
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->first();

        // Ambil teaching schedules jika jadwal ada
        $teachingSchedules = collect();
        if ($courseSchedule) {
            $teachingSchedules = TeachingSchedule::with('activity')
                ->where('course_schedule_id', $courseSchedule->id)
                ->orderBy('session_number')
                ->get()
                ->groupBy(function ($item) {
                    $name = strtolower($item->activity->activity_name);
                    return match (true) {
                        Str::contains($name, ['ujian praktikum', 'praktikum']) => 'PRAKTIKUM',
                        Str::contains($name, ['ujian skill lab', 'skill lab']) => 'SKILL LAB',
                        default => strtoupper($item->activity->activity_name),
                    };
                })
                ->map(fn($group) => $group->sortBy('activity_id')->values());
        }
        return view('courses.edit', compact('lecturerData', 'studentData', 'course', 'lecturers', 'selectedLecturers', 'semesterId', 'courseSchedule', 'semester', 'teachingSchedules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course)
    {
        $semesterId = $request->input('semester_id');
        $request->validate([
            'kode_blok' => ['required', 'string', 'max:255', Rule::unique('courses', 'kode_blok')->ignore($course->id)],
            'name' => 'required|string|max:255',
            'lecturers' => 'nullable|array',
        ]);

        $data = $request->only(['kode_blok', 'name', 'semester']);
        $data['slug'] = Str::slug($data['name']);

        $course->update($data);

        $lecturers = $request->lecturers ?? [];

        // Sync lecturers for specific semester
        $existingLecturers = $course->lecturers()->wherePivot('semester_id', $semesterId)->pluck('lecturers.id')->toArray();

        // Detach removed lecturers for this semester
        $toRemove = array_diff($existingLecturers, $lecturers);
        if (!empty($toRemove)) {
            $course->lecturers()->detach($toRemove);
        }

        // Attach new lecturers for this semester
        $toAdd = array_diff($lecturers, $existingLecturers);
        foreach ($toAdd as $lecturerId) {
            $course->lecturers()->attach($lecturerId, [
                'semester_id' => $semesterId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('courses.index', ['semester_id' => $semesterId])
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
