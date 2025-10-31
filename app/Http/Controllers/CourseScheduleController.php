<?php

namespace App\Http\Controllers;

use App\Helpers\ZoneTimeHelper;
use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\CourseScheduleDetail;
use App\Models\Exam;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use DB;
use Illuminate\Http\Request;
use Str;

class CourseScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courseSchedules = CourseSchedule::with(['course', 'semester.academicYear', 'creator'])
            ->latest()
            ->paginate(10);

        return view('courses.schedule.index', compact('courseSchedules'));
    }

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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $courseSchedule = CourseSchedule::with(['course', 'semester'])->findOrFail($id);

        $teachingSchedules = TeachingSchedule::with(['activity', 'lecturer'])
            ->where('course_id', $courseSchedule->course_id)
            ->where('semester_id', $courseSchedule->semester_id)
            ->orderBy('activity_id')
            ->orderBy('session_number')
            ->get()
            ->groupBy(function ($item) {
                $name = strtolower($item->activity->activity_name);
                // ✅ Gabungkan kategori sejenis
                if (Str::contains($name, 'ujian praktikum') || Str::contains($name, 'praktikum')) {
                    return 'PRAKTIKUM';
                } elseif (Str::contains($name, 'ujian skill lab') || Str::contains($name, 'skill lab')) {
                    return 'SKILL LAB';
                }
                // Default: group berdasarkan nama asli
                return strtoupper($item->activity->activity_name);
            });

        $lecturers = Lecturer::with('user')->get();

        return view('courses.schedule.show', compact('courseSchedule', 'teachingSchedules', 'lecturers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function updateSchedules(Request $request, $courseScheduleId)
    {
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|exists:teaching_schedules,id',
            'schedules.*.scheduled_date' => 'nullable|date',

            'schedules.*.room' => 'nullable|string|max:50',
            // Field khusus untuk activity T/P
            'schedules.*.zone' => 'nullable|string|max:20',
            'schedules.*.group' => 'nullable|string|max:20',
            'schedules.*.topic' => 'nullable|string|max:255',
            'schedules.*.lecturer_id' => 'nullable|exists:lecturers,id',
            // Field khusus untuk activity U
            'schedules.*.exam_type' => 'nullable|string|in:UTS,UAS,QUIZ,TUGAS',
            'schedules.*.supervisor_id' => 'nullable|exists:lecturers,id',
            'schedules.*.notes' => 'nullable|string|max:500',
            // Field khusus untuk activity lainnya
            'schedules.*.location' => 'nullable|string|max:100',
            'schedules.*.instructor_id' => 'nullable|exists:lecturers,id',
            'schedules.*.equipment' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->schedules as $scheduleData) {
                $schedule = TeachingSchedule::find($scheduleData['id']);

                if ($schedule) {
                    // Update field dasar
                    [$start, $end] = ZoneTimeHelper::getTimes($scheduleData['zone'] ?? null);
                    $schedule->update([
                        'scheduled_date' => $scheduleData['scheduled_date'] ?? null,
                        'start_time' => $start ?? null,
                        'end_time' => $end ?? null,
                        'room' => $scheduleData['room'] ?? null,
                        'zone' => $scheduleData['zone'] ?? null,
                    ]);

                    // Update field khusus berdasarkan activity
                    $activityCode = strtolower($schedule->activity->code ?? '');

                    if (in_array($activityCode, ['t', 'p', 'k'])) {
                        $schedule->update([
                            'group' => $scheduleData['group'] ?? null,
                            'topic' => $scheduleData['topic'] ?? null,
                            'lecturer_id' => $scheduleData['lecturer_id'] ?? null,
                        ]);
                    } elseif ($activityCode === 'u') {
                        $schedule->update([
                            'exam_type' => $scheduleData['exam_type'] ?? null,
                            'supervisor_id' => $scheduleData['supervisor_id'] ?? null,
                            'notes' => $scheduleData['notes'] ?? null,
                        ]);
                    } else {
                        $schedule->update([
                            'location' => $scheduleData['location'] ?? null,
                            'instructor_id' => $scheduleData['instructor_id'] ?? null,
                            'equipment' => $scheduleData['equipment'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors(['error' => 'Gagal memperbarui jadwal: ' . $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
