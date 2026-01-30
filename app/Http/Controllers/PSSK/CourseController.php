<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\CourseParticipantsExport;
use App\Exports\CoursesExport;
use App\Http\Controllers\Controller;
use App\Imports\CoursesImport;
use App\Models\Course;
use App\Models\CourseCoordinator;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\User;
use App\Services\SemesterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class CourseController extends Controller
{
    private function getSemesterId(Request $request)
    {
        return $request->get('semester_id')
            ?? optional(SemesterService::active())->id;
    }

    private function applySemesterFilter($query, $semester)
    {
        if (!$semester) return $query;

        $semesterName = strtolower($semester->semester_name);

        return $query->where(function ($q) use ($semesterName) {
            if ($semesterName === 'ganjil') {
                $q->whereIn('semester', ['Ganjil', 'Ganjil/Genap']);
            } elseif ($semesterName === 'genap') {
                $q->whereIn('semester', ['Genap', 'Ganjil/Genap']);
            }
        });
    }

    private function applyLecturerFilter($query, $semesterId)
    {
        $user = auth()->user();
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('koordinator')) {
            $query->whereHas('coordinators', function ($q) use ($user, $semesterId) {
                $q->where('lecturer_id', $user->lecturer->id);
                if ($semesterId) {
                    $q->where('semester_id', $semesterId);
                }
            });
        }
        return $query;
    }

    private function applyCounts($query, $semesterId)
    {
        return $query->withCount([
            'courseStudents as student_count' => fn($q) =>
            $semesterId ? $q->where('semester_id', $semesterId) : $q,

            'courseLecturer as lecturer_count' => fn($q) =>
            $semesterId ? $q->where('semester_id', $semesterId) : $q,
        ]);
    }

    public function index(Request $request)
    {
        $agent = new Agent();
        $semesters = SemesterService::list();
        $activeSemester = SemesterService::active();
        $semesterId = $request->semester_id ?? $activeSemester->id;
        $semester = Semester::findOrFail($semesterId);

        // Base query
        $query = Course::query()->with(['lecturers', 'courseStudents', 'courseLecturer', 'coordinators']);

        // Apply filters
        $query = $this->applyLecturerFilter($query, $semesterId);
        $query = $this->applySemesterFilter($query, $semester);
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
        $courses = $query->paginate(25)->appends($request->all());
        if ($agent->isMobile()) {
            return view('pssk.courses.index_mobile', compact('courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
        }
        return view('pssk.courses.index', compact('courses', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
    }

    public function editKoor($slug, Request $request)
    {
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->find($semesterId);
        $course = Course::where('slug', $slug)->firstOrFail();

        $coordinators = CourseCoordinator::with('lecturer.user')
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereIn('role', ['koordinator', 'sekretaris'])
            ->get()
            ->keyBy('role');

        $koordinator = $coordinators->get('koordinator');
        $sekretaris = $coordinators->get('sekretaris');

        $lecturers = Lecturer::select('id', 'user_id')
            ->with('user:id,name')
            ->orderBy('id')
            ->get();
        return view('pssk.courses.editKoor', compact(
            'koordinator',
            'sekretaris',
            'lecturers',
            'course',
            'semester'
        ));
    }

    public function updateKoor(Request $request)
    {
        $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'semester_id'    => 'required|exists:semesters,id',
            'koordinator_id' => 'nullable|exists:lecturers,id',
            'sekretaris_id'  => 'nullable|exists:lecturers,id',
        ]);

        try {
            DB::beginTransaction();

            $courseId   = $request->course_id;
            $semesterId = $request->semester_id;

            // Helper: memastikan lecturer masuk ke CourseLecturer
            $addToCourseLecturer = function ($lecturerId) use ($courseId, $semesterId) {
                if ($lecturerId) {
                    CourseLecturer::updateOrCreate(
                        [
                            'course_id'   => $courseId,
                            'semester_id' => $semesterId,
                            'lecturer_id' => $lecturerId,
                        ],
                        []
                    );
                }
            };

            // roles yang ingin diatur
            $roles = [
                'koordinator' => $request->koordinator_id,
                'sekretaris'  => $request->sekretaris_id,
            ];

            $oldKoor = CourseCoordinator::where([
                'course_id' => $courseId,
                'semester_id' => $semesterId,
                'role' => 'koordinator',
            ])->first();

            $oldSekre = CourseCoordinator::where([
                'course_id' => $courseId,
                'semester_id' => $semesterId,
                'role' => 'sekretaris',
            ])->first();

            foreach ($roles as $role => $lecturerId) {
                if ($lecturerId) {
                    CourseCoordinator::updateOrCreate(
                        [
                            'course_id'   => $courseId,
                            'semester_id' => $semesterId,
                            'role'        => $role,
                        ],
                        ['lecturer_id' => $lecturerId,]
                    );

                    // Assign role (cek dulu)
                    $lecturer = Lecturer::with('user')->find($lecturerId);
                    if ($lecturer && $lecturer->user && !$lecturer->user->hasRole('koordinator')) {
                        $lecturer->user->assignRole('koordinator');
                    }

                    // Tambah ke CourseLecturer
                    $addToCourseLecturer($lecturerId);
                }
            }
            if ($oldKoor && $oldKoor->lecturer_id != $request->koordinator_id) {

                $stillCoordinator = CourseCoordinator::where('lecturer_id', $oldKoor->lecturer_id)
                    ->where('id', '!=', $oldKoor->id)
                    ->exists();

                if (!$stillCoordinator) {
                    $oldKoor->lecturer->user->removeRole('koordinator');
                }
            }

            // === REMOVE ROLE jika sekretaris berubah ===
            if ($oldSekre && $oldSekre->lecturer_id != $request->sekretaris_id) {

                $stillCoordinator = CourseCoordinator::where('lecturer_id', $oldSekre->lecturer_id)
                    ->where('id', '!=', $oldSekre->id)
                    ->exists();

                if (!$stillCoordinator) {
                    $oldSekre->lecturer->user->removeRole('koordinator');
                }
            }
            DB::commit();

            return back()->with('success', 'Koordinator dan Sekretaris berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Error in updateKoor: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function create()
    {
        $lecturers = User::role('lecturer')->get();
        return view('pssk.courses.create', compact('lecturers'));
    }

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

    public function show($slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');

        $lecturers = CourseCoordinator::with(['lecturer.user'])
            ->where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->orderBy("role", 'asc')
            ->get();

        $query = CourseStudent::with(['student.user'])
            ->where('course_id', $course->id)
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        if ($sort === 'nim') {
            $query->join('students', 'course_students.student_id', '=', 'students.id')->orderBy('students.nim', $dir)->select('course_students.*');
        } elseif ($sort === 'name') {
            $query->join('students', 'course_students.student_id', '=', 'students.id')->join('users', 'students.user_id', '=', 'users.id')->orderBy('users.name', $dir)->select('course_students.*');
        } elseif ($sort === 'kelompok') {
            $query->orderBy('course_students.kelompok', 'asc');
        }

        $students = $query->paginate(35)->appends(request()->query());

        return view('pssk.courses.show', compact('sort', 'dir', 'course', 'lecturers', 'students', 'semesterId'));
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

        $query = Course::query()->with(['lecturers', 'courseStudents', 'courseLecturer']);

        $query = $this->applyLecturerFilter($query, $semesterId);
        $query = $this->applySemesterFilter($query, $semesterId);
        $query = $this->applyCounts($query, $semesterId);

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
        $semesterId = $request->query('semester_id');
        $course = Course::where('slug', $slug)->firstOrFail();
        $activeTab = $request->query('tab', 'kelas');

        $tabData = [];

        switch ($activeTab) {
            case 'kelas':
                $tabData['kelasData'] = app(CourseScheduleController::class)->getScheduleData($request, $slug);
                break;
            case 'siswa':
                $tabData['studentData'] = app(CourseStudentController::class)->getStudentData($request, $slug);
                break;
            case 'dosen':
                $tabData['lecturerData'] = app(CourseLecturerController::class)->getLecturerData($request, $slug);
                break;
            case 'praktikum':
                $tabData['practicumData'] = app(CoursePracticumController::class)->getPracticumData($request, $slug);
                break;
            case 'pemicu':
                $tabData['pemicuData'] = app(CoursePemicuController::class)->getPemicuData($request, $slug);
                break;
            case 'pleno':
                $tabData['plenoData'] = app(CoursePlenoController::class)->getPlenoData($request, $slug);
                break;
            case 'skilllab':
                $tabData['skillLabData'] = app(CourseSkillsLabController::class)->getSkillsLabData($request, $slug);
                break;
        }

        return view('pssk.courses.edit', array_merge([
            'course' => $course,
            'semesterId' => $semesterId,
            'activeTab' => $activeTab,
            'semester' => Semester::with('academicYear')->findOrFail($semesterId),
        ], $tabData));
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
