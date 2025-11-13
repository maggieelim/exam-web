<?php

namespace App\Http\Controllers;

use App\Exports\DaftarSiswaExport;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\CourseStudent;
use App\Models\PracticumGroup;
use App\Models\Semester;
use App\Models\SkillslabDetails;
use App\Models\Student;
use App\Models\TeachingSchedule;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Attributes\Group;

class CourseStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $course = Course::with('lecturers', 'students')->orderBy('name', 'asc')->get();
        return view('students.courses.index', compact('course'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $courseId)
    {
        $course = Course::findOrFail($courseId);
        $now = Carbon::now();
        $semesterId = $request->input('semester_id'); // Ambil semester dari form

        if (!$semesterId) {
            return back()->withErrors(['error' => 'Semester belum dipilih.']);
        }

        $added = [];
        $notFound = [];
        $exists = [];

        // --- Case 1: Input manual NIM ---
        if ($request->filled('nim')) {
            $nims = preg_split('/\r\n|\r|\n/', trim($request->nim));

            foreach ($nims as $nim) {
                $nim = trim($nim);
                if (!$nim) {
                    continue;
                }

                $student = Student::where('nim', $nim)->first();

                if ($student) {
                    // Cek apakah sudah pernah terdaftar (termasuk soft deleted)
                    $existing = CourseStudent::withTrashed()->where('course_id', $course->id)->where('student_id', $student->id)->where('semester_id', $semesterId)->first();

                    if ($existing) {
                        if ($existing->trashed()) {
                            // Jika soft-deleted, restore
                            $existing->restore();
                            $existing->updated_at = $now;
                            $existing->save();
                            $added[] = $nim . ' (dipulihkan)';
                        } else {
                            $exists[] = $nim;
                        }
                    } elseif (!$existing) {
                        // Jika belum pernah ada, buat baru
                        CourseStudent::create([
                            'course_id' => $course->id,
                            'student_id' => $student->id,
                            'kelompok' => null,
                            'user_id' => $student->user_id,
                            'semester_id' => $semesterId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $added[] = $nim;
                    }
                } else {
                    $notFound[] = $nim;
                }
            }

            $messages = [];
            if (!empty($added)) {
                $messages['success'] = 'Mahasiswa berhasil ditambahkan: ' . implode(', ', $added);
            }
            if (!empty($notFound)) {
                $messages['error'] = 'Mahasiswa tidak ditemukan: ' . implode(', ', $notFound);
            }
            if (!empty($exists)) {
                $messages['error_exists'] = 'Mahasiswa sudah terdaftar: ' . implode(', ', $exists);
            }

            return redirect()
                ->to(url()->previous() . '#siswa')
                ->with('success', 'Mahasiswa berhasil ditambahkan.');
        }

        // --- Case 2: Import Excel (NIM saja) ---
        if ($request->hasFile('excel')) {
            $collection = Excel::toCollection(null, $request->file('excel'))->toArray();

            foreach ($collection[0] as $row) {
                $nim = trim($row[0]);
                if (!$nim) {
                    continue;
                }

                $student = Student::where('nim', $nim)->first();
                if ($student) {
                    $exists = CourseStudent::where('course_id', $course->id)->where('student_id', $student->id)->where('semester_id', $semesterId)->exists();

                    if (!$exists) {
                        CourseStudent::create([
                            'course_id' => $course->id,
                            'student_id' => $student->id,
                            'user_id' => $student->user_id,
                            'semester_id' => $semesterId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }

            return redirect()
                ->to(url()->previous() . '#siswa')
                ->with('success', 'Mahasiswa berhasil ditambahkan.');
        }

        return back()->withErrors(['error' => 'Tidak ada data yang dikirim.']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(Request $request, string $slug)
    // {
    //     $agent = new Agent();
    //     $semesterId = $request->query('semester_id');
    //     $course = Course::with(['lecturers'])
    //         ->where('slug', $slug)
    //         ->firstOrFail();
    //     $lecturers = User::role('lecturer')->get();

    //     $query = CourseStudent::with(['student.user'])->where('course_id', $course->id);

    //     if ($semesterId) {
    //         $query->where('semester_id', $semesterId);
    //     }

    //     if ($request->filled('nim')) {
    //         $query->whereHas('student', function ($q) use ($request) {
    //             $q->where('nim', 'like', '%' . $request->nim . '%');
    //         });
    //     }

    //     if ($request->filled('name')) {
    //         $query->whereHas('student.user', function ($q) use ($request) {
    //             $q->where('name', 'like', '%' . $request->name . '%');
    //         });
    //     }

    //     $sort = $request->get('sort', 'name');
    //     $dir = $request->get('dir', 'asc');

    //     if ($sort === 'nim') {
    //         $query->join('students', 'course_students.student_id', '=', 'students.id')->orderBy('students.nim', $dir)->select('course_students.*');
    //     } elseif ($sort === 'name') {
    //         $query->join('students', 'course_students.student_id', '=', 'students.id')->join('users', 'students.user_id', '=', 'users.id')->orderBy('users.name', $dir)->select('course_students.*');
    //     } else {
    //         $query->orderBy('course_students.created_at', 'desc');
    //     }

    //     $students = $query->paginate(15)->appends($request->all());
    //     if ($agent->isMobile()) {
    //         return view('courses.Student.edit_mobile', compact('course', 'lecturers', 'students', 'sort', 'dir', 'semesterId'));
    //     }
    //     return view('courses.tabs._siswa', compact('course', 'lecturers', 'students', 'sort', 'dir', 'semesterId'));
    // }

    public function createGroup(string $slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $teachingSchedule = CourseSchedule::where('course_id', $course->id)->where('semester_id', $semesterId)->first();
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $studentData = app(CourseStudentController::class)->getStudentData($request, $slug);
        $skillslabDetails = SkillslabDetails::where('course_schedule_id', $teachingSchedule->id)->get();
        $practicumDetails = PracticumGroup::with('members')
            ->where('course_schedule_id', $teachingSchedule->id)
            ->get()
            ->flatMap(function ($group) {
                return $group->members->map(function ($member) use ($group) {
                    return [
                        'tipe' => $group->tipe,
                        'group_code' => $group->group_code,
                        'kelompok_num' => $member->kelompok_num,
                    ];
                });
            });
        $jumlahPerKelompok = CourseSchedule::where('course_id', $course->id)->where('semester_id', $semesterId)->value('kelompok');
        return view('courses.siswa.group', compact('course', 'studentData', 'semester', 'semesterId', 'jumlahPerKelompok', 'skillslabDetails', 'practicumDetails'));
    }

    public function getStudentData(Request $request, string $slug)
    {
        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();
        $lecturers = User::role('lecturer')->get();

        $query = CourseStudent::with(['student.user'])
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if ($request->filled('nim')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('nim', 'like', '%' . $request->nim . '%');
            });
        }

        if ($request->filled('name')) {
            $query->whereHas('student.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        if ($sort === 'nim') {
            $query->join('students', 'course_students.student_id', '=', 'students.id')->orderBy('students.nim', $dir)->select('course_students.*');
        } elseif ($sort === 'name') {
            $query->join('students', 'course_students.student_id', '=', 'students.id')->join('users', 'students.user_id', '=', 'users.id')->orderBy('users.name', $dir)->select('course_students.*');
        } else {
            $query->orderBy('course_students.created_at', 'desc');
        }

        $students = $query->get();
        $groupedStudents = $students->groupBy('kelompok')->sortKeys();

        return (object) [
            'course' => $course,
            'lecturers' => $lecturers,
            'students' => $students,
            'groupedStudents' => $groupedStudents,
            'sort' => $sort,
            'dir' => $dir,
            'semesterId' => $semesterId,
        ];
    }

    public function createKelompok(string $slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $studentData = app(CourseStudentController::class)->getStudentData($request, $slug);

        $jumlahPerKelompok = CourseSchedule::where('course_id', $course->id)->where('semester_id', $semesterId)->value('kelompok');
        return view('courses.siswa.kelompok', compact('course', 'studentData', 'semester', 'semesterId', 'jumlahPerKelompok'));
    }

    public function updateKelompok(Request $request, string $slug)
    {
        $request->validate([
            'kelompok' => 'required|integer|min:1|max:20',
        ]);

        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->input('semester_id');
        $jumlahSiswaPerKelompok = $request->input('kelompok');

        $students = CourseStudent::with(['student.user'])
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada mahasiswa yang terdaftar di blok ini.');
        }

        $totalSiswa = $students->count();

        // Hitung jumlah kelompok berdasarkan siswa per kelompok
        $jumlahKelompok = round($totalSiswa / $jumlahSiswaPerKelompok);
        // Validasi jumlah kelompok maksimal
        if ($jumlahKelompok > 35) {
            return redirect()->back()->with('error', 'Dengan jumlah siswa per kelompok tersebut, akan terbentuk terlalu banyak kelompok (maksimal 35 kelompok).');
        }

        // Update atau create course schedule
        $courseSchedule = CourseSchedule::updateOrCreate(
            [
                'course_id' => $course->id,
                'semester_id' => $semesterId,
            ],
            [
                'kelompok' => $jumlahSiswaPerKelompok,
                'year_level' => $course->year_level ?? 1,
                'created_by' => auth()->id(),
            ],
        );

        // Reset semua kelompok sebelum distribusi baru
        CourseStudent::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->update(['kelompok' => null]);

        // Kelompokkan berdasarkan gender dan acak
        $groupedByGender = $students
            ->groupBy(function ($cs) {
                $gender = $cs->student->user->gender ?? 'tidak diketahui';
                return match (strtolower($gender)) {
                    'l', 'laki-laki', 'pria', 'male' => 'laki-laki',
                    'p', 'perempuan', 'wanita', 'female' => 'perempuan',
                    default => 'tidak diketahui',
                };
            })
            ->map(fn($group) => $group->shuffle());

        // Siapkan wadah kelompok
        $kelompok = [];
        for ($i = 1; $i <= $jumlahKelompok; $i++) {
            $kelompok[$i] = [];
        }

        // FASE 1: Distribusikan siswa secara round-robin per gender sampai semua kelompok penuh
        foreach ($groupedByGender as $gender => $studentsByGender) {
            $groupIndex = 1;
            foreach ($studentsByGender as $student) {
                // Cari kelompok yang masih bisa menerima anggota baru
                $found = false;
                $attempts = 0;

                while (!$found && $attempts < $jumlahKelompok) {
                    if (count($kelompok[$groupIndex]) < $jumlahSiswaPerKelompok) {
                        $kelompok[$groupIndex][] = $student->id;
                        $found = true;
                    }
                    $groupIndex = ($groupIndex % $jumlahKelompok) + 1;
                    $attempts++;
                }

                // Jika tidak menemukan kelompok yang sesuai, masukkan ke kelompok dengan anggota paling sedikit
                if (!$found) {
                    $minGroup = $this->findGroupWithMinStudents($kelompok);
                    $kelompok[$minGroup][] = $student->id;
                }
            }
        }

        // FASE 2: Handle sisa siswa yang belum terdistribusi
        $allDistributedStudents = collect($kelompok)->flatten();
        $remainingStudents = $students->pluck('id')->diff($allDistributedStudents);

        if ($remainingStudents->isNotEmpty()) {
            // Distribusikan sisa siswa ke kelompok dengan anggota paling sedikit
            foreach ($remainingStudents as $studentId) {
                $minGroup = $this->findGroupWithMinStudents($kelompok);
                $kelompok[$minGroup][] = $studentId;
            }
        }

        // Update kelompok ke DB
        DB::transaction(function () use ($kelompok, $course, $semesterId) {
            foreach ($kelompok as $kelompokNumber => $studentIds) {
                if (!empty($studentIds)) {
                    CourseStudent::where('course_id', $course->id)
                        ->where('semester_id', $semesterId)
                        ->whereIn('id', $studentIds)
                        ->update(['kelompok' => $kelompokNumber]);
                }
            }
        });

        $skillsLabGroup = app(GroupController::class);
        return $skillsLabGroup->updateGroup($request, $slug);

        $skillsLabGroup->updateGroup($request, $slug);
        return redirect()
            ->back()
            ->with('success', "Berhasil membentuk $jumlahKelompok kelompok dengan $jumlahSiswaPerKelompok siswa per kelompok.");
    }

    public function updateKelompokManual(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->input('semester_id');

        // cek apakah ada data kelompok
        if (!$request->has('kelompok') || empty($request->kelompok)) {
            return redirect()
                ->to(url()->previous() . '#siswa')
                ->with('error', 'Tidak ada perubahan data kelompok.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->kelompok as $studentId => $kelompokNum) {
                $kelompokNum = $kelompokNum !== '' ? intval($kelompokNum) : null;

                CourseStudent::where('id', $studentId)
                    ->where('course_id', $course->id)
                    ->where('semester_id', $semesterId)
                    ->update(['kelompok' => $kelompokNum]);
            }

            DB::commit();

            return redirect()
                ->to(url()->previous() . '#siswa')
                ->with('success', 'Data kelompok mahasiswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->to(url()->previous() . '#siswa')
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    // Helper function untuk mencari kelompok dengan siswa paling sedikit
    private function findGroupWithMinStudents($kelompok)
    {
        $minCount = PHP_INT_MAX;
        $minGroup = 1;

        foreach ($kelompok as $groupNumber => $students) {
            if (count($students) < $minCount) {
                $minCount = count($students);
                $minGroup = $groupNumber;
            }
        }

        return $minGroup;
    }

    public function destroy(Course $course, $studentId)
    {
        // pastikan mahasiswa ada di course
        $exists = CourseStudent::where('id', $studentId)->where('course_id', $course->id)->exists();
        if (!$exists) {
            return back()->with('error', 'Mahasiswa tidak ditemukan di course ini.');
        }

        CourseStudent::where('id', $studentId)->where('course_id', $course->id)->delete();
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Mahasiswa berhasil dihapus.']);
        }
        return back()->with('success', 'Mahasiswa berhasil dihapus dari course.');
    }

    public function downloadExcel($courseSlug, $semesterId)
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $courseId = $course->id;
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $yearName = str_replace('/', '-', $semester->academicYear->year_name);
        $filename = "DaftarSiswa_Blok_{$courseSlug}_{$semester->semester_name}_{$yearName}.xlsx";
        return Excel::download(new DaftarSiswaExport($courseId, $semesterId), $filename);
    }
}
