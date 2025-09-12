<?php

namespace App\Http\Controllers;

use App\Imports\CoursesImport;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $course = Course::with('lecturers')->orderBy('name', 'asc')->get();
        return view('admin.courses.index', compact('course'));
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
    public function show($id)
    {
        $course = Course::with('lecturers', 'students')->findOrFail($id);
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $course = Course::with('lecturers')->findOrFail($id);
        $lecturers = User::role('lecturer')->get();

        return view('admin.courses.edit', compact('course', 'lecturers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'kode_blok' => 'required|string|max:255|unique:courses,kode_blok,' . $course->id,
            'name'      => 'required|string|max:255',
            'cover'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'lecturers' => 'nullable|array',
        ]);

        $data = $request->only(['kode_blok', 'name']);
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

        return redirect()->back()->with('success', 'Course berhasil diperbarui!');
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
