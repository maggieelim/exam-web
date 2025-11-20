<?php

namespace App\Http\Controllers;

use App\Exports\PemicuExport;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\PemicuDetails;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use App\Services\LecturerAttendanceService;
use App\Services\LecturerSortService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CoursePemicuController extends Controller
{
    private $attendanceService;

    public function __construct(LecturerAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    public function getPemicuData(Request $request, string $slug)
    {
        $scheduleService = app(ScheduleConflictService::class);
        $sorter = app(LecturerSortService::class);

        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();

        $kelompok = CourseStudent::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->select('kelompok')
            ->distinct()
            ->orderBy('kelompok') // Changed from sortBy to orderBy for SQL sorting
            ->pluck('kelompok');

        $tutors = TeachingSchedule::whereIn('activity_id', [5])
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereNotNull('scheduled_date')
            ->with('pemicuDetails')
            ->orderBy('session_number')
            ->get()
            ->map(function ($tutor) {
                $tutor->scheduled_date = Carbon::parse($tutor->scheduled_date)->translatedFormat('D d/M');
                $tutor->start_time = Carbon::parse($tutor->start_time)->translatedFormat('H:i');
                $tutor->end_time = Carbon::parse($tutor->end_time)->translatedFormat('H:i');
                return $tutor;
            });

        $lecturers = CourseLecturer::with('activities', 'lecturer.user')
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereHas('activities', function ($query) {
                $query->where('activity_id', 5);
            })
            ->get();

        $lecturers = $sorter->sort($lecturers, $course->id, $semesterId);

        // Get unavailable slots using single query
        $unavailableSlots = [];

        foreach ($lecturers as $lecturer) {
            $lecturerId = $lecturer->lecturer_id;
            $unavailableSlots[$lecturerId] = [];

            foreach ($tutors as $tutor) {
                $hasConflict = $scheduleService->hasScheduleConflict(
                    $lecturerId,
                    $tutor->getRawOriginal('scheduled_date'),
                    $tutor->getRawOriginal('start_time'),
                    $tutor->getRawOriginal('end_time'),
                    null, // excludeScheduleId
                    $semesterId,
                );

                if ($hasConflict) {
                    $unavailableSlots[$lecturerId][] = $tutor->id;
                }
            }
        }

        return (object) [
            'course' => $course,
            'tutors' => $tutors,
            'lecturers' => $lecturers,
            'kelompok' => $kelompok,
            'semesterId' => $semesterId,
            'unavailableSlots' => $unavailableSlots,
        ];
    }

    public function update(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'course_id' => 'required|exists:courses,id',
            'assignments' => 'sometimes|array',
        ]);

        try {
            DB::beginTransaction();

            $semesterId = $request->semester_id;
            $courseId = $request->course_id;
            $assignments = $request->assignments ?? [];

            $pemicuIds = TeachingSchedule::where('course_id', $courseId)->where('semester_id', $semesterId)->where('activity_id', 5)->pluck('id');

            foreach ($assignments as $lecturerId => $tutorAssignments) {
                foreach ($tutorAssignments as $tutorId => $assignmentData) {
                    $kelompok = $assignmentData['kelompok'] ?? null;

                    if (!empty($kelompok)) {
                        PemicuDetails::updateOrCreate(
                            [
                                'teaching_schedule_id' => $tutorId,
                                'lecturer_id' => $lecturerId,
                            ],
                            [
                                'kelompok_num' => $kelompok,
                                'practicum_group_id' => null,
                            ],
                        );
                        $this->attendanceService->syncLecturerAttendance(
                            $tutorId,
                            $lecturerId,
                            $courseId,
                            $semesterId,
                            5
                        );
                    } else {
                        PemicuDetails::where('teaching_schedule_id', $tutorId)->where('lecturer_id', $lecturerId)->delete();
                        $this->attendanceService->removeLecturerAttendance(
                            $tutorId,
                            $lecturerId,
                            $courseId,
                            $semesterId
                        );
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data dosen pemicu berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function downloadExcel($courseSlug, $semesterId)
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $courseId = $course->id;
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $yearName = str_replace('/', '-', $semester->academicYear->year_name);
        $filename = "Jadwal_Pemicu_Blok_{$courseSlug}_{$semester->semester_name}_{$yearName}.xlsx";
        return Excel::download(new PemicuExport($courseId, $semesterId), $filename);
    }

    public function nilai() {}
}
