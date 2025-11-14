<?php

namespace App\Services;

use App\Models\CourseCoordinator;
use Illuminate\Support\Collection;

class LecturerSortService
{
    public function sort(Collection $lecturers, $courseId, $semesterId)
    {
        $coordinators = CourseCoordinator::where('course_id', $courseId)
            ->where('semester_id', $semesterId)
            ->pluck('role', 'lecturer_id');

        return $lecturers->sort(function ($a, $b) use ($coordinators) {

            $priorityA = $this->priority($a->lecturer_id, $coordinators);
            $priorityB = $this->priority($b->lecturer_id, $coordinators);

            // Jika prioritas berbeda
            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }

            // Jika sama → urutkan alfabet nama
            return strcmp(
                $a->lecturer->user->name,
                $b->lecturer->user->name
            );
        })->values(); // reset keys
    }

    private function priority($lecturerId, $coordinators)
    {
        if (!isset($coordinators[$lecturerId])) {
            return 3; // Prioritas default
        }

        return $coordinators[$lecturerId] === 'koordinator' ? 1 : 2;
    }
}
