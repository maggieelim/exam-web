<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use App\Models\Lecturer;
use App\Models\CourseSchedule;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ScheduleDataService
{
    public function getScheduleData(string $slug, ?string $semesterId): object
    {
        $course = $this->getCourseWithSchedules($slug);
        $semester = $this->getSemesterWithAcademicYear($semesterId);
        $lecturers = Lecturer::with('user')->get();
        $courseSchedule = $this->getCourseSchedule($course->id, $semesterId);

        if (!$courseSchedule) {
            return $this->emptyScheduleData($course, $semester);
        }

        return (object) [
            'course' => $course,
            'semester' => $semester,
            'courseSchedule' => $courseSchedule,
            'teachingSchedules' => $this->getTeachingSchedules($courseSchedule->id),
            'lecturers' => $lecturers,
        ];
    }

    private function getCourseWithSchedules(string $slug): Course
    {
        return Course::with('schedules')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function getSemesterWithAcademicYear(string $semesterId): Semester
    {
        return Semester::with('academicYear')
            ->findOrFail($semesterId);
    }

    private function getCourseSchedule(int $courseId, string $semesterId): ?CourseSchedule
    {
        return CourseSchedule::with(['course', 'semester'])
            ->where('course_id', $courseId)
            ->where('semester_id', $semesterId)
            ->first();
    }

    private function getTeachingSchedules(int $courseScheduleId): Collection
    {
        return TeachingSchedule::with('activity', 'pemicuDetails')
            ->where('course_schedule_id', $courseScheduleId)
            ->orderBy('session_number')
            ->get()
            ->groupBy(fn($item) => $this->categorizeActivity($item))
            ->map(fn($group) => $group->sortBy('activity_id')->values());
    }

    private function categorizeActivity($item): string
    {
        $name = strtolower($item->activity->activity_name);

        return match (true) {
            Str::contains($name, ['ujian praktikum', 'praktikum']) => 'PRAKTIKUM',
            Str::contains($name, ['ujian skill lab', 'skill lab']) => 'SKILL LAB',
            default => strtoupper($item->activity->activity_name),
        };
    }

    private function emptyScheduleData(Course $course, Semester $semester): object
    {
        return (object) [
            'course' => $course,
            'semester' => $semester,
            'teachingSchedules' => collect(),
            'lecturers' => collect(),
            'availableLecturersPerSchedule' => [],
        ];
    }
}
