<?php

namespace App\Http\Controllers;

use App\Exports\PlenoExport;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\PlenoDetails;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use App\Services\LecturerAttendanceService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CoursePlenoController extends Controller
{
    private $attendanceService;

    public function __construct(LecturerAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function getPlenoData(Request $request, string $slug)
    {
        $scheduleService = app(ScheduleConflictService::class);
        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();

        $plenos = TeachingSchedule::where('activity_id', 4)
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereNotNull('scheduled_date')
            ->with('plenoDetails')
            ->orderBy('session_number')
            ->get()
            ->map(function ($pleno) {
                $pleno->scheduled_date = Carbon::parse($pleno->scheduled_date)->translatedFormat('D d/M');
                $pleno->start_time = Carbon::parse($pleno->start_time)->translatedFormat('H:i');
                $pleno->end_time = Carbon::parse($pleno->end_time)->translatedFormat('H:i');
                return $pleno;
            });

        $lecturers = CourseLecturer::with('activities', 'lecturer.user')
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereHas('activities', function ($query) {
                $query->where('activity_id', 4);
            })
            ->get();

        // Get unavailable slots using single query
        $unavailableSlots = [];

        foreach ($lecturers as $lecturer) {
            $lecturerId = $lecturer->lecturer_id;
            $unavailableSlots[$lecturerId] = [];

            foreach ($plenos as $pleno) {
                $hasConflict = $scheduleService->hasScheduleConflict(
                    $lecturerId,
                    $pleno->getRawOriginal('scheduled_date'),
                    $pleno->getRawOriginal('start_time'),
                    $pleno->getRawOriginal('end_time'),
                    null, // excludeScheduleId
                    $semesterId,
                );

                if ($hasConflict) {
                    $unavailableSlots[$lecturerId][] = $pleno->id;
                }
            }
        }

        return (object) [
            'course' => $course,
            'plenos' => $plenos,
            'lecturers' => $lecturers,
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

            $plenoIds = TeachingSchedule::where('course_id', $courseId)->where('semester_id', $semesterId)->where('activity_id', 4)->pluck('id');

            PlenoDetails::whereIn('teaching_schedule_id', $plenoIds)->delete();

            foreach ($assignments as $lecturerId => $plenoAssignments) {
                foreach ($plenoAssignments as $plenoId => $isAssigned) {
                    if ($isAssigned) {
                        PlenoDetails::updateOrCreate([
                            'teaching_schedule_id' => $plenoId,
                            'lecturer_id' => $lecturerId,
                        ]);
                        $this->attendanceService->syncLecturerAttendance(
                            $plenoId,
                            $lecturerId,
                            $courseId,
                            $semesterId,
                            4
                        );
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data dosen pleno berhasil diperbarui.',
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
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $yearName = str_replace('/', '-', $semester->academicYear->year_name);
        $filename = "Jadwal_Pleno_{$course->slug}_{$semester->semester_name}_{$yearName}.xlsx";
        return Excel::download(new PlenoExport($course->id, $semesterId), $filename);
    }
}
