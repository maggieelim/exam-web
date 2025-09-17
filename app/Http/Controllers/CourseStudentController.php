<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
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
        return view('admin.courses.student.index', compact('course'));
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

        // --- Case 1: Input manual banyak NIM (copy dari Excel)
        if ($request->filled('nim')) {
            // Pisahkan berdasarkan newline
            $nims = preg_split('/\r\n|\r|\n/', trim($request->nim));

            foreach ($nims as $nim) {
                $nim = trim($nim);
                if (!$nim) continue;

                $student = Student::where('nim', $nim)->first();
                if ($student) {
                    $course->students()->syncWithoutDetaching([$student->user_id]);
                }
            }

            return back()->with('success', 'Mahasiswa berhasil ditambahkan.');
        }

        // --- Case 2: Import Excel (NIM saja)
        if ($request->hasFile('excel')) {
            $collection = Excel::toCollection(null, $request->file('excel'));

            foreach ($collection[0] as $row) {
                $nim = trim($row[0]);
                if (!$nim) continue;

                $student = Student::where('nim', $nim)->first();
                if ($student) {
                    $course->students()->syncWithoutDetaching([$student->user_id]);
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

        return view('admin.courses.student.edit', compact('course', 'lecturers', 'students', 'sort', 'dir'));
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
    public function destroy(string $courseId, string $studentId)
    {
        $course = Course::findOrFail($courseId);
        $course->students()->detach($studentId);

        return back()->with('success', 'Mahasiswa berhasil dihapus dari course.');
    }
}
