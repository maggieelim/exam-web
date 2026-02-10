<?php

namespace App\Services;

use App\Models\CourseSchedule;
use App\Models\CourseScheduleDetail;
use App\Models\TeachingSchedule;
use App\Models\Exam;
use App\Models\AttendanceSessions;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScheduleCreationService
{
    public function createOrUpdateSchedule(array $requestData)
    {
        $courseSchedule = $this->findOrCreateCourseSchedule($requestData);

        foreach ($requestData['activities'] as $activityId => $newCount) {
            // if ($newCount <= 0) {
            //     continue;
            // }

            $this->processActivity($courseSchedule, $activityId, $newCount);
        }

        return [
            'course_slug' => $courseSchedule->course->slug,
            'course_schedule_id' => $courseSchedule->id
        ];
    }

    private function findOrCreateCourseSchedule(array $requestData)
    {
        return CourseSchedule::firstOrCreate(
            [
                'course_id' => $requestData['course_id'],
                'semester_id' => $requestData['semester_id'],
            ],
            [
                'year_level' => $requestData['year_level'] ?? null,
                'created_by' => auth()->id(),
            ]
        );
    }

    private function processActivity(CourseSchedule $courseSchedule, $activityId, $newCount)
    {
        $activity = Activity::find($activityId);
        if (!$activity) {
            return;
        }

        $existingDetail = CourseScheduleDetail::where([
            'course_schedule_id' => $courseSchedule->id,
            'activity_id' => $activityId,
        ])->first();

        $oldCount = $existingDetail ? $existingDetail->total_sessions : 0;

        // Update atau buat detail
        $detail = $this->updateOrCreateDetail($courseSchedule, $activityId, $newCount);

        $this->syncTeachingSchedules($courseSchedule, $activity, $newCount, $oldCount);
    }

    private function updateOrCreateDetail(CourseSchedule $courseSchedule, $activityId, $newCount)
    {
        return CourseScheduleDetail::updateOrCreate(
            [
                'course_schedule_id' => $courseSchedule->id,
                'activity_id' => $activityId,
            ],
            ['total_sessions' => $newCount]
        );
    }

    private function syncTeachingSchedules(CourseSchedule $courseSchedule, Activity $activity, $newCount, $oldCount)
    {
        $multiplier = $this->getSessionMultiplier($activity);
        $actualNewCount = $newCount * $multiplier;
        $actualOldCount = $oldCount * $multiplier;

        if ($actualNewCount > $actualOldCount) {
            $this->createNewSessions($courseSchedule, $activity, $actualOldCount + 1, $actualNewCount);
        } elseif ($actualNewCount < $actualOldCount) {
            $this->removeExcessSessions($courseSchedule, $activity, $actualNewCount);
        }
    }

    private function getSessionMultiplier(Activity $activity): int
    {
        return Str::contains(strtolower($activity->activity_name), 'pemicu') ? 2 : 1;
    }

    private function createNewSessions(CourseSchedule $courseSchedule, Activity $activity, $startFrom, $endAt)
    {
        for ($i = $startFrom; $i <= $endAt; $i++) {
            if (strtolower($activity->code) === 'u') {
                $this->createExamSession($courseSchedule, $activity, $i);
            } else {
                $this->createRegularSession($courseSchedule, $activity, $i);
            }
        }
    }

    private function createExamSession(CourseSchedule $courseSchedule, Activity $activity, $sessionNumber)
    {
        // Create teaching schedule
        $teaching = TeachingSchedule::create([
            'course_schedule_id' => $courseSchedule->id,
            'course_id' => $courseSchedule->course_id,
            'semester_id' => $courseSchedule->semester_id,
            'activity_id' => $activity->id,
            'session_number' => $sessionNumber,
            'created_by' => auth()->id(),
        ]);

        $exam = Exam::create([
            'teaching_schedule_id' => $teaching->id,
            'course_id' => $courseSchedule->course_id,
            'semester_id' => $courseSchedule->semester_id,
            'title' => "UT {$sessionNumber}",
            'created_by' => auth()->id(),
        ]);

        $this->createAttendanceSession($teaching, $courseSchedule, $activity);
    }

    private function createRegularSession(CourseSchedule $courseSchedule, Activity $activity, $sessionNumber)
    {
        $pemicuKe = null;
        if ($activity->id === 5) {
            $pemicuNumber = ceil($sessionNumber / 2);
            $urutanSesi = $sessionNumber % 2 === 0 ? 2 : 1;
            $pemicuKe = intval($pemicuNumber . $urutanSesi);
        }

        $teaching = TeachingSchedule::create([
            'course_schedule_id' => $courseSchedule->id,
            'course_id' => $courseSchedule->course_id,
            'semester_id' => $courseSchedule->semester_id,
            'activity_id' => $activity->id,
            'session_number' => $sessionNumber,
            'pemicu_ke' => $pemicuKe,
            'created_by' => auth()->id(),
        ]);

        $this->createAttendanceSession($teaching, $courseSchedule, $activity);
    }

    private function createAttendanceSession(TeachingSchedule $teaching, CourseSchedule $courseSchedule, Activity $activity)
    {
        $teaching->refresh();

        AttendanceSessions::create([
            'absensi_code' => 'ABS-' . strtoupper(Str::random(8)),
            'semester_id' => $courseSchedule->semester_id,
            'course_id' => $courseSchedule->course_id,
            'teaching_schedule_id' => $teaching->id,
            'activity_id' => $activity->id,
            'created_by' => auth()->id(),
        ]);
    }

    private function removeExcessSessions(CourseSchedule $courseSchedule, Activity $activity, $keepUpToSession)
    {
        // Delete teaching schedules
        TeachingSchedule::where('course_schedule_id', $courseSchedule->id)
            ->where('activity_id', $activity->id)
            ->where('session_number', '>', $keepUpToSession)
            ->delete();

        // Delete associated exams if activity is exam
        if (strtolower($activity->code) === 'u') {
            $this->removeExcessExams($courseSchedule, $keepUpToSession);
        }
    }

    private function removeExcessExams(CourseSchedule $courseSchedule, $keepUpToSession)
    {
        Exam::where('course_id', $courseSchedule->course_id)
            ->where('semester_id', $courseSchedule->semester_id)
            ->where('title', 'like', 'UT%')
            ->whereRaw('CAST(SUBSTRING(title, 4) AS UNSIGNED) > ?', [$keepUpToSession])
            ->delete();
    }
}
