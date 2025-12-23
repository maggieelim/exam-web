<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCoordinator;
use App\Models\Lecturer;
use App\Models\TeachingSchedule;
use App\Models\User;
use App\Services\SemesterService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        $user = Auth::user();
        $totalLecturers = User::role('lecturer')->count();
        $totalAdmins = User::role('admin')->count();
        $totalStudents = User::role('student')->count();
        $activeSemester = SemesterService::active();
        $totalCourses = Course::whereIn('semester', [$activeSemester->semester_name, 'Ganjil/Genap'])->count();
        $semesterStart = Carbon::parse($activeSemester->start_date)->format('d M Y');
        $semesterEnd = Carbon::parse($activeSemester->end_date)->format('d M Y');

        $lecturerId = Lecturer::where('user_id', $user->id)->value('id');
        $koordinatorCourses = CourseCoordinator::where('lecturer_id', $lecturerId)->get();
        $koordinatorCourseIds = $koordinatorCourses->pluck('course_id');
        // Controller
        $summary = TeachingSchedule::with('course', 'activity')
            ->whereIn('course_id', $koordinatorCourseIds)
            ->get()
            ->groupBy('course_id')
            ->map(function ($schedules, $courseId) {
                $course = $schedules->first()->course;

                $activities = $schedules->groupBy('activity_id')
                    ->map(function ($items, $activityId) {
                        return [
                            'activity' => $items->first()->activity,
                            'count' => $items->count()
                        ];
                    });

                return [
                    'course' => $course,
                    'activities' => $activities,
                    'total_schedules' => $schedules->count(),
                    'scheduled_count' => $schedules->whereNotNull('scheduled_date')->count(),
                    'unscheduled_count' => $schedules->whereNull('scheduled_date')->count()
                ];
            });

        return view('dashboard', compact(
            'totalAdmins',
            'totalLecturers',
            'totalStudents',
            'totalCourses',
            'activeSemester',
            'semesterStart',
            'semesterEnd',
            'koordinatorCourses',
            'summary',
        ));
    }
}
