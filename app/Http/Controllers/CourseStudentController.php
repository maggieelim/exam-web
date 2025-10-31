<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;

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
                    } else {
                        // Jika belum pernah ada, buat baru
                        CourseStudent::create([
                            'course_id' => $course->id,
                            'student_id' => $student->id,
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

            return back()->with($messages);
        }

        // --- Case 2: Import Excel (NIM saja) ---
        if ($request->hasFile('excel')) {
            $collection = Excel::toCollection(null, $request->file('excel'));

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

            return back()->with('success', 'Mahasiswa dari Excel berhasil ditambahkan.');
        }

        return back()->withErrors(['error' => 'Tidak ada data yang dikirim.']);
    }

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
    public function edit(Request $request, string $slug)
    {
        $agent = new Agent();
        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();
        $lecturers = User::role('lecturer')->get();

        $query = CourseStudent::with(['student.user'])->where('course_id', $course->id);

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

        $students = $query->paginate(15)->appends($request->all());
        if ($agent->isMobile()) {
            return view('courses.Student.edit_mobile', compact('course', 'lecturers', 'students', 'sort', 'dir', 'semesterId'));
        }
        return view('courses.tabs._siswa', compact('course', 'lecturers', 'students', 'sort', 'dir', 'semesterId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course, $studentId)
    {
        // pastikan mahasiswa ada di course
        $exists = CourseStudent::where('id', $studentId)->where('course_id', $course->id)->exists();
        if (!$exists) {
            return back()->with('error', 'Mahasiswa tidak ditemukan di course ini.');
        }

        CourseStudent::where('id', $studentId)->where('course_id', $course->id)->delete();
        return back()->with('success', 'Mahasiswa berhasil dihapus dari course.');
    }
}
