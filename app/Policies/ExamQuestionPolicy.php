<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ExamQuestion;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamQuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Tentukan siapa saja yang bisa create exam question
     */
    public function create(User $user)
    {
        return $user->role === 'admin' || $user->role === 'lecturer' || $user->role === 'koordinator';
    }

    /**
     * Tentukan siapa saja yang bisa update exam question
     */
    public function update(User $user, ExamQuestion $examQuestion)
    {
        // admin bisa update semua
        if ($user->role === 'admin') {
            return true;
        }

        // lecturer hanya bisa update soal ujian yang ada di course/blok yang dia ajar
        if ($user->role === 'koordinator' || $user->role === 'lecturer') {
            return $user->courses->contains($examQuestion->exam->course_id);
        }

        return false;
    }

    /**
     * Tentukan siapa saja yang bisa delete exam question
     */
    public function delete(User $user, ExamQuestion $examQuestion)
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'lecturer'  || $user->role === 'koordinator') {
            return $user->courses->contains($examQuestion->exam->course_id);
        }

        return false;
    }
}
