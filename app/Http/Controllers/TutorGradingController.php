<?php

namespace App\Http\Controllers;

use App\Exports\LecturerTutorGrading;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\Lecturer;
use App\Models\PemicuDetails;
use App\Models\PemicuScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TutorGradingController extends Controller
{
    public function index()
    {
        $lecturer = Lecturer::where('user_id', Auth::id())->firstOrFail();
        $details = PemicuDetails::with('teachingSchedule.course')
            ->where('lecturer_id', $lecturer->id)
            ->orderBy('teaching_schedule_id')
            ->get();

        $grouped = $details->groupBy(fn($item) => $item->teachingSchedule->course_id . '-' . $item->kelompok_num);

        $tutors = $grouped->map(function ($group) use ($lecturer) {
            $first = $group->first();
            $courseId = $first->teachingSchedule->course_id;
            $kelompok = $first->kelompok_num;

            // Ambil list pemicu_ke untuk group ini
            $pemicuKeList = $group->pluck('teachingSchedule.pemicu_ke')->unique()->sort()->values();
            $pemicuKe = intval(substr($pemicuKeList->first(), 0, 1));
            // Hitung total mahasiswa per course + kelompok
            $studentCount = CourseStudent::where('course_id', $courseId)
                ->where('kelompok', $kelompok)
                ->count();

            // Auto-create PemicuScore jika belum ada
            $group->pluck('teachingSchedule')->each(function ($schedule) use ($courseId, $kelompok, $lecturer) {
                $students = CourseStudent::where('course_id', $courseId)
                    ->where('kelompok', $kelompok)
                    ->get();
                foreach ($students as $student) {
                    $pemicuDetail = PemicuDetails::where('lecturer_id', $lecturer->id)
                        ->where('kelompok_num', $student->kelompok)
                        ->where('teaching_schedule_id', $schedule->id) // jadwal pemicu
                        ->first();

                    PemicuScore::firstOrCreate(
                        [
                            'course_student_id' => $student->id,
                            'pemicu_detail_id' => $pemicuDetail->id,
                            'teaching_schedule_id' => $schedule->id,
                        ],
                        [
                            'disiplin' => 0,
                            'keaktifan' => 0,
                            'berpikir_kritis' => 0,
                            'info_baru' => 0,
                            'analisis_rumusan' => 0,
                            'total_score' => 0,
                        ]
                    );
                }
            });
            $pemicuDetailIdList = $group->pluck('id')->values();
            return [
                'course' => $first->teachingSchedule->course,
                'kelompok' => $kelompok,
                'pemicu_ke' => $pemicuKeList,
                'pemicu' => $pemicuKe,
                'student_count' => $studentCount,
                'pemicu_detail_ids' => $pemicuDetailIdList,
            ];
        })->values();

        return view('pemicu.index', compact('tutors'));
    }

    public function show($courseId, $kelompok, Request $request)
    {
        $pemicusJson = $request->get('pemicu', '[]');
        $pemicu = json_decode($pemicusJson, true); // hasil array
        $course = Course::where('id', $courseId)->firstOrFail();
        $kel = $kelompok;

        $students = PemicuScore::with(['courseStudent.student.user', 'pemicuDetail'])
            ->whereIn('pemicu_detail_id', $pemicu)
            ->get()
            ->groupBy('course_student_id');
        return view('pemicu.show', compact('kel', 'students', 'course', 'pemicu'));
    }


    public function edit($pemicu, $studentId, Request $request)
    {
        $pemicusJson = $request->get('pemicus', '[]');
        $pemicus = json_decode($pemicusJson, true);
        $score = PemicuScore::with('pemicuDetail', 'courseStudent.course', 'courseStudent.student.user', 'teachingSchedule')->where('pemicu_detail_id', $pemicu)->where('course_student_id', $studentId)->firstOrFail();
        $student = $score->courseStudent->student;
        $teachingSchedule = $score->teachingSchedule;
        return view('pemicu.edit', compact('score', 'pemicus', 'student', 'teachingSchedule'));
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

        return redirect()->route('tutors.show', [
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
