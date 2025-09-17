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
        // Ambil query Course dengan relasi lecturers
        $query = Course::with('lecturers');

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
        // Ambil parameter sort & direction
        $sort = $request->get('sort', 'name'); // default sort by name
        $dir  = $request->get('dir', 'asc');   // default asc

        // Validasi kolom yang bisa di-sort
        $allowedSorts = ['name', 'kode_blok'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        // Terapkan sorting
        $query->orderBy($sort, $dir);

        // Pagination 15 per page, tetap simpan query params
        $courses = $query->paginate(15)->appends($request->all());

        return view('admin.courses.index', compact('courses', 'sort', 'dir'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.courses.create');
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
        ]);

        $data = $request->only(['kode_blok', 'name']);

        // Handle upload cover jika ada
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/covers', $filename);
            $data['cover'] = 'covers/' . $filename;
        }

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

        return view('admin.courses.show', compact('course', 'students', 'sort', 'direction'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course)
    {
        $course->load(['lecturers', 'students.student']);
        $lecturers = User::role('lecturer')->get();

        return view('admin.courses.edit', compact('course', 'lecturers'));
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

        return redirect()
            ->route('admin.courses.edit', $course->slug)
            ->with('success', 'Course berhasil diperbarui!');
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

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course berhasil dihapus!');
    }
}
