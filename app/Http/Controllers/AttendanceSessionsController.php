<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AttendanceSessions;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Str;

class AttendanceSessionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function getActiveSemester()
    {
        $today = Carbon::today();
        return Semester::where('start_date', '<=', $today)->where('end_date', '>=', $today)->first();
    }

    public function index(Request $request)
    {
        $semesterId = $request->get('semester_id');

        if (!$semesterId) {
            $activeSemester = $this->getActiveSemester();
            $semesterId = $activeSemester ? $activeSemester->id : null;
        }

        $activeSemester = $this->getActiveSemester();
        $semesters = Semester::with('academicYear')->get();
        $query = AttendanceSessions::query()->with(['course', 'records']);

        $sort = $request->get('sort', 'start_time');
        $dir = $request->get('dir', 'asc');
        $allowedSorts = ['kode_blok', 'start_time'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'start_time';
        }

        $query->orderBy($sort, $dir);
        $attendances = $query->paginate(15)->appends($request->all());
        return view('attendance.index', compact('activeSemester', 'semesterId', 'semesters', 'sort', 'dir', 'attendances'));
    }

    public function create(Request $request)
    {
        $semester = $this->getActiveSemester();
        $semesterId = $request->get('semester_id', $semester?->id);
        $user = auth()->user();

        $lecturer = Lecturer::where('user_id', $user->id)->first();
        $semester = Semester::where('id', $semesterId)->firstOrFail();
        $lecturers = Lecturer::with('courseLecturers')->get();
        $activity = Activity::all();

        if ($user->hasRole('lecturer')) {
            $courses = Course::whereHas('courseLecturer', function ($query) use ($lecturer, $semesterId) {
                $query->where('lecturer_id', $lecturer->id)->where('semester_id', $semesterId);
            })
                ->with([
                    'courseLecturer' => function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    },
                ])
                ->get();
        } elseif ($user->hasRole('admin')) {
            $courses = Course::whereHas('courseLecturer', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
                ->with([
                    'courseLecturer' => function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    },
                ])
                ->get();
        }

        return view('attendance.create', compact('lecturer', 'courses', 'lecturers', 'activity'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'absensi_code' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'location_lat' => 'decimal:10,8',
            'location_long' => 'decimal:10,8',
            'tolerance_meter' => 'required|numeric',
        ]);
        AttendanceSessions::create([
            'course_lecturer_id' => $request->courseLecturer,
            'activity_type' => $request->activity,
            'absensi_code' => 'ABS-' . strtoupper(Str::random(8)),
            'start_time' => $request->startTime,
            'end_time' => $request->endTime,
            'location_lat' => $request->lat,
            'location_long' => $request->long,
            'tolerance_meter' => $request->tolerance,
        ]);

        return redirect()->back()->with('success', 'Absen Berhasil Dibuat');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
