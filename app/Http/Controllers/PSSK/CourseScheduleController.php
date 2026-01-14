<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\KelasExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Semester;
use App\Models\CourseSchedule;
use App\Models\TeachingSchedule;
use App\Models\Activity;
use App\Rules\ValidZone;
use App\Services\ScheduleDataService;
use App\Services\ScheduleUpdateService;
use App\Services\ScheduleCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CourseScheduleController extends Controller
{
    private $scheduleDataService;
    private $scheduleUpdateService;
    private $scheduleCreationService;

    public function __construct(
        ScheduleDataService $scheduleDataService,
        ScheduleUpdateService $scheduleUpdateService,
        ScheduleCreationService $scheduleCreationService
    ) {
        $this->scheduleDataService = $scheduleDataService;
        $this->scheduleUpdateService = $scheduleUpdateService;
        $this->scheduleCreationService = $scheduleCreationService;
    }

    public function getScheduleData(Request $request, string $slug)
    {
        $semesterId = $request->query('semester_id');
        return $this->scheduleDataService->getScheduleData($slug, $semesterId);
    }

    public function create(Request $request, $courseSlug)
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->findOrFail($semesterId);
        $activities = Activity::orderBy('activity_name')->get();

        $existingSchedule = CourseSchedule::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->with('details.activity')
            ->first();

        $existingCounts = $this->getExistingCounts($existingSchedule);

        return view('pssk.courses.kelas.create', compact(
            'activities',
            'course',
            'semester',
            'semesterId',
            'existingCounts',
            'existingSchedule'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'activities' => 'required|array',
            'activities.*' => 'integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $result = $this->scheduleCreationService->createOrUpdateSchedule($request->all());

            DB::commit();

            return redirect()
                ->route('courses.edit', [
                    'course' => $result['course_slug'],
                    'semester_id' => $request->semester_id
                ])
                ->with('success', 'Jadwal kelas berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Gagal menyimpan jadwal: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function updateSchedules(Request $request, $courseScheduleId)
    {
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|exists:teaching_schedules,id',
            'schedules.*.scheduled_date' => 'nullable|date',
            'schedules.*.room' => 'nullable|string|max:50',
            'schedules.*.zone' => ['nullable', 'string', 'max:20', new ValidZone()],
            'schedules.*.group' => 'nullable|string|max:20',
            'schedules.*.topic' => 'nullable|string|max:255',
            'schedules.*.lecturer_id' => 'nullable|exists:lecturers,id',
            'schedules.*.exam_type' => 'nullable|string|in:UTS,UAS,QUIZ,TUGAS',
            'schedules.*.supervisor_id' => 'nullable|exists:lecturers,id',
            'schedules.*.notes' => 'nullable|string|max:500',
            'schedules.*.location' => 'nullable|string|max:100',
            'schedules.*.instructor_id' => 'nullable|exists:lecturers,id',
            'schedules.*.equipment' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $result = $this->scheduleUpdateService->updateSchedules(
                $request->schedules,
                $courseScheduleId
            );

            DB::commit();

            // Pastikan response konsisten
            $response = [
                'success' => true,
                'message' => count($result['failedSchedules'])
                    ? 'Sebagian jadwal berhasil disimpan. Ada ' . count($result['failedSchedules']) . ' jadwal bentrok.'
                    : 'Semua jadwal berhasil diperbarui.',
                'updated_schedules' => $result['updatedSchedules'],
                'failed_schedules' => $result['failedSchedules'],
            ];

            if ($request->ajax()) {
                return response()->json($response);
            }

            return redirect()->back()->with('success', $response['message']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating schedules', ['error' => $e->getMessage()]);

            $errorResponse = [
                'success' => false,
                'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage(),
            ];

            if ($request->ajax()) {
                return response()->json($errorResponse, 500);
            }

            return redirect()->back()->withErrors(['error' => $errorResponse['message']]);
        }
    }

    public function destroy(string $id)
    {
        $schedule = TeachingSchedule::findOrFail($id);
        $schedule->clearSchedule();

        return response()->json([
            'success' => true,
            'message' => 'Schedule berhasil dikosongkan.',
        ]);
    }

    public function downloadExcel($courseSlug, $semesterId)
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $semester = Semester::with('academicYear')->where('id', $semesterId)->first();

        $yearName = str_replace('/', '-', $semester->academicYear->year_name);
        $filename = "Jadwal_Perkuliahan_{$course->slug}_{$semester->semester_name}_{$yearName}.xlsx";

        return Excel::download(new KelasExport($course->id, $semesterId), $filename);
    }

    private function getExistingCounts($existingSchedule)
    {
        if (!$existingSchedule) {
            return [];
        }

        $counts = [];
        foreach ($existingSchedule->details as $detail) {
            $counts[$detail->activity_id] = $detail->total_sessions;
        }

        return $counts;
    }
}
