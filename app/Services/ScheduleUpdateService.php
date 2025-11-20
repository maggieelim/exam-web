<?php

namespace App\Services;

use App\Models\TeachingSchedule;
use App\Models\AttendanceSessions;
use App\Models\CourseLecturer;
use App\Models\CourseLecturerActivity;
use App\Models\LecturerAttendanceRecords;
use App\Models\SkillslabDetails;
use App\Helpers\ZoneTimeHelper;
use App\Models\AttendanceRecords;
use App\Models\CourseStudent;
use App\Services\ScheduleConflictService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ScheduleUpdateService
{
    private array $updateHandlers;

    public function __construct()
    {
        $this->updateHandlers = [
            'k' => 'updateKuliah',
            'pr' => 'updatePraktikum',
            'up' => 'updatePraktikum',
            'usl' => 'updateUjianSkillslab',
            'sl' => 'updateSkillslab',
            't' => 'updatePemicu',
            'p' => 'updatePleno',
            'u' => 'updateUjian',
        ];
    }

    private function getUpdatedScheduleData(TeachingSchedule $schedule): array
    {
        $schedule->refresh();
        $schedule->load(['activity', 'lecturer.user']); // Pastikan relasi diload

        return [
            'id' => $schedule->id,
            'scheduled_date' => $schedule->scheduled_date,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'zone' => $schedule->zone,
            'group' => $schedule->group,
            'room' => $schedule->room,
            'topic' => $schedule->topic,
            'lecturer_id' => $schedule->lecturer_id,
            'activity' => [
                'id' => $schedule->activity->id,
                'activity_name' => $schedule->activity->activity_name,
                'code' => $schedule->activity->code,
            ],
            'lecturer' => $schedule->lecturer ? [
                'id' => $schedule->lecturer->id,
                'user' => $schedule->lecturer->user ? [
                    'name' => $schedule->lecturer->user->name,
                ] : null,
            ] : null,
        ];
    }

    // Update method updateSchedules untuk menggunakan method baru
    public function updateSchedules(array $schedules): array
    {
        $updatedSchedules = [];
        $failedSchedules = [];

        foreach ($schedules as $scheduleData) {
            $result = $this->updateSingleSchedule($scheduleData);

            if ($result && isset($result['success'])) {
                if ($result['success'] === false) {
                    $failedSchedules[] = $result;
                } elseif (isset($result['schedule'])) {
                    $updatedSchedules[] = $this->getUpdatedScheduleData($result['schedule']);
                }
            }
        }

        return [
            'updatedSchedules' => $updatedSchedules,
            'failedSchedules' => $failedSchedules
        ];
    }

    private function updateSingleSchedule(array $scheduleData): ?array
    {
        $schedule = TeachingSchedule::with('activity')->find($scheduleData['id']);
        if (!$schedule) {
            return null;
        }

        $activityCode = strtolower($schedule->activity->code ?? '');
        $handlerMethod = $this->updateHandlers[$activityCode] ?? 'updateKuliah';

        if (!method_exists($this, $handlerMethod)) {
            Log::error("Handler method not found: {$handlerMethod}");
            return null;
        }

        return $this->{$handlerMethod}($schedule, $scheduleData);
    }

    private function updateKuliah(TeachingSchedule $schedule, array $data): array
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        $oldLecturerId = $schedule->lecturer_id;
        $newLecturerId = $data['lecturer_id'] ?? null;

        $updateData = $this->prepareKuliahUpdateData($data, $start, $end, $newLecturerId);

        // Check lecturer conflict
        $conflictResult = $this->checkLecturerConflict($newLecturerId, $data, $start, $end, $schedule);
        if ($conflictResult) {
            return $conflictResult;
        }

        // Remove old lecturer attendance if lecturer changed
        if ($oldLecturerId && $oldLecturerId != $newLecturerId) {
            $this->removeLecturerAttendance($schedule->id, $oldLecturerId);
        }

        // Update schedule
        $schedule->update($updateData);
        $schedule->refresh();

        // Sync attendance
        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        // Add lecturer relationship if needed
        if (!empty($newLecturerId)) {
            $this->addLecturerIfNotExists($schedule, $newLecturerId);
        }

        return ['success' => true, 'schedule' => $schedule->load('activity', 'lecturer.user')];
    }

    private function prepareKuliahUpdateData(array $data, ?string $start, ?string $end, ?int $lecturerId): array
    {
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'room' => $data['room'] ?? null,
            'zone' => $data['zone'] ?? null,
            'topic' => $data['topic'] ?? null,
            'lecturer_id' => $lecturerId,
        ];

        if (!empty($data['zone'])) {
            $updateData['group'] = 'AB';
        }

        return $updateData;
    }

    private function checkLecturerConflict(?int $lecturerId, array $data, ?string $start, ?string $end, TeachingSchedule $schedule): ?array
    {
        if (!empty($lecturerId) && !empty($data['scheduled_date']) && $start && $end) {
            $conflictService = app(ScheduleConflictService::class);
            $isConflict = $conflictService->hasScheduleConflict(
                $lecturerId,
                $data['scheduled_date'],
                $start,
                $end,
                $schedule->id,
                $schedule->semester_id
            );

            if ($isConflict) {
                return [
                    'success' => false,
                    'schedule_id' => $schedule->id,
                    'lecturer_id' => $lecturerId,
                    'message' => 'Jadwal dosen bentrok dengan jadwal lain.',
                ];
            }
        }

        return null;
    }

    private function updatePleno(TeachingSchedule $schedule, array $data): array
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'zone' => $data['zone'] ?? null,
        ];

        if (!empty($data['zone'])) {
            $updateData['group'] = 'AB';
        }

        $schedule->update($updateData);
        $schedule->refresh();

        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        return ['success' => true, 'schedule' => $schedule->load('activity')];
    }

    private function updatePraktikum(TeachingSchedule $schedule, array $data): array
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        $group = $this->determineGroup($data);

        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'zone' => $data['zone'] ?? null,
            'group' => $group,
            'topic' => isset($data['topic']) ? strtolower($data['topic']) : null,
        ];

        $schedule->update($updateData);
        $schedule->refresh();

        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        return ['success' => true, 'schedule' => $schedule->load('activity')];
    }

    private function updateSkillslab(TeachingSchedule $schedule, array $data): array
    {
        $groups = SkillslabDetails::where('course_schedule_id', $schedule->course_schedule_id)
            ->select('group_code')
            ->distinct()
            ->orderBy('group_code')
            ->pluck('group_code');

        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        $zoneIsFilled = !empty($data['zone']);
        $groupIsFilled = !empty($data['group']);

        if ($zoneIsFilled && !$groupIsFilled) {
            $this->updateSkillslabGroups($schedule, $data, $groups, $start, $end);
        } elseif ($zoneIsFilled && $groupIsFilled) {
            $this->updateSingleSkillslab($schedule, $data, $start, $end);
        }

        $schedule->refresh();
        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        return ['success' => true, 'schedule' => $schedule->load('activity')];
    }

    private function updateSkillslabGroups(TeachingSchedule $schedule, array $data, $groups, ?string $start, ?string $end): void
    {
        foreach ($groups as $index => $groupCode) {
            $sessionNumber = $schedule->session_number + $index;

            $updateData = [
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'start_time' => $start,
                'end_time' => $end,
                'zone' => $data['zone'] ?? null,
                'group' => $groupCode,
                'topic' => isset($data['topic']) ? strtolower($data['topic']) : null,
            ];

            $existingSchedule = TeachingSchedule::where([
                'course_schedule_id' => $schedule->course_schedule_id,
                'course_id' => $schedule->course_id,
                'semester_id' => $schedule->semester_id,
                'activity_id' => $schedule->activity_id,
                'session_number' => $sessionNumber,
            ])->first();

            if ($existingSchedule) {
                $existingSchedule->update($updateData);
            } else {
                TeachingSchedule::create(array_merge([
                    'course_schedule_id' => $schedule->course_schedule_id,
                    'course_id' => $schedule->course_id,
                    'semester_id' => $schedule->semester_id,
                    'activity_id' => $schedule->activity_id,
                    'session_number' => $sessionNumber,
                    'created_by' => auth()->id(),
                ], $updateData));
            }
        }
    }

    private function updateSingleSkillslab(TeachingSchedule $schedule, array $data, ?string $start, ?string $end): void
    {
        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'zone' => $data['zone'] ?? null,
            'group' => $data['group'],
            'topic' => isset($data['topic']) ? strtolower($data['topic']) : null,
        ];

        $schedule->update($updateData);
    }

    private function updateUjianSkillslab(TeachingSchedule $schedule, array $data): array
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);

        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'room' => $data['room'] ?? null,
            'zone' => $data['zone'] ?? null,
            'topic' => $data['topic'] ?? null,
        ];

        if (!empty($data['zone'])) {
            $updateData['group'] = 'AB';
        }

        $schedule->update($updateData);
        $schedule->refresh();

        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        return ['success' => true, 'schedule' => $schedule->load('activity')];
    }

    private function updatePemicu(TeachingSchedule $schedule, array $data): array
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        $group = $this->determineGroup($data);

        $updateData = [
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'zone' => $data['zone'] ?? null,
            'group' => $group,
        ];

        $schedule->update($updateData);
        $schedule->refresh();

        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        return ['success' => true, 'schedule' => $schedule->load('activity')];
    }

    private function updateUjian(TeachingSchedule $schedule, array $data): array
    {
        [$start, $end] = ZoneTimeHelper::getTimes($data['zone'] ?? null);
        $group = $this->determineGroup($data);

        $schedule->update([
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'zone' => $data['zone'] ?? null,
            'group' => $group,
            'topic' => $data['topic'] ?? null,
            'room' => $data['room'] ?? null,
        ]);

        if ($schedule->examDetail) {
            $schedule->examDetail->update([
                'exam_date' => $data['scheduled_date'] ?? null,
            ]);
        }

        $schedule->refresh();
        $this->syncAttendanceWithSchedule($schedule, $data, $start, $end);

        return ['success' => true, 'schedule' => $schedule->load('activity')];
    }

    private function determineGroup(array $data): ?string
    {
        if (!empty($data['group'])) {
            return strtoupper($data['group']);
        } elseif (!empty($data['zone'])) {
            return 'AB';
        }

        return null;
    }

    private function syncAttendanceWithSchedule(TeachingSchedule $schedule, array $data, ?string $start, ?string $end): void
    {
        try {
            $scheduledDate = $data['scheduled_date'] ?? null;

            if (!$scheduledDate || !$start || !$end) {
                Log::warning('Incomplete data for attendance sync', [
                    'schedule_id' => $schedule->id,
                    'scheduled_date' => $scheduledDate,
                ]);
                return;
            }

            $startDateTime = Carbon::parse($scheduledDate . ' ' . $start);
            $endDateTime = Carbon::parse($scheduledDate . ' ' . $end);

            $activity = $schedule->activity_id;

            // Query dasar untuk mendapatkan students dengan course_student_id
            $studentsQuery = CourseStudent::with('student')
                ->where('course_id', $schedule->course_id)
                ->where('semester_id', $schedule->semester_id);

            // Filter khusus untuk SKILLSLAB berdasarkan group dan kelompok
            if ($activity === 2) {
                $skillslabKelompok = SkillslabDetails::where('course_schedule_id', $schedule->course_schedule_id)
                    ->where('group_code', $schedule->group)
                    ->pluck('kelompok_num')
                    ->toArray();

                Log::info('Skillslab group filtering', [
                    'schedule_id' => $schedule->id,
                    'group' => $schedule->group,
                    'kelompok_found' => $skillslabKelompok,
                    'course_schedule_id' => $schedule->course_schedule_id
                ]);


                $studentsQuery->whereIn('kelompok', $skillslabKelompok);
            }

            $students = $studentsQuery->get();

            Log::info('Students for attendance', [
                'schedule_id' => $schedule->id,
                'activity_code' => $activity,
                'group' => $schedule->group ?? 'N/A',
                'student_count' => $students->count(),
                'course_student_ids' => $students->pluck('id')->toArray()
            ]);

            // Cari atau buat attendance session
            $attendance = AttendanceSessions::where('teaching_schedule_id', $schedule->id)->first();

            if (!$attendance) {
                $attendance = AttendanceSessions::create([
                    'absensi_code' => 'ABS-' . strtoupper(Str::random(8)),
                    'semester_id' => $schedule->semester_id,
                    'course_id' => $schedule->course_id,
                    'teaching_schedule_id' => $schedule->id,
                    'activity_id' => $schedule->activity_id,
                    'start_time' => $startDateTime,
                    'end_time' => $endDateTime,
                    'created_by' => auth()->id(),
                ]);

                Log::info('Created new attendance session', [
                    'attendance_id' => $attendance->id,
                    'absensi_code' => $attendance->absensi_code
                ]);
            } else {
                $attendance->update([
                    'start_time' => $startDateTime,
                    'end_time' => $endDateTime,
                ]);

                Log::info('Updated existing attendance session', [
                    'attendance_id' => $attendance->id
                ]);
            }

            // GENERATE ALL STUDENT ATTENDANCE RECORDS DI AWAL
            $this->generateStudentAttendanceRecords($attendance, $students);

            // Sync lecturer attendance
            $this->syncLecturerAttendance($attendance, $schedule->lecturer_id);
        } catch (\Exception $e) {
            Log::error('Error syncing attendance for schedule: ' . $schedule->id, [
                'error' => $e->getMessage(),
                'schedule_id' => $schedule->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Generate semua student attendance records di awal sesuai struktur tabel
     */
    private function generateStudentAttendanceRecords(AttendanceSessions $attendance, $students): void
    {
        try {
            $chunkSize = 500; // Sesuaikan dengan kapasitas server
            $recordsCreated = 0;
            $recordsUpdated = 0;

            $students->chunk($chunkSize, function ($studentChunk) use ($attendance, &$recordsCreated, &$recordsUpdated) {
                // Get existing records untuk chunk ini
                $existingRecords = AttendanceRecords::where('attendance_session_id', $attendance->id)
                    ->whereIn('course_student_id', $studentChunk->pluck('id'))
                    ->get()
                    ->keyBy('course_student_id');

                $recordsToCreate = [];
                $recordsToUpdate = [];
                $now = now();

                foreach ($studentChunk as $courseStudent) {
                    if (!$courseStudent->student) {
                        Log::warning('Student not found for course_student', [
                            'course_student_id' => $courseStudent->id
                        ]);
                        continue;
                    }

                    $existingRecord = $existingRecords->get($courseStudent->id);

                    if (!$existingRecord) {
                        // Create new record
                        $recordsToCreate[] = [
                            'attendance_session_id' => $attendance->id,
                            'course_student_id' => $courseStudent->id,
                            'nim' => $courseStudent->student->nim,
                            'latitude' => null,
                            'longitude' => null,
                            'loc_name' => null,
                            'distance' => null,
                            'wifi_ssid' => null,
                            'device_info' => null,
                            'scanned_at' => null,
                            'method' => null,
                            'status' => 'absent',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $recordsCreated++;
                    } else {
                        // Check if update needed
                        $updates = [];

                        if ($existingRecord->nim !== $courseStudent->student->nim) {
                            $updates['nim'] = $courseStudent->student->nim;
                        }

                        if ($existingRecord->status === null) {
                            $updates['status'] = 'absent';
                        }

                        if (!empty($updates)) {
                            $recordsToUpdate[$existingRecord->id] = $updates;
                            $recordsUpdated++;
                        }
                    }
                }

                // Bulk insert
                if (!empty($recordsToCreate)) {
                    AttendanceRecords::insert($recordsToCreate);

                    Log::debug('Bulk created attendance records', [
                        'count' => count($recordsToCreate)
                    ]);
                }

                // Bulk update
                if (!empty($recordsToUpdate)) {
                    foreach ($recordsToUpdate as $recordId => $updates) {
                        AttendanceRecords::where('id', $recordId)->update($updates);
                    }

                    Log::debug('Bulk updated attendance records', [
                        'count' => count($recordsToUpdate)
                    ]);
                }
            });

            // Update total attendance count
            $totalRecords = AttendanceRecords::where('attendance_session_id', $attendance->id)->count();
            $attendance->update([
                'total_attendance' => $totalRecords
            ]);

            Log::info('Student attendance records generated', [
                'attendance_id' => $attendance->id,
                'total_students' => $students->count(),
                'records_created' => $recordsCreated,
                'records_updated' => $recordsUpdated,
                'total_attendance' => $totalRecords
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating student attendance records', [
                'error' => $e->getMessage(),
                'attendance_id' => $attendance->id,
                'student_count' => $students->count(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw untuk handling di level atas
        }
    }

    private function syncLecturerAttendance(AttendanceSessions $attendance, ?int $lecturerId): void
    {
        try {
            if (!$lecturerId) {
                return;
            }

            $courseLecturer = CourseLecturer::where([
                'lecturer_id' => $lecturerId,
                'course_id' => $attendance->course_id,
                'semester_id' => $attendance->semester_id,
            ])->first();

            if (!$courseLecturer) {
                Log::warning('CourseLecturer not found for lecturer attendance sync', [
                    'lecturer_id' => $lecturerId,
                    'course_id' => $attendance->course_id,
                ]);
                return;
            }

            LecturerAttendanceRecords::updateOrCreate(
                [
                    'attendance_session_id' => $attendance->id,
                    'course_lecturer_id' => $courseLecturer->id,
                ],
                [
                    'attendance_status' => 'scheduled',
                    'created_by' => auth()->id(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error syncing lecturer attendance', [
                'error' => $e->getMessage(),
                'attendance_id' => $attendance->id,
                'lecturer_id' => $lecturerId,
            ]);
        }
    }

    private function removeLecturerAttendance(int $teachingScheduleId, int $oldLecturerId): void
    {
        try {
            $attendance = AttendanceSessions::where('teaching_schedule_id', $teachingScheduleId)->first();
            if (!$attendance) {
                return;
            }

            $oldCourseLecturer = CourseLecturer::where([
                'lecturer_id' => $oldLecturerId,
                'course_id' => $attendance->course_id,
                'semester_id' => $attendance->semester_id,
            ])->first();

            if ($oldCourseLecturer) {
                LecturerAttendanceRecords::where([
                    'attendance_session_id' => $attendance->id,
                    'course_lecturer_id' => $oldCourseLecturer->id,
                ])->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error removing old lecturer attendance', [
                'error' => $e->getMessage(),
                'teaching_schedule_id' => $teachingScheduleId,
            ]);
        }
    }

    private function addLecturerIfNotExists(TeachingSchedule $schedule, int $lecturerId): void
    {
        $courseSchedule = $schedule->courseSchedule;
        if (!$courseSchedule) {
            return;
        }

        $courseLecturer = CourseLecturer::firstOrCreate([
            'course_id' => $courseSchedule->course_id,
            'lecturer_id' => $lecturerId,
            'semester_id' => $courseSchedule->semester_id,
        ]);

        CourseLecturerActivity::firstOrCreate([
            'course_lecturer_id' => $courseLecturer->id,
            'activity_id' => $schedule->activity_id,
        ]);
    }
}
