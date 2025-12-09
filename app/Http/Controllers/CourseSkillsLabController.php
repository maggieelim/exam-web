<?php

namespace App\Http\Controllers;

use App\Exports\SkillsLabExport;
use App\Models\Course;
use App\Models\Semester;
use App\Models\SkillslabDetails;
use App\Models\TeachingSchedule;
use App\Services\LecturerAttendanceService;
use App\Services\LecturerSortService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CourseSkillsLabController extends Controller
{
    private $attendanceService;

    public function __construct(LecturerAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    public function getSkillsLabData(Request $request, string $slug)
    {
        $scheduleService = app(ScheduleConflictService::class);
        $sorter = app(LecturerSortService::class);

        $semesterId = $request->query('semester_id');
        $course = Course::with([
            'courseLecturer' => function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereHas('activities', fn($q) => $q->where('activity_id', 2))
                    ->with(['lecturer.user', 'activities']);
            }
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $skillsLabs = TeachingSchedule::with('skillslabDetails')
            ->where([['activity_id', 2], ['course_id', $course->id], ['semester_id', $semesterId]])
            ->whereNotNull('scheduled_date')
            ->orderBy('session_number')
            ->get()
            ->map(function ($skillLab) {
                $skillLab->scheduled_date = Carbon::parse($skillLab->scheduled_date)->translatedFormat('D d/M');
                $skillLab->start_time = $skillLab->start_time ? Carbon::parse($skillLab->start_time)->translatedFormat('H:i') : null;
                $skillLab->end_time = $skillLab->end_time ? Carbon::parse($skillLab->end_time)->translatedFormat('H:i') : null;
                return $skillLab;
            });

        $scheduleIds = $skillsLabs->pluck('id');

        $kelompok = SkillslabDetails::whereIn('teaching_schedule_id', $scheduleIds)
            ->select('group_code', 'kelompok_num')
            ->orderBy('group_code')
            ->orderBy('kelompok_num')
            ->get()
            ->groupBy('group_code')
            ->map(fn($items) => $items->pluck('kelompok_num')->unique()->values());

        $lecturers = $sorter->sort($course->courseLecturer, $course->id, $semesterId);

        $unavailableSlots = [];
        foreach ($lecturers as $lecturer) {
            $lecturerId = $lecturer->lecturer_id;
            $unavailableSlots[$lecturerId] = [];

            foreach ($skillsLabs as $skillsLab) {
                $hasConflict = $scheduleService->hasScheduleConflict($lecturerId, $skillsLab->getRawOriginal('scheduled_date'), $skillsLab->getRawOriginal('start_time'), $skillsLab->getRawOriginal('end_time'), null, $semesterId);

                if ($hasConflict) {
                    $unavailableSlots[$lecturerId][] = $skillsLab->id;
                }
            }
        }

        return (object) [
            'course' => $course,
            'skillsLabs' => $skillsLabs,
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

            foreach ($assignments as $lecturerId => $skillLabs) {
                foreach ($skillLabs as $skillLabId => $assignmentData) {

                    $kelompok = $assignmentData['kelompok'] ?? null;

                    // Ambil detail lama (bisa null)
                    $detail = SkillslabDetails::where('teaching_schedule_id', $skillLabId)
                        ->where('lecturer_id', $lecturerId)
                        ->first();

                    // Ambil data dari teaching_schedules
                    $schedule = TeachingSchedule::find($skillLabId);

                    if (!empty($kelompok)) {

                        SkillslabDetails::updateOrCreate(
                            [
                                'teaching_schedule_id' => $skillLabId,
                                'kelompok_num'        => $kelompok,
                                'group_code'          => $detail->group_code ?? $schedule->group,
                            ],
                            [
                                'lecturer_id'        => $lecturerId,
                                'practicum_group_id'  => null,
                                'course_schedule_id'  => $detail->course_schedule_id ?? $schedule->course_schedule_id,
                            ]
                        );

                        $this->attendanceService->syncLecturerAttendance(
                            $skillLabId,
                            $lecturerId,
                            $courseId,
                            $semesterId,
                            2
                        );
                    } else {

                        SkillslabDetails::where('teaching_schedule_id', $skillLabId)
                            ->where('lecturer_id', $lecturerId)
                            ->delete();

                        $this->attendanceService->removeLecturerAttendance(
                            $skillLabId,
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
                'message' => 'Data dosen Skill Lab berhasil diperbarui.',
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
        $filename = "Jadwal_SkillLab_{$course->slug}_{$semester->semester_name}_{$yearName}.xlsx";
        return Excel::download(new SkillsLabExport($course->id, $semesterId), $filename);
    }
}
