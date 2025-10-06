<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        // --- Case 1: Input manual banyak NIM (copy dari Excel)
        // --- Case 1: Input manual banyak NIM (copy dari Excel)
        if ($request->filled('nim')) {
            $nims = preg_split('/\r\n|\r|\n/', trim($request->nim));

            $notFound = []; // simpan NIM yang tidak ada

            foreach ($nims as $nim) {
                $nim = trim($nim);
                if (!$nim) continue;

                $student = Student::where('nim', $nim)->first();
                if ($student) {
                    // Hanya attach jika belum ada
                    if (!$course->students()->where('user_id', $student->user_id)->exists()) {
                        $course->students()->attach($student->user_id, [
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
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
                $messages['error'] = 'Mahasiswa tidak ada: ' . implode(', ', $notFound);
            }

            return back()->with($messages);
        }


        // --- Case 2: Import Excel (NIM saja)
        if ($request->hasFile('excel')) {
            $collection = Excel::toCollection(null, $request->file('excel'));

            foreach ($collection[0] as $row) {
                $nim = trim($row[0]);
                if (!$nim) continue;

                $student = Student::where('nim', $nim)->first();
                if ($student) {
                    if (!$course->students()->where('user_id', $student->user_id)->exists()) {
                        $course->students()->attach($student->user_id, [
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }

            return back()->with('success', 'Mahasiswa dari Excel berhasil ditambahkan.');
        }

        return back()->withErrors(['error' => 'Tidak ada data yang dikirim']);
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
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();

        $lecturers = User::role('lecturer')->get();

        // Query mahasiswa yang terdaftar di course ini
        $query = $course->students()->with('student'); // relasi user->student

        // FILTER
        if ($request->filled('nim')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('nim', 'like', '%' . $request->nim . '%');
            });
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // SORTING
        $sort = $request->get('sort', 'name'); // default sort by name
        $dir  = $request->get('dir', 'asc');   // default ascending

        if ($sort === 'nim') {
            $query->join('students', 'users.id', '=', 'students.user_id')
                ->orderBy('students.nim', $dir)
                ->select('users.*'); // pastikan users.* di-select supaya model tetap User
        } else {
            $query->orderBy($sort, $dir);
        }

        // PAGINATION
        $students = $query->paginate(15)->appends($request->all());

        return view('courses.student.edit', compact('course', 'lecturers', 'students', 'sort', 'dir'));
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
        $exists = $course->students()->where('user_id', $studentId)->exists();

        if (!$exists) {
            return back()->with('error', 'Mahasiswa tidak ditemukan di course ini.');
        }

        $course->students()->detach($studentId); // hapus relasi
        return back()->with('success', 'Mahasiswa berhasil dihapus dari course.');
    }
}
