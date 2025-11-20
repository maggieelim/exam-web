<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
use App\Models\AttendanceSessions;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\Lecturer;
use App\Models\LecturerAttendanceRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function index($slug, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $attendances = AttendanceSessions::with('teachingSchedule')
            ->withCount('studentRecords') // total semua record
            ->withCount([
                'studentRecords as present_count' => function ($query) {
                    $query->whereIn('status', ['present', 'late']);
                }
            ])
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->where('status', 'finished')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('start_time', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('start_time', '<=', $endDate);
            })
            ->orderBy('start_time', 'desc')
            ->paginate(30)
            ->appends($request->query());

        return view('attendance.report.index', compact('attendances', 'course', 'semesterId'));
    }

    public function indexLecturer(Request $request)
    {
        $userId = Auth::id();

        // Dosen login
        $lecturer = Lecturer::with('courseLecturers')
            ->where('user_id', $userId)
            ->firstOrFail();

        // Semua course_lecturer_id milik dosen ini
        $courseLecturerIds = $lecturer->courseLecturers->pluck('id');
        // Filter date
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        // Ambil attendance sessions yang terkait dosen
        $attendances = AttendanceSessions::with(['course', 'activity', 'lecturerRecords'])
            ->withCount('studentRecords')
            ->withCount([
                'studentRecords as present_count' => function ($query) {
                    $query->whereIn('status', ['present', 'late']);
                }
            ])
            ->whereHas('lecturerRecords', function ($q) use ($courseLecturerIds) {
                $q->whereIn('course_lecturer_id', $courseLecturerIds);
            })
            ->where('status', 'finished')
            ->when($startDate, fn($q) => $q->whereDate('start_time', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('start_time', '<=', $endDate))
            ->orderBy('start_time', 'desc')
            ->paginate(30)
            ->appends($request->query());

        return view('attendance.report.indexLecturer', compact('attendances'));
    }

    public function show($slug, $session, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');

        $attendance = AttendanceSessions::with([
            'teachingSchedule',
            'studentRecords.courseStudent.student.user',
            'lecturerRecords'
        ])->findOrFail($session);

        $studentAttendances = $attendance->studentRecords()
            ->with('courseStudent.student.user')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('courseStudent.student', function ($q2) use ($request) {
                    $q2->where('nim', 'like', "%" . $request->search . "%")
                        ->orWhereHas('user', function ($q3) use ($request) {
                            $q3->where('name', 'like', "%" . $request->search . "%");
                        });
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->paginate(35);
        $lecturerAttendances = $attendance->lecturerRecords->where('status', 'checked_in');
        return view('attendance.report.show', compact('attendance', 'course', 'semesterId', 'studentAttendances', 'lecturerAttendances'));
    }

    public function exportAttendanceReport($slug, $session, Request $request)
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        $attendance = AttendanceSessions::with([
            'activity',
            'teachingSchedule',
            'studentRecords.courseStudent.student.user',
            'lecturerRecords.courseLecturer.lecturer.user'
        ])->findOrFail($session);

        $activity = $attendance->activity->activity_name;
        $schedule = $attendance->formatted1_schedule;
        $studentAttendances = $attendance->studentRecords()
            ->with('courseStudent.student.user')
            ->get();

        $lecturerAttendances = $attendance->lecturerRecords()
            ->with('courseLecturer.lecturer.user')
            ->get();

        $fileName = "Rekap_Absen_"
            . $attendance->activity->activity_name
            . "_Blok_" . $course->name
            . "_" . $attendance->teachingSchedule->scheduled_date
            . ".xlsx";

        return Excel::download(
            new AttendanceReportExport(
                $studentAttendances,
                $lecturerAttendances,
                $activity,
                $schedule
            ),
            $fileName
        );
    }
}
