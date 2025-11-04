<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseLecturerActivity;
use App\Models\Lecturer;
use App\Models\Semester;
use DB;
use Illuminate\Http\Request;

class CourseLecturerController extends Controller
{
    public function getLecturerData(Request $request, string $slug)
    {
        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();

        $query = CourseLecturer::with('lecturer.user')->where('course_id', $course->id)->where('semester_id', $semesterId);
        if ($request->filled('bagian')) {
            $query->whereHas('lecturer', function ($q) use ($request) {
                $q->where('bagian', 'like', '%' . $request->bagian . '%');
            });
        }

        if ($request->filled('name')) {
            $query->whereHas('lecturer.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        if ($sort === 'bagian') {
            $query->join('lecturers', 'course_lecturer.lecturer_id', '=', 'lecturers.id')->orderBy('lecturers.bagian', $dir)->select('course_lecturer.*');
        } elseif ($sort === 'name') {
            $query->join('lecturers', 'course_lecturer.lecturer_id', '=', 'lecturers.id')->join('users', 'lecturers.user_id', '=', 'users.id')->orderBy('users.name', $dir)->select('course_lecturer.*');
        } else {
            $query->orderBy('course_lecturer.created_at', 'desc');
        }

        $lecturers = $query->get();

        return (object) [
            'course' => $course,
            'lecturers' => $lecturers,
            'sort' => $sort,
            'dir' => $dir,
            'semesterId' => $semesterId,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
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
    public function edit(string $slug, Request $request)
    {
        $activity = Activity::where('category', 'teaching')->get();
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $query = Lecturer::with('user');
        if ($request->filled('bagian')) {
            $query->where('bagian', 'like', '%' . $request->bagian . '%');
        }

        if ($request->filled('name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        $lecturers = $query->get();
        return view('courses.dosen.add_lecturer', compact('course', 'lecturers', 'semester', 'semesterId', 'activity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        DB::transaction(function () use ($request, $course) {
            foreach ($request->input('lecturers', []) as $lecturerId => $activities) {
                // Hapus aktivitas lama
                CourseLecturerActivity::where('course_lecturer_id', $lecturerId)->delete();

                // Simpan aktivitas baru berdasarkan checkbox yang dicentang
                foreach ($activities as $activityName => $isChecked) {
                    if ($isChecked) {
                        $activity = Activity::where('activity_name', ucfirst($activityName))->first();
                        if ($activity) {
                            CourseLecturerActivity::create([
                                'course_lecturer_id' => $lecturerId,
                                'activity_id' => $activity->id,
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()
            ->to(url()->previous() . '#dosen')
            ->with('success', 'Data dosen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
