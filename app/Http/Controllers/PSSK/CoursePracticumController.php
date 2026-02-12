<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\PracticumAssignmentExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\PracticumDetails;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use App\Services\LecturerAttendanceService;
use App\Services\LecturerSortService;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CoursePracticumController extends Controller
{
    private $attendanceService;

    public function __construct(LecturerAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function getPracticumData(Request $request, string $slug)
    {
        $scheduleService = app(ScheduleConflictService::class);
        $sorter = app(LecturerSortService::class);

        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();

        $practicums = TeachingSchedule::whereIn('activity_id', [3, 7])
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereNotNull('scheduled_date')
            ->with('practicumDetails')
            ->orderBy('scheduled_date', 'asc')
            ->get()
            ->map(function ($practicum) {
                $practicum->scheduled_date = Carbon::parse($practicum->scheduled_date)->translatedFormat('D d/M');
                $practicum->start_time = Carbon::parse($practicum->start_time)->translatedFormat('H:i');
                $practicum->end_time = Carbon::parse($practicum->end_time)->translatedFormat('H:i');
                return $practicum;
            });

        $lecturers = CourseLecturer::with('activities', 'lecturer.user')
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereHas('activities', function ($query) {
                $query->where('activity_id', 3);
            })
            ->get();

        $lecturers = $sorter->sort($lecturers, $course->id, $semesterId);

        // Get unavailable slots using single query
        $unavailableSlots = [];

        foreach ($lecturers as $lecturer) {
            $lecturerId = $lecturer->lecturer_id;
            $unavailableSlots[$lecturerId] = [];

            foreach ($practicums as $practicum) {
                $hasConflict = $scheduleService->hasScheduleConflict(
                    $lecturerId,
                    $practicum->getRawOriginal('scheduled_date'),
                    $practicum->getRawOriginal('start_time'),
                    $practicum->getRawOriginal('end_time'),
                    null, // excludeScheduleId
                    $semesterId,
                );

                if ($hasConflict) {
                    $unavailableSlots[$lecturerId][] = $practicum->id;
                }
            }
        }

        return (object) [
            'course' => $course,
            'practicums' => $practicums,
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

        DB::beginTransaction();

        try {

            $semesterId = $request->semester_id;
            $courseId = $request->course_id;
            $assignments = $request->assignments ?? [];

            // Ambil semua practicum berdasarkan course & semester
            $practicumIds = TeachingSchedule::where('course_id', $courseId)
                ->where('semester_id', $semesterId)
                ->where('activity_id', 3)
                ->pluck('id');

            foreach ($practicumIds as $practicumId) {

                // Lecturer yang dikirim dari form untuk practicum ini
                $submittedLecturers = [];

                foreach ($assignments as $lecturerId => $practicumAssignments) {
                    if (!empty($practicumAssignments[$practicumId])) {
                        $submittedLecturers[] = $lecturerId;
                    }
                }

                // Lecturer yang ada di database
                $existingLecturers = PracticumDetails::where('teaching_schedule_id', $practicumId)
                    ->pluck('lecturer_id')
                    ->toArray();

                // Cari yang harus dihapus
                $toDelete = array_diff($existingLecturers, $submittedLecturers);

                // Cari yang harus ditambah
                $toAdd = array_diff($submittedLecturers, $existingLecturers);

                // HAPUS
                foreach ($toDelete as $lecturerId) {

                    PracticumDetails::where('teaching_schedule_id', $practicumId)
                        ->where('lecturer_id', $lecturerId)
                        ->delete();

                    $this->attendanceService->removeLecturerAttendance(
                        $practicumId,
                        $lecturerId,
                        $courseId,
                        $semesterId
                    );
                }

                // TAMBAH
                foreach ($toAdd as $lecturerId) {

                    PracticumDetails::create([
                        'teaching_schedule_id' => $practicumId,
                        'lecturer_id' => $lecturerId,
                    ]);

                    $this->attendanceService->syncLecturerAttendance(
                        $practicumId,
                        $lecturerId,
                        $courseId,
                        $semesterId,
                        3
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data dosen praktikum berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadExcel($courseSlug, $semesterId)
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();
        $yearName = str_replace('/', '-', $semester->academicYear->year_name);
        $filename = "Jadwal_Praktikum_{$course->slug}_{$semester->semester_name}_{$yearName}.xlsx";
        return Excel::download(new PracticumAssignmentExport($course->id, $semesterId), $filename);
    }
}
