<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\LecturerTutorGrading;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecords;
use App\Models\AttendanceSessions;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Lecturer;
use App\Models\LecturerAttendanceRecords;
use App\Models\PemicuDetails;
use App\Models\PemicuScore;
use App\Models\Semester;
use App\Services\SemesterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TutorGradingController extends Controller
{
    public function index(Request $request)
    {
        $activeSemester = SemesterService::active();
        $semesterId = $request->query('semester_id', $activeSemester->id);
        $courseId   = $request->query('course_id');

        $user = Auth::id();
        $semesters = SemesterService::list();
        $lecturer  = Lecturer::where('user_id', $user)->firstOrFail();

        $details = PemicuDetails::with('teachingSchedule.course')
            ->where('lecturer_id', $lecturer->id)
            ->wherenotnull('kelompok_num')
            ->whereHas('teachingSchedule', function ($q) use ($semesterId, $courseId) {
                $q->whereDate('scheduled_date', '<=', today())
                    ->where('semester_id', $semesterId)
                    ->when($courseId, fn($q) => $q->where('course_id', $courseId));
            })
            ->orderBy('teaching_schedule_id', 'desc')
            ->get();

        $courses = $details
            ->pluck('teachingSchedule.course')
            ->filter()
            ->unique('id')
            ->values();

        $grouped = $details->groupBy(function ($d) {
            $pemicuKe = (string) $d->teachingSchedule->pemicu_ke;

            return implode('-', [
                $d->teachingSchedule->course_id,
                $d->kelompok_num,
                substr($pemicuKe, 0, 1),
            ]);
        });


        $tutors = $grouped->map(function ($group) use ($lecturer) {

            $first     = $group->first();
            $schedule  = $first->teachingSchedule;
            $courseId  = $schedule->course_id;
            $kelompok  = $first->kelompok_num;

            $pemicuKeList = $group->pluck('teachingSchedule.pemicu_ke')
                ->unique()
                ->sort()
                ->values();

            $pemicu = intval(substr($pemicuKeList->first(), 0, 1));

            $studentCount = CourseStudent::where('course_id', $courseId)
                ->where('kelompok', $kelompok)
                ->where('semester_id', $schedule->semester_id)
                ->count();

            $students = CourseStudent::where('course_id', $courseId)
                ->where('semester_id', $schedule->semester_id)
                ->where('kelompok', $kelompok)
                ->get();

            $group->pluck('teachingSchedule')->each(function ($schedule) use ($students, $lecturer) {
                foreach ($students as $student) {

                    $detail = PemicuDetails::where('lecturer_id', $lecturer->id)
                        ->where('kelompok_num', $student->kelompok)
                        ->where('teaching_schedule_id', $schedule->id)
                        ->first();

                    PemicuScore::firstOrCreate(
                        [
                            'course_student_id'      => $student->id,
                            'pemicu_detail_id'       => $detail->id,
                            'teaching_schedule_id'   => $schedule->id,
                        ],
                        [
                            'disiplin'          => 0,
                            'keaktifan'         => 0,
                            'berpikir_kritis'   => 0,
                            'info_baru'         => 0,
                            'analisis_rumusan'  => 0,
                            'total_score'       => 0,
                        ]
                    );
                }
            });

            return [
                'course'            => $schedule->course,
                'kelompok'          => $kelompok,
                'pemicu_ke'         => $pemicuKeList,
                'pemicu'            => $pemicu,
                'student_count'     => $studentCount,
                'pemicu_detail_ids' => $group->pluck('id')->values(),
            ];
        })->values();
        return view('pssk.pemicu.index', compact('tutors', 'semesterId', 'semesters', 'activeSemester', 'courses'));
    }

    public function show($courseId, $kelompok, Request $request)
    {
        $search = $request->query('search');
        $pemicusJson = $request->get('pemicu', '[]');
        $pemicu = json_decode($pemicusJson, true);

        $course = Course::findOrFail($courseId);
        $kel = $kelompok;

        $students = PemicuScore::with(['courseStudent.student.user', 'pemicuDetail'])
            ->whereIn('pemicu_detail_id', $pemicu)
            ->whereHas('courseStudent', function ($q) use ($courseId, $kelompok) {
                $q->where('course_id', $courseId)
                    ->where('kelompok', $kelompok);
            })
            ->when($search, function ($q) use ($search) {
                $q->whereHas('courseStudent.student', function ($q2) use ($search) {
                    $q2->where('nim', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q3) use ($search) {
                            $q3->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->get()
            ->groupBy('course_student_id');

        $teachingId = PemicuDetails::whereIn('id', $pemicu)
            ->get(['teaching_schedule_id']);

        $attendanceSessions = AttendanceSessions::where('course_id', $courseId)->whereIn('teaching_schedule_id', collect($teachingId)->pluck('teaching_schedule_id'))
            ->get()
            ->keyBy('teaching_schedule_id');

        $existingAttendance = AttendanceRecords::whereIn('attendance_session_id', $attendanceSessions->pluck('id'))
            ->get()
            ->groupBy('course_student_id');

        return view('pssk.pemicu.show', compact('kel', 'students', 'course', 'pemicu', 'pemicusJson', 'attendanceSessions', 'existingAttendance'));
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'attendance_session_id' => 'required|integer',
            'course_student_id' => 'required|integer',
            'checked' => 'required|boolean',
        ]);

        $user = Auth::id();
        $lecturer  = Lecturer::where('user_id', $user)->firstOrFail();
        $courseId = CourseStudent::where('id', $request->course_student_id)->firstOrFail()->course_id;
        $courseLecturer = $lecturer->courseLecturers()
            ->where('course_id', $courseId)
            ->firstOrFail();

        if (!$request->checked) {
            AttendanceRecords::where('attendance_session_id', $request->attendance_session_id)
                ->where('course_student_id', $request->course_student_id)
                ->delete();

            return response()->json(['status' => 'deleted']);
        }

        LecturerAttendanceRecords::updateOrCreate([
            'attendance_session_id' => $request->attendance_session_id,
            'course_lecturer_id' => $courseLecturer->id,
        ], [
            'status' => 'checked_in',
            'checked_in_at' => Carbon::now(),
        ]);
        AttendanceRecords::firstOrCreate(
            [
                'attendance_session_id' => $request->attendance_session_id,
                'course_student_id' => $request->course_student_id,
            ],
            [
                'nim' => $request->nim ?? '',
                'latitude' => '-6.16925120',
                'longitude' => '106.79052950',
                'loc_name' => 'Universitas Tarumanagara Kampus 1',
                'distance' => 0,
                'wifi_ssid' => 'unknown',
                'device_info' => request()->userAgent(),
                'scanned_at' => Carbon::now(),
                'method' => 'manual',
                'status' => 'present',
            ]
        );

        return response()->json(['status' => 'created']);
    }

    public function edit($pemicu, $studentId, Request $request)
    {
        $pemicusJson = $request->get('pemicus', '[]');
        $pemicus = json_decode($pemicusJson, true);
        $score = PemicuScore::with('pemicuDetail', 'courseStudent.course', 'courseStudent.student.user', 'teachingSchedule')->where('pemicu_detail_id', $pemicu)->where('course_student_id', $studentId)->firstOrFail();
        $student = $score->courseStudent->student;
        $teachingSchedule = $score->teachingSchedule;
        return view('pssk.pemicu.edit', compact('score', 'pemicus', 'student', 'teachingSchedule'));
    }

    public function update(Request $request, $pemicu, $studentId)
    {
        $pemicusJson = $request->get('pemicus', '[]');
        $pemicus = json_decode($pemicusJson, true);
        $request->validate([
            'disiplin' => 'required|integer|min:0|max:3',
            'keaktifan' => 'required|integer|min:0|max:3',
            'berpikir_kritis' => 'required|integer|min:0|max:3',
            'info_baru' => 'nullable|integer|max:3',
            'analisis_rumusan' => 'nullable|integer|max:3',
        ]);

        $courseStudent = CourseStudent::with('course')->findOrFail($studentId);
        $courseId = $courseStudent->course_id;

        $detail = PemicuDetails::where('id', $pemicu)->firstOrFail();

        PemicuScore::updateOrCreate(
            [
                'course_student_id' => $studentId,
                'teaching_schedule_id' => $detail->teaching_schedule_id ?? null,
                'pemicu_detail_id' => $pemicu ?? null
            ],
            [
                'disiplin' => $request->disiplin,
                'keaktifan' => $request->keaktifan,
                'berpikir_kritis' => $request->berpikir_kritis,
                'info_baru' => $request->info_baru ?? null,
                'analisis_rumusan' => $request->analisis_rumusan ?? null,
                'total_score' => $request->disiplin + $request->keaktifan + $request->berpikir_kritis
                    + ($request->info_baru ?? 0) + ($request->analisis_rumusan ?? 0)
            ]
        );

        return redirect()->route('tutors.detail', [
            'course' => $courseId,
            'kelompok' => $detail->kelompok_num,
            'pemicu' => json_encode($pemicus)
        ])
            ->with('success', 'Nilai berhasil disimpan.');
    }

    public function downloadExcel($courseId, $kelompok, Request $request)
    {
        $pemicusJson = $request->get('pemicus', '[]');
        $pemicu = json_decode($pemicusJson, true); // hasil array
        $course = Course::where('id', $courseId)->firstOrFail();
        $kel = $kelompok;

        $students = PemicuScore::with(['courseStudent.student.user', 'pemicuDetail', 'teachingSchedule'])
            ->whereIn('pemicu_detail_id', $pemicu)
            ->get()
            ->groupBy('course_student_id');
        $filename = "Nilai_Tutor_Kelompok_{$kel}_Blok_{$course->slug}.xlsx";

        return Excel::download(new LecturerTutorGrading($course, $students, $kel), $filename);
    }
}
