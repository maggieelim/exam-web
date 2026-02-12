<?php

namespace App\Services;

use App\Models\TeachingSchedule;
use App\Models\PracticumDetails;
use App\Models\PemicuDetails;
use App\Models\SkillslabDetails;
use App\Models\PlenoDetails;
use Illuminate\Support\Collection;

class ScheduleConflictService
{
    /**
     * ============================
     * PUBLIC API
     * ============================
     */

    public function hasScheduleConflict(
        int $lecturerId,
        ?string $date,
        ?string $startTime,
        ?string $endTime,
        ?int $excludeScheduleId = null,
        ?int $semesterId = null
    ): bool {
        if (!$date || !$startTime || !$endTime) {
            return false;
        }

        // Teaching Schedule langsung
        if ($this->hasTeachingConflict(
            $lecturerId,
            $date,
            $startTime,
            $endTime,
            $excludeScheduleId,
            $semesterId
        )) {
            return true;
        }

        // Detail schedules (Practicum, Pemicu, Skillslab, Pleno)
        foreach ($this->detailModels() as $model) {
            if ($this->hasDetailConflict(
                $model,
                $lecturerId,
                $date,
                $startTime,
                $endTime,
                $excludeScheduleId,
                $semesterId
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Digunakan untuk PEMICU view (bulk check)
     */
    public function getLecturerConflicts(
        int $lecturerId,
        string $date,
        int $semesterId
    ): Collection {
        return TeachingSchedule::where('scheduled_date', $date)
            ->where('semester_id', $semesterId)
            ->whereNotNull(['start_time', 'end_time'])
            ->where(function ($q) use ($lecturerId) {
                $q->where('lecturer_id', $lecturerId)
                    ->orWhereHas('practicumDetails', fn($q) => $q->where('lecturer_id', $lecturerId))
                    ->orWhereHas('pemicuDetails', fn($q) => $q->where('lecturer_id', $lecturerId)->whereNotNull('kelompok_num'))
                    ->orWhereHas('skillslabDetails', fn($q) => $q->where('lecturer_id', $lecturerId))
                    ->orWhereHas('plenoDetails', fn($q) => $q->where('lecturer_id', $lecturerId));
            })
            ->get(['id', 'start_time', 'end_time']);
    }

    /**
     * Ambil SEMUA lecturer yang bentrok
     */
    public function getConflictingLecturers(
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null,
        ?int $semesterId = null
    ): Collection {
        if (!$date || !$startTime || !$endTime) {
            return collect();
        }

        $conflicts = collect();

        // Teaching Schedule
        $conflicts = $conflicts->merge(
            $this->getTeachingConflicts($date, $startTime, $endTime, $excludeScheduleId, $semesterId)
        );

        // Detail tables
        foreach ($this->detailModels() as $model) {
            $conflicts = $conflicts->merge(
                $this->getDetailConflicts($model, $date, $startTime, $endTime, $excludeScheduleId, $semesterId)
            );
        }

        return $conflicts->unique()->values();
    }

    /**
     * Filter lecturer yang tersedia
     */
    public function getAvailableLecturers(
        Collection $lecturers,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null,
        ?int $semesterId = null
    ): Collection {
        $conflictingIds = $this
            ->getConflictingLecturers($date, $startTime, $endTime, $excludeScheduleId, $semesterId)
            ->flip();

        return $lecturers->filter(fn($l) => !isset($conflictingIds[$l->id]));
    }

    /**
     * ============================
     * INTERNAL HELPERS
     * ============================
     */

    private function hasTeachingConflict(
        int $lecturerId,
        string $date,
        string $start,
        string $end,
        ?int $excludeId,
        ?int $semesterId
    ): bool {
        return TeachingSchedule::where('lecturer_id', $lecturerId)
            ->where('scheduled_date', $date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->exists();
    }

    private function hasDetailConflict(
        string $model,
        int $lecturerId,
        string $date,
        string $start,
        string $end,
        ?int $excludeId,
        ?int $semesterId
    ): bool {
        $query = $model::where('lecturer_id', $lecturerId)
            ->whereHas('teachingSchedule', function ($q) use ($date, $start, $end, $semesterId) {
                $q->where('scheduled_date', $date)
                    ->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);

                if ($semesterId) {
                    $q->where('semester_id', $semesterId);
                }
            })
            ->when($excludeId, fn($q) => $q->where('teaching_schedule_id', '!=', $excludeId));

        // ?? KHUSUS Pemicu DETAILS - harus memiliki kelompok_num
        if ($model === PemicuDetails::class) {
            $query->whereNotNull('kelompok_num');
        }

        return $query->exists();
    }

    private function getTeachingConflicts(
        string $date,
        string $start,
        string $end,
        ?int $excludeId,
        ?int $semesterId
    ): Collection {
        return TeachingSchedule::where('scheduled_date', $date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
            ->pluck('lecturer_id')
            ->filter()
            ->unique()
            ->values();
    }

    private function getDetailConflicts(
        string $model,
        string $date,
        string $start,
        string $end,
        ?int $excludeId,
        ?int $semesterId
    ): Collection {
        $query = $model::whereHas('teachingSchedule', function ($q) use ($date, $start, $end, $semesterId) {
            $q->where('scheduled_date', $date)
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start);

            if ($semesterId) {
                $q->where('semester_id', $semesterId);
            }
        });

        // ?? KHUSUS Pemicu DETAILS - harus memiliki kelompok_num
        if ($model === PemicuDetails::class) {
            $query->whereNotNull('kelompok_num');
        }

        return $query
            ->when($excludeId, fn($q) => $q->where('teaching_schedule_id', '!=', $excludeId))
            ->pluck('lecturer_id')
            ->filter()
            ->unique()
            ->values();
    }

    private function detailModels(): array
    {
        return [
            PracticumDetails::class,
            PemicuDetails::class,
            SkillslabDetails::class,
            PlenoDetails::class,
        ];
    }
}
