<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Exam;

class ExamPolicy
{
    /**
     * If user is admin, allow everything.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Anyone with role ['lecturer', 'koordinator'] may attempt to create.
     * (Detailed check whether the lecturer teaches the selected course
     * will be done in controller because exam is not created yet.)
     */
    public function create(User $user)
    {
        return $user->hasRole(['lecturer', 'koordinator']);
    }

    /**
     * Update only if lecturer is assigned to the exam's course.
     */
    public function update(User $user, Exam $exam)
    {
        if ($user->hasRole(['lecturer', 'koordinator'])) {
            // pastikan relasi course->lecturers siap (eager loaded atau akses lazy)
            return $exam->course->lecturers->contains('id', $user->id);
        }
        return false;
    }

    /**
     * Delete same as update (admin allowed by before()).
     */
    public function delete(User $user, Exam $exam)
    {
        return $this->update($user, $exam);
    }
}
