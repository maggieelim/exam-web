<?php

namespace App\Http\Controllers;

use App\Imports\CoursesImport;
use App\Models\Course;
use App\Models\User;
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
        $query = Course::with('lecturers');
        $user = auth()->user();
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        if ($user->hasRole('lecturer')) {
            $query->whereHas('lecturers', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

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

        return view('courses.index', compact('courses', 'sort', 'dir'));
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
    public function show(Course $course, Request $request)
    {
        $sort = $request->get('sort', 'nim'); // default sort berdasarkan nim
        $direction = $request->get('direction', 'asc'); // default ascending

        // Ambil mahasiswa dengan relasi student, lalu urutkan
        $students = $course->students()->with('student')
            ->get()
            ->sortBy(function ($item) use ($sort) {
                return $sort === 'nim' ? $item->student->nim : $item->student->name;
            }, SORT_REGULAR, $direction === 'desc');

        // Simpan direction dan sort agar bisa digunakan di view
        $currentSort = $sort;
        $currentDirection = $direction;

        return view('courses.show', compact('course', 'students', 'sort', 'direction'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $course->load(['lecturers', 'students.student']);
        $lecturers = User::role('lecturer')->get();

        return view('courses.edit', compact('course', 'lecturers'));
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
