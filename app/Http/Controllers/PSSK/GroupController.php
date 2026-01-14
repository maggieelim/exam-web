<?php

namespace App\Http\Controllers\PSSK;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseStudent;
use App\Models\PracticumDetails;
use App\Models\PracticumGroup;
use App\Models\PracticumGroupMember;
use App\Models\SkillslabDetails;
use App\Models\TeachingSchedule;
use DB;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function updateGroup(Request $request, string $slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->input('semester_id');

        // Jika ada data group yang dipilih manual
        if ($request->has('selected_groups') || $request->has('selected_practicum_groups')) {
            $this->updateGroupManual($request, $course, $semesterId);
            $this->updatePracticumGroupManual($request, $course, $semesterId);

            return redirect()->back()->with('success', 'Group Skill Lab dan Praktikum berhasil diperbarui.');
        }

        // Jika tidak, lakukan distribusi otomatis
        $this->updatePracticumGroupAuto($request, $course, $semesterId);
        $this->updateGroupAuto($request, $course, $semesterId);
        return redirect()->back()->with('success', 'Group Skill Lab dan Praktikum berhasil dibentuk secara otomatis.');
    }

    private function updatePracticumGroupAuto(Request $request, $course, $semesterId)
    {
        $teachingSchedule = TeachingSchedule::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereIn('activity_id', [3, 7])
            ->get();

        if ($teachingSchedule->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal teaching untuk praktikum.');
        }

        $jumlahKelompok = CourseStudent::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereNotNull('kelompok')
            ->distinct('kelompok')
            ->count('kelompok');

        if ($jumlahKelompok === 0) {
            return redirect()->back()->with('error', 'Belum ada kelompok yang terbentuk.');
        }

        // Grup untuk tipe1
        $tipe1Groups = ['A1', 'A2', 'B1', 'B2'];
        $jumlahGrupTipe1 = count($tipe1Groups);

        // Hitung distribusi kelompok per grup
        $base = intdiv($jumlahKelompok, $jumlahGrupTipe1);
        $remainder = $jumlahKelompok % $jumlahGrupTipe1;

        $distribusi = [];
        $start = 1;

        for ($i = 0; $i < $jumlahGrupTipe1; $i++) {
            $jumlah = $base + ($i < $remainder ? 1 : 0);
            $end = $start + $jumlah - 1;
            if ($jumlah > 0) {
                $distribusi[$tipe1Groups[$i]] = range($start, min($end, $jumlahKelompok));
            } else {
                $distribusi[$tipe1Groups[$i]] = [];
            }
            $start = $end + 1;
        }

        DB::transaction(function () use ($distribusi, $course, $semesterId, $teachingSchedule, $tipe1Groups) {
            // Hapus semua data practicum groups tipe1 untuk course dan semester ini
            $teachingScheduleIds = $teachingSchedule->pluck('id');

            $practicumGroupIds = PracticumGroup::whereIn('teaching_schedule_id', $teachingScheduleIds)
                ->where('tipe', 'tipe1')
                ->pluck('id');

            PracticumGroupMember::whereIn('practicum_group_id', $practicumGroupIds)->delete();
            PracticumGroup::whereIn('id', $practicumGroupIds)->delete();

            // Buat distribusi otomatis untuk tipe1
            foreach ($distribusi as $groupCode => $kelompokList) {
                foreach ($kelompokList as $kelompokNum) {
                    foreach ($teachingSchedule as $schedule) {
                        // Create practicum group
                        $practicumGroup = PracticumGroup::create([
                            'course_schedule_id' => $schedule->course_schedule_id,
                            'teaching_schedule_id' => $schedule->id,
                            'course_id' => $course->id,
                            'semester_id' => $semesterId,
                            'tipe' => 'tipe1',
                            'group_code' => $groupCode,
                        ]);

                        // Add kelompok to this practicum group
                        PracticumGroupMember::create([
                            'practicum_group_id' => $practicumGroup->id,
                            'kelompok_num' => $kelompokNum,
                        ]);
                    }
                }
            }

            // Untuk tipe2 dan tipe3, kosongkan (optional)
            PracticumGroup::whereIn('teaching_schedule_id', $teachingScheduleIds)
                ->whereIn('tipe', ['tipe2', 'tipe3'])
                ->delete();
        });

        $message = "Grup praktikum tipe1 berhasil dibentuk secara otomatis. Distribusi: ";
        foreach ($distribusi as $group => $kelompok) {
            $message .= "$group: " . count($kelompok) . " kelompok, ";
        }
        $message = rtrim($message, ', ');

        return redirect()->back()->with('success', $message);
    }

    private function updatePracticumGroupManual(Request $request, $course, $semesterId)
    {
        $selectedGroups = $request->input('selected_practicum_groups', []);

        if (empty($selectedGroups)) {
            return redirect()->back()->with('error', 'Tidak ada grup praktikum yang dipilih.');
        }

        $teachingSchedule = TeachingSchedule::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereIn('activity_id', [3, 7])
            ->get();

        if ($teachingSchedule->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada jadwal teaching untuk praktikum.');
        }

        DB::transaction(function () use ($selectedGroups, $teachingSchedule, $course, $semesterId) {
            // Hapus semua data practicum groups untuk course dan semester ini
            $teachingScheduleIds = $teachingSchedule->pluck('id');

            // Dapatkan practicum group IDs yang terkait
            $practicumGroupIds = PracticumGroup::whereIn('teaching_schedule_id', $teachingScheduleIds)->pluck('id');

            PracticumGroupMember::whereIn('practicum_group_id', $practicumGroupIds)->delete();
            PracticumGroup::whereIn('id', $practicumGroupIds)->delete();

            // Process selected practicum groups
            foreach ($selectedGroups as $kelompokNum => $groupByType) {
                foreach ($groupByType as $tipe => $groupCodes) {
                    foreach ($groupCodes as $groupCode => $isSelected) {
                        // Only process if checkbox is checked
                        if ($isSelected == '1') {
                            foreach ($teachingSchedule as $schedule) {
                                // Create or get practicum group
                                $practicumGroup = PracticumGroup::firstOrCreate(
                                    [
                                        'course_schedule_id' => $schedule->course_schedule_id,
                                        'teaching_schedule_id' => $schedule->id,
                                        'tipe' => $tipe,
                                        'group_code' => $groupCode,
                                    ],
                                    [
                                        'course_id' => $course->id,
                                        'semester_id' => $semesterId,
                                    ],
                                );

                                // Add kelompok to this practicum group
                                PracticumGroupMember::create([
                                    'practicum_group_id' => $practicumGroup->id,
                                    'kelompok_num' => $kelompokNum,
                                ]);
                            }
                        }
                    }
                }
            }

            // Clean up - delete practicum groups without members
            $practicumGroupsWithMembers = PracticumGroupMember::distinct()->pluck('practicum_group_id');
            PracticumGroup::whereNotIn('id', $practicumGroupsWithMembers)->delete();
        });

        return redirect()->back()->with('success', 'Grup praktikum berhasil diupdate.');
    }
    private function updateGroupAuto(Request $request, $course, $semesterId)
    {
        $teachingSchedule = TeachingSchedule::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereIn('activity_id', [2, 8])
            ->get();

        $jumlahKelompok = CourseStudent::where('course_id', $course->id)->where('semester_id', $semesterId)->whereNotNull('kelompok')->distinct('kelompok')->count('kelompok');

        if ($jumlahKelompok === 0) {
            return redirect()->back()->with('error', 'Belum ada kelompok yang terbentuk.');
        }

        $groupLabels = ['A', 'B', 'C', 'D'];
        $base = intdiv($jumlahKelompok, 4);
        $remainder = $jumlahKelompok % 4;

        $distribusi = [];
        $start = 1;

        for ($i = 0; $i < 4; $i++) {
            $jumlah = $base + ($i < $remainder ? 1 : 0);
            $end = $start + $jumlah - 1;
            if ($jumlah > 0) {
                $distribusi[$groupLabels[$i]] = range($start, min($end, $jumlahKelompok));
            } else {
                $distribusi[$groupLabels[$i]] = [];
            }
            $start = $end + 1;
        }

        DB::transaction(function () use ($distribusi, $course, $semesterId, $teachingSchedule) {
            foreach ($distribusi as $groupCode => $kelompokList) {
                foreach ($kelompokList as $kelompokNum) {
                    foreach ($teachingSchedule as $schedule) {
                        SkillslabDetails::updateOrCreate(
                            [
                                'course_schedule_id' => $schedule->course_schedule_id,
                                'teaching_schedule_id' => $schedule->id,
                                'kelompok_num' => $kelompokNum,
                            ],
                            [
                                'group_code' => $groupCode,
                                'lecturer_id' => null,
                            ],
                        );
                    }
                }
            }

            $validGroupCodes = array_keys(array_filter($distribusi, fn($list) => !empty($list)));
            $validKelompokNums = collect($distribusi)->flatten()->toArray();

            SkillslabDetails::whereIn('teaching_schedule_id', $teachingSchedule->pluck('id'))
                ->where(function ($query) use ($validGroupCodes, $validKelompokNums) {
                    $query->whereNotIn('group_code', $validGroupCodes)->orWhereNotIn('kelompok_num', $validKelompokNums);
                })
                ->delete();
        });

        return redirect()->back()->with('success', 'Grup berhasil dibentuk berdasarkan distribusi otomatis.');
    }

    private function updateGroupManual(Request $request, $course, $semesterId)
    {
        $selectedGroups = $request->input('selected_groups', []);

        if ($selectedGroups === []) {
            return redirect()->back()->with('error', 'Tidak ada grup yang dipilih.');
        }

        $teachingSchedule = TeachingSchedule::where('course_id', $course->id)
            ->where('semester_id', $semesterId)
            ->whereIn('activity_id', [2, 8])
            ->get();

        // Group selected groups by kelompok
        $groupedByKelompok = [];
        foreach ($selectedGroups as $group) {
            // Format: "group_kelompok" (contoh: "A_1", "B_2", dll)
            $parts = explode('_', $group);
            if (count($parts) === 2) {
                $groupCode = $parts[0];
                $kelompokNum = $parts[1];
                $groupedByKelompok[$kelompokNum][] = $groupCode;
            }
        }

        $errors = [];
        $skillLabGroups = ['A', 'B', 'C', 'D']; // Grup untuk Skill Lab

        foreach ($groupedByKelompok as $kelompokNum => $groupCodes) {
            $selectedSkillLabGroups = array_intersect($groupCodes, $skillLabGroups);

            if (count($selectedSkillLabGroups) > 1) {
                $errors[] = "Kelompok $kelompokNum memilih lebih dari satu grup Skill Lab: " . implode(', ', $selectedSkillLabGroups) . '. Hanya boleh memilih satu grup Skill Lab.';
            }
        }

        // Jika ada error, kembalikan dengan pesan error
        if (!empty($errors)) {
            return redirect()->back()->with('error', implode('<br>', $errors))->withInput();
        }

        DB::transaction(function () use ($groupedByKelompok, $course, $semesterId, $teachingSchedule) {
            SkillslabDetails::whereIn('teaching_schedule_id', $teachingSchedule->pluck('id'))->delete();

            foreach ($groupedByKelompok as $kelompokNum => $groupCodes) {
                foreach ($groupCodes as $groupCode) {
                    foreach ($teachingSchedule as $schedule) {
                        SkillslabDetails::create([
                            'course_schedule_id' => $schedule->course_schedule_id,
                            'teaching_schedule_id' => $schedule->id,
                            'kelompok_num' => $kelompokNum,
                            'group_code' => $groupCode,
                            'lecturer_id' => null,
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Grup berhasil diupdate.');
    }
}
