<?php

namespace App\Http\Controllers;

use App\Helpers\ZoneTimeHelper;
use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseLecturerActivity;
use App\Models\CourseSchedule;
use App\Models\CourseScheduleDetail;
use App\Models\Exam;
use App\Models\Semester;
use App\Models\SkillslabDetails;
use App\Models\TeachingSchedule;
use App\Rules\ValidZone;
use DB;
use Illuminate\Http\Request;
use Str;

class CourseScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $courseSlug)
    {
        $course = Course::where('slug', $courseSlug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->findOrFail($semesterId);
        $activities = Activity::orderBy('activity_name')->get();

        $existingSchedule = CourseSchedule::where('course_id', $course->id)->where('semester_id', $semesterId)->with('details.activity')->first();

        $existingCounts = [];
        if ($existingSchedule) {
            foreach ($existingSchedule->details as $detail) {
                $existingCounts[$detail->activity_id] = $detail->total_sessions;
            }
        }
        return view('courses.kelas.create', compact('activities', 'course', 'semester', 'semesterId', 'existingCounts', 'existingSchedule'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'activities' => 'required|array',
            'activities.*' => 'integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $courseSchedule = CourseSchedule::firstOrCreate(
                [
                    'course_id' => $request->course_id,
                    'semester_id' => $request->semester_id,
                ],
                [
                    'year_level' => $request->year_level,
                    'created_by' => auth()->id(),
                ],
            );

            foreach ($request->activities as $activityId => $newCount) {
                if ($newCount <= 0) {
                    continue;
                }

                $activity = Activity::find($activityId);
                if (!$activity) {
                    continue;
                }

                // Ambil atau buat detail
                $detail = CourseScheduleDetail::firstOrNew([
                    'course_schedule_id' => $courseSchedule->id,
                    'activity_id' => $activityId,
                ]);

                $oldCount = $detail->exists ? $detail->total_sessions : 0;
                $detail->total_sessions = $newCount;
                $detail->save();

                // Jika kegiatan adalah PEMICU → dikali 2
                $multiplier = 1;
                if (Str::contains(strtolower($activity->activity_name), 'pemicu')) {
                    $multiplier = 2;
                }

                // Jumlah total sesi aktual
                $actualNewCount = $newCount * $multiplier;
                $actualOldCount = $oldCount * $multiplier;

                // ✅ Tambah sesi baru
                if ($actualNewCount > $actualOldCount) {
                    for ($i = $actualOldCount + 1; $i <= $actualNewCount; $i++) {
                        if (strtolower($activity->code) === 'u') {
                            // UJIAN
                            $exam = Exam::create([
                                'course_id' => $courseSchedule->course_id,
                                'semester_id' => $courseSchedule->semester_id,
                                'title' => "UT {$i}",
                                'created_by' => auth()->id(),
                            ]);

                            // Buat juga di TeachingSchedule
                            TeachingSchedule::create([
                                'course_schedule_id' => $courseSchedule->id,
                                'course_id' => $courseSchedule->course_id,
                                'semester_id' => $courseSchedule->semester_id,
                                'activity_id' => $activityId,
                                'session_number' => $i,
                                'created_by' => auth()->id(),
                            ]);
                        } else {
                            // KEGIATAN BIASA (termasuk PEMICU)
                            TeachingSchedule::create([
                                'course_schedule_id' => $courseSchedule->id,
                                'course_id' => $courseSchedule->course_id,
                                'semester_id' => $courseSchedule->semester_id,
                                'activity_id' => $activityId,
                                'session_number' => $i,
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }
                }

                // ✅ Kurangi jika jumlah dikurangi
                elseif ($actualNewCount < $actualOldCount) {
                    TeachingSchedule::where('course_schedule_id', $courseSchedule->id)->where('activity_id', $activityId)->where('session_number', '>', $actualNewCount)->delete();

                    if (strtolower($activity->code) === 'u') {
                        Exam::where('course_id', $courseSchedule->course_id)
                            ->where('semester_id', $courseSchedule->semester_id)
                            ->where('title', 'like', 'UT%')
                            ->whereRaw('CAST(SUBSTRING(title, 4) AS UNSIGNED) > ?', [$actualNewCount])
                            ->delete();
                    }
                }
            }

            DB::commit();

            $courseSlug = $courseSchedule->course->slug;
            return redirect()
                ->route('courses.edit', ['course' => $courseSlug, 'semester_id' => $request->semester_id])
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

        DB::beginTransaction();
        try {
            $updatedSchedules = [];

            foreach ($request->schedules as $scheduleData) {
                $schedule = TeachingSchedule::find($scheduleData['id']);

                if ($schedule) {
                    $activityCode = strtolower($schedule->activity->code ?? '');

                    switch ($activityCode) {
                        case 'k': // Kuliah
                            $this->updateKuliah($schedule, $scheduleData);
                            break;
                        case 'pr':
                        case 'up':
                            $this->updatePraktikum($schedule, $scheduleData);
                            break;
                        case 'usl':
                            $this->updateUjianSkillslab($schedule, $scheduleData);
                            break;
                        case 'sl':
                            $this->updateSkillslab($schedule, $scheduleData);
                            break;
                        case 't':
                            $this->updatePemicu($schedule, $scheduleData);
                            break;
                        case 'p':
                            $this->updatePleno($schedule, $scheduleData);
                            break;
                        case 'u': // Ujian
                            $this->updateUjian($schedule, $scheduleData);
                            break;
                        default:
                            // Lainnya
                            $this->updateKuliah($schedule, $scheduleData);
                            break;
                    }

                    $updatedSchedules[] = $schedule->load('activity', 'lecturer.user');
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal berhasil diperbarui.',
                    'updated_schedules' => $updatedSchedules,
                ]);
            }

            return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error updating schedules', [
                'error' => $e->getMessage(),
                'course_schedule_id' => $courseScheduleId,
                'data' => $request->all(),
            ]);

            if ($request->ajax()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Gagal memperbarui jadwal: ' . $e->getMessage(),
                    ],
                    500,
                );
            }

            return redirect()
                ->back()
                ->withErrors(['error' => 'Gagal memperbarui jadwal: ' . $e->getMessage()]);
        }
    }

    private function updateKuliah($schedule, $data)
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        // Siapkan data untuk update
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start ?? null,
            'end_time' => $end ?? null,
            'room' => $data['room'] ?? null,
            'zone' => $data['zone'] ?? null,
            'topic' => $data['topic'] ?? null,
            'lecturer_id' => $data['lecturer_id'] ?? null,
        ];

        if (!empty($data['zone'])) {
            $updateData['group'] = 'AB';
        }

        // Update schedule
        $schedule->update($updateData);
        $schedule->refresh();

        // Jika ada lecturer_id yang dipilih, cek dan tambahkan ke course_lecturer dan course_lecturer_activity
        if (!empty($data['lecturer_id'])) {
            $this->addLecturerIfNotExists($schedule, $data['lecturer_id']);
        }
    }
    private function updatePleno($schedule, $data)
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        // Siapkan data untuk update
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start ?? null,
            'end_time' => $end ?? null,
            'zone' => $data['zone'] ?? null,
        ];

        if (!empty($data['zone'])) {
            $updateData['group'] = 'AB';
        }

        // Update schedule
        $schedule->update($updateData);
        $schedule->refresh();
    }
    private function updatePraktikum($schedule, $data)
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        if (!empty($data['group'])) {
            $group = strtoupper($data['group']);
        } elseif (!empty($data['zone'])) {
            $group = 'AB';
        } else {
            $group = null;
        }
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start ?? null,
            'end_time' => $end ?? null,
            'zone' => $data['zone'] ?? null,
            'group' => $group,
            'topic' => isset($data['topic']) ? strtolower($data['topic']) : null,
        ];

        // Update schedule
        $schedule->update($updateData);
        $schedule->refresh();
    }

    private function updateSkillslab($schedule, $data)
    {
        $groups = SkillslabDetails::where('course_schedule_id', $schedule->course_schedule_id)->select('group_code')->distinct()->orderBy('group_code')->get()->pluck('group_code');
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        $currentSession = $schedule->session_number;
        $zoneIsFilled = !empty($data['zone']);
        $groupIsFilled = !empty($data['group']);

        if ($zoneIsFilled && !$groupIsFilled) {
            foreach ($groups as $index => $groupCode) {
                $sessionNumber = $currentSession + $index;

                $existingSchedule = TeachingSchedule::where([
                    'course_schedule_id' => $schedule->course_schedule_id,
                    'course_id' => $schedule->course_id,
                    'semester_id' => $schedule->semester_id,
                    'activity_id' => $schedule->activity_id,
                    'session_number' => $sessionNumber,
                ])->first();

                $updateData = [
                    'scheduled_date' => $data['scheduled_date'] ?? null,
                    'start_time' => $start ?? null,
                    'end_time' => $end ?? null,
                    'zone' => $data['zone'] ?? null,
                    'group' => $groupCode,
                    'topic' => isset($data['topic']) ? strtolower($data['topic']) : null,
                ];

                if ($existingSchedule) {
                    $existingSchedule->update($updateData);
                } else {
                    TeachingSchedule::create(
                        array_merge(
                            [
                                'course_schedule_id' => $schedule->course_schedule_id,
                                'course_id' => $schedule->course_id,
                                'semester_id' => $schedule->semester_id,
                                'activity_id' => $schedule->activity_id,
                                'session_number' => $sessionNumber,
                                'created_by' => auth()->id(),
                            ],
                            $updateData,
                        ),
                    );
                }
            }
        } elseif ($zoneIsFilled && $groupIsFilled) {
            $updateData = [
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'start_time' => $start ?? null,
                'end_time' => $end ?? null,
                'zone' => $data['zone'] ?? null,
                'group' => $data['group'],
                'topic' => isset($data['topic']) ? strtolower($data['topic']) : null,
            ];
            $schedule->update($updateData);
        }
        $schedule->refresh();
    }
    private function updateUjianSkillslab($schedule, $data)
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        // Siapkan data untuk update
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start ?? null,
            'end_time' => $end ?? null,
            'room' => $data['room'] ?? null,
            'zone' => $data['zone'] ?? null,
            'topic' => $data['topic'] ?? null,
        ];

        if (!empty($data['zone'])) {
            $updateData['group'] = 'AB';
        }

        // Update schedule
        $schedule->update($updateData);
        $schedule->refresh();
    }

    private function updatePemicu($schedule, $data)
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        if (!empty($data['group'])) {
            $group = strtoupper($data['group']);
        } elseif (!empty($data['zone'])) {
            $group = 'AB';
        } else {
            $group = null;
        }
        // Siapkan data untuk update
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start ?? null,
            'end_time' => $end ?? null,
            'zone' => $data['zone'] ?? null,
            'group' => $group,
        ];

        // Update schedule
        $schedule->update($updateData);
        $schedule->refresh();
    }

    private function updateUjian($schedule, $data)
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        if (!empty($data['group'])) {
            $group = strtoupper($data['group']);
        } elseif (!empty($data['zone'])) {
            $group = 'AB';
        } else {
            $group = null;
        }
        $schedule->update([
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start ?? null,
            'end_time' => $end ?? null,
            'zone' => $data['zone'] ?? null,
            'group' => $group,
            'topic' => $data['topic'] ?? null,
            'room' => $data['room'] ?? null,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = TeachingSchedule::findOrFail($id);

        $schedule->update([
            'scheduled_date' => null,
            'start_time' => null,
            'end_time' => null,
            'room' => null,
            'zone' => null,
            'group' => null,
            'topic' => null,
            'lecturer_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schedule berhasil dikosongkan.',
        ]);
    }
    private function addLecturerIfNotExists($schedule, $lecturerId)
    {
        $courseSchedule = $schedule->courseSchedule;
        if (!$courseSchedule) {
            return;
        }

        $courseId = $courseSchedule->course_id;
        $semesterId = $courseSchedule->semester_id;
        $activityId = $schedule->activity_id;

        $courseLecturer = CourseLecturer::where([
            'course_id' => $courseId,
            'lecturer_id' => $lecturerId,
            'semester_id' => $semesterId,
        ])->first();

        if (!$courseLecturer) {
            $courseLecturer = CourseLecturer::create([
                'course_id' => $courseId,
                'lecturer_id' => $lecturerId,
                'semester_id' => $semesterId,
            ]);
        }

        $courseLecturerActivity = CourseLecturerActivity::where([
            'course_lecturer_id' => $courseLecturer->id,
            'activity_id' => $activityId,
        ])->first();

        if (!$courseLecturerActivity) {
            CourseLecturerActivity::create([
                'course_lecturer_id' => $courseLecturer->id,
                'activity_id' => $activityId,
            ]);
        }
    }
}
