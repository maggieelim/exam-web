<?php

namespace App\Services;

use App\Models\AttendanceSessions;
use App\Models\CourseLecturer;
use App\Models\CourseLecturerActivity;
use App\Models\LecturerAttendanceRecords;
use App\Models\TeachingSchedule;
use Illuminate\Support\Facades\Log;

class LecturerAttendanceService
{
    /**
     * Sync lecturer attendance for any activity type
     */
    public function syncLecturerAttendance($teachingScheduleId, $lecturerId, $courseId, $semesterId, $activityId)
    {
        try {
            // Cari attendance session berdasarkan teaching_schedule_id
            $attendance = AttendanceSessions::where('teaching_schedule_id', $teachingScheduleId)->first();

            if (!$attendance) {
                Log::warning('Attendance session not found', [
                    'teaching_schedule_id' => $teachingScheduleId,
                    'lecturer_id' => $lecturerId,
                    'activity_id' => $activityId
                ]);
                return false;
            }

            // Cari atau buat course_lecturer relationship
            $courseLecturer = CourseLecturer::firstOrCreate([
                'course_id' => $courseId,
                'lecturer_id' => $lecturerId,
                'semester_id' => $semesterId,
            ], [
                'created_by' => auth()->id(),
            ]);

            // Cari atau buat course_lecturer_activity relationship
            $courseLecturerActivity = CourseLecturerActivity::firstOrCreate([
                'course_lecturer_id' => $courseLecturer->id,
                'activity_id' => $activityId,
            ], [
                'created_by' => auth()->id(),
            ]);

            // Buat atau update lecturer attendance record
            LecturerAttendanceRecords::updateOrCreate([
                'attendance_session_id' => $attendance->id,
                'course_lecturer_id' => $courseLecturer->id,
            ], [
                'status' => 'pending',
                'updated_at' => now(),
            ]);

            Log::info('Lecturer attendance synced successfully', [
                'teaching_schedule_id' => $teachingScheduleId,
                'lecturer_id' => $lecturerId,
                'activity_id' => $activityId,
                'attendance_id' => $attendance->id,
                'course_lecturer_id' => $courseLecturer->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error syncing lecturer attendance', [
                'error' => $e->getMessage(),
                'teaching_schedule_id' => $teachingScheduleId,
                'lecturer_id' => $lecturerId,
                'activity_id' => $activityId
            ]);
            throw $e;
        }
    }

    /**
     * Remove lecturer attendance when assignment is removed
     */
    public function removeLecturerAttendance($teachingScheduleId, $lecturerId, $courseId, $semesterId)
    {
        try {
            $attendance = AttendanceSessions::where('teaching_schedule_id', $teachingScheduleId)->first();

            if (!$attendance) {
                return false;
            }

            $courseLecturer = CourseLecturer::where([
                'course_id' => $courseId,
                'lecturer_id' => $lecturerId,
                'semester_id' => $semesterId,
            ])->first();

            if ($courseLecturer) {
                $deleted = LecturerAttendanceRecords::where([
                    'attendance_session_id' => $attendance->id,
                    'course_lecturer_id' => $courseLecturer->id,
                ])->delete();

                return $deleted > 0;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error removing lecturer attendance', [
                'error' => $e->getMessage(),
                'teaching_schedule_id' => $teachingScheduleId,
                'lecturer_id' => $lecturerId
            ]);
            throw $e;
        }
    }

    /**
     * Bulk sync lecturer attendance for multiple assignments
     */
    public function bulkSyncLecturerAttendance(array $assignments, $courseId, $semesterId, $activityId)
    {
        $results = [];

        foreach ($assignments as $lecturerId => $scheduleAssignments) {
            foreach ($scheduleAssignments as $teachingScheduleId => $isAssigned) {
                if ($isAssigned) {
                    $result = $this->syncLecturerAttendance(
                        $teachingScheduleId,
                        $lecturerId,
                        $courseId,
                        $semesterId,
                        $activityId
                    );
                    $results[] = [
                        'teaching_schedule_id' => $teachingScheduleId,
                        'lecturer_id' => $lecturerId,
                        'success' => $result
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Get activity ID from teaching schedule
     */
    public function getActivityIdFromTeachingSchedule($teachingScheduleId)
    {
        $teachingSchedule = TeachingSchedule::find($teachingScheduleId);
        return $teachingSchedule ? $teachingSchedule->activity_id : null;
    }
}
