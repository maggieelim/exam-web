<?php

namespace App\Http\Controllers\PSSK;

use App\Exports\AllNilaiPemicuExport;
use App\Exports\NilaiPemicuExport;
use App\Exports\PemicuExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\PemicuDetails;
use App\Models\PemicuScore;
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
        $course = Course::with([
            'courseLecturer' => function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId)
                    ->whereHas('activities', fn($q) => $q->where('activity_id', 5))
                    ->with(['lecturer.user', 'activities']);
            }
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $kelompok = CourseStudent::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->distinct()
            ->orderBy('kelompok')
            ->pluck('kelompok');

        $tutors = TeachingSchedule::where('activity_id', 5)
            ->where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereNotNull('scheduled_date')
            ->with('pemicuDetails')
            ->orderBy('session_number')
            ->get();

        $tutors->each(function ($tutor) {
            $tutor->formatted_date = Carbon::parse($tutor->scheduled_date)->translatedFormat('D d/M');
            $tutor->formatted_start = Carbon::parse($tutor->start_time)->translatedFormat('H:i');
            $tutor->formatted_end = Carbon::parse($tutor->end_time)->translatedFormat('H:i');
        });

        $lecturers = $sorter->sort($course->courseLecturer, $course->id, $semesterId);

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
                    null,
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

            foreach ($assignments as $lecturerId => $tutorAssignments) {
                foreach ($tutorAssignments as $tutorId => $assignmentData) {
                    $kelompok = $assignmentData['kelompok'] ?? null;
                    \Log::info($lecturerId . ' ' . $tutorId . ' ' . $kelompok);
                    if (!empty($kelompok)) {
                        PemicuDetails::updateOrCreate(
                            [
                                'teaching_schedule_id' => $tutorId,
                                'lecturer_id' => $lecturerId,
                            ],
                            [
                                'kelompok_num' => $kelompok,
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
                        PemicuDetails::where('teaching_schedule_id', $tutorId)->where('lecturer_id', $lecturerId)->update([
                            'kelompok_num' => null,
                        ]);
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

    private function getPemicuDetailsData($id1, $id2)
    {
        $pemicuDetails = PemicuDetails::with([
            'lecturer.user',
            'pemicuScore',
            'teachingSchedule.course.courseStudents.student.user'
        ])
            ->whereIn('teaching_schedule_id', [$id1, $id2])
            ->get();

        $firstDetail = $pemicuDetails->first();
        $pemicuNumber = intval($firstDetail->teachingSchedule->pemicu_ke);
        $preGroup = floor(($pemicuNumber - 11) / 10) + 1;

        $course = $firstDetail->teachingSchedule->course;

        $groupedStudents = $course->courseStudents->groupBy('kelompok')->sortKeys();

        $scores = PemicuScore::whereIn('pemicu_detail_id', $pemicuDetails->pluck('id'))
            ->get()
            ->groupBy('course_student_id');

        $groupLecturer = $pemicuDetails->groupBy('kelompok_num')->map(function ($items) {
            return $items->mapWithKeys(function ($d) {
                return [
                    $d->teaching_schedule_id => $d->lecturer->user->name
                ];
            });
        });

        return compact(
            'pemicuDetails',
            'groupedStudents',
            'scores',
            'groupLecturer',
            'preGroup',
            'course'
        );
    }

    public function nilai($id1, $id2)
    {
        try {
            $data = $this->getPemicuDetailsData($id1, $id2);

            // validasi data penting
            if (
                empty($data['pemicuDetails']) ||
                $data['pemicuDetails']->isEmpty()
            ) {
                return redirect()->back()
                    ->with('error', 'Data nilai pemicu belum tersedia.');
            }

            return view('pssk.courses.pemicu.nilai', array_merge($data, [
                'id1' => $id1,
                'id2' => $id2,
            ]));
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Data belum lengkap atau belum diinput.');
        }
    }

    public function downloadPemicu($id1, $id2)
    {
        $data = $this->getPemicuDetailsData($id1, $id2);

        $filename = "Nilai_Pemicu_{$data['preGroup']}_Blok_{$data['course']->slug}.xlsx";

        return Excel::download(
            new NilaiPemicuExport(
                $data['groupedStudents'],
                $data['scores'],
                $data['groupLecturer'],
                $id1,
                $id2,
                $data['preGroup'],
                $data['course']
            ),
            $filename
        );
    }

    private function getAllPemicuData($id, $semester)
    {
        $teachingSchedules = TeachingSchedule::where('course_id', $id)
            ->where('semester_id', $semester)
            ->where('activity_id', 5)
            ->orderBy('pemicu_ke')
            ->get();

        if ($teachingSchedules->isEmpty()) {
            return [
                'semester' => $semester,
                'pemicuGroups' => [],
                'preGroup' => 0,
                'course' => Course::find($id),
                'groupedStudents' => collect(),
                'scores' => collect(),
                'groupLecturer' => collect(),
                'teachingSchedules' => collect(),
            ];
        }

        $pemicuGroups = [];
        foreach ($teachingSchedules as $index => $schedule) {
            $pemicuNumber = ceil(($index + 1) / 2);
            $pemicuGroups[$pemicuNumber][] = $schedule->id;
        }

        $preGroup = count($pemicuGroups);
        $course = $teachingSchedules->first()->course ?? Course::find($id);

        $pemicuDetails = PemicuDetails::with([
            'lecturer.user',
            'pemicuScore',
            'teachingSchedule'
        ])
            ->whereIn('teaching_schedule_id', $teachingSchedules->pluck('id'))
            ->get();

        $groupedStudents = optional($course)->courseStudents
            ? $course->courseStudents->groupBy('kelompok')->sortKeys()
            : collect();

        $scores = PemicuScore::whereIn('pemicu_detail_id', $pemicuDetails->pluck('id'))
            ->get()
            ->groupBy('course_student_id');

        $groupLecturer = $pemicuDetails->mapWithKeys(function ($d) {
            return [
                $d->kelompok_num => optional($d->lecturer->user)->name ?? '-'
            ];
        });

        return compact(
            'semester',
            'pemicuGroups',
            'preGroup',
            'course',
            'groupedStudents',
            'scores',
            'groupLecturer',
            'teachingSchedules'
        );
    }

    public function allPemicu($id, Request $request)
    {
        $semester = $request->query('semester_id');
        $data = $this->getAllPemicuData($id, $semester);

        return view('pssk.courses.pemicu.pemicu_report', $data);
    }

    public function downloadAllPemicu($id, $semester)
    {
        $data = $this->getAllPemicuData($id, $semester);

        if (empty($data['pemicuGroups'])) {
            abort(404, 'Tidak ada data pemicu yang ditemukan');
        }

        $filename = "Nilai_Diskusi_Blok_{$data['course']->slug}.xlsx";

        return Excel::download(
            new AllNilaiPemicuExport(
                $data['pemicuGroups'],
                $data['preGroup'],
                $data['course'],
                $data['groupedStudents'],
                $data['scores'],
                $data['groupLecturer'],
                $data['teachingSchedules']
            ),
            $filename
        );
    }
}
