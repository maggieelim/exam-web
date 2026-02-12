<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AttendanceSessions;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Services\SemesterService;
use Illuminate\Http\Request;

class CourseRecapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Ambil semua dosen PSSK
        $semesters = SemesterService::list();
        $activeSemester = SemesterService::active();
        $semesterId = $request->semester_id ?? $activeSemester->id;
        $semester = Semester::findOrFail($semesterId);
        $lecturers = Lecturer::with('courseLecturers', 'user')
            ->where('type', 'pssk')
            ->join('users', 'users.id', '=', 'lecturers.user_id')
            ->orderBy('users.name', 'asc')
            ->select('lecturers.*')
            ->get();

        $courseLecturerIds = $lecturers
            ->flatMap(fn($lecturer) => $lecturer->courseLecturers)
            ->pluck('id')
            ->unique();

        // 2. Ambil attendance (MODEL UTUH, BUKAN MAP)
        $attendances = AttendanceSessions::with([
            'course',
            'activity',
            'lecturerRecords.courseLecturer'
        ])
            ->where('status', 'finished')
            ->whereHas('course', function ($q) use ($semester) {
                $q->whereIn('semester', [
                    $semester->semester_name,
                    'Ganjil/Genap'
                ]);
            })
            ->whereHas('lecturerRecords', function ($q) use ($courseLecturerIds) {
                $q->whereIn('course_lecturer_id', $courseLecturerIds);
            })
            ->get();

        $activityMap = [
            1 => 'Kuliah',
            4 => 'Pleno',
            5 => 'Pemicu',
            2 => 'KKD',
            8 => 'KKD',
            3 => 'Praktikum',
            7 => 'Praktikum',
        ];

        // 3. Hitung summary: dosen × blok × kegiatan
        $summary = [];
        foreach ($attendances as $attendance) {
            foreach ($attendance->lecturerRecords as $record) {

                // skip activity 6
                if ($attendance->activity_id == 6) {
                    continue;
                }

                // skip activity yang tidak dimapping
                if (!isset($activityMap[$attendance->activity_id])) {
                    continue;
                }

                $lecturerId = $record->courseLecturer->lecturer_id;
                $courseId   = $attendance->course_id;

                $activityKey = $activityMap[$attendance->activity_id];

                $summary[$lecturerId][$courseId][$activityKey] =
                    ($summary[$lecturerId][$courseId][$activityKey] ?? 0) + 1;
            }
        }

        // 4. Ambil master data untuk header tabel
        $courses    = Course::orderBy('sesi')->whereIn('semester', [$semester->semester_name, 'Ganjil/Genap'])->get();
        $activities = collect([
            'Kuliah',
            'Pleno',
            'KKD',
            'Praktikum',
            'Pemicu',
        ]);

        return view('admin.courseRecap.index', compact(
            'lecturers',
            'courses',
            'activities',
            'summary',
            'semesterId',
            'semester',
            'semesters',
            'activeSemester'
        ));
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
    public function edit(string $id)
    {
        //
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
    public function destroy(string $id)
    {
        //
    }
}
