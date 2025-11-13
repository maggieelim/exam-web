<?php

namespace App\Http\Controllers;

use App\Exports\SkillsLabExport;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\Semester;
use App\Models\SkillslabDetails;
use App\Models\TeachingSchedule;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CourseSkillsLabController extends Controller
{
    public function getSkillsLabData(Request $request, string $slug)
    {
        $scheduleService = app(ScheduleConflictService::class);
        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
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

        $lecturers = CourseLecturer::with('activities', 'lecturer.user')->where('course_id', $course->id)->where('semester_id', $semesterId)->whereHas('activities', fn($query) => $query->where('activity_id', 2))->get();

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

            $assignments = $request->assignments ?? [];

            foreach ($assignments as $lecturerId => $skillLabs) {
                foreach ($skillLabs as $skillLabId => $assignmentData) {
                    $kelompok = $assignmentData['kelompok'] ?? null;
                    $assigned = $assignmentData['assigned'] ?? 0;

                    if (!empty($kelompok)) {
                        SkillslabDetails::updateOrCreate(
                            [
                                'teaching_schedule_id' => $skillLabId,
                                'lecturer_id' => $lecturerId,
                            ],
                            [
                                'kelompok_num' => $kelompok,
                                'practicum_group_id' => null, // jika memang tidak digunakan
                            ],
                        );
                    } elseif ($assigned) {
                        // Jika sebelumnya assigned tapi sekarang dropdown dikosongkan → hapus
                        SkillslabDetails::where('teaching_schedule_id', $skillLabId)->where('lecturer_id', $lecturerId)->delete();
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
