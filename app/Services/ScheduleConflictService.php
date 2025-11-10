<?php

namespace App\Services;

use App\Models\PemicuDetails;
use App\Models\PlenoDetails;
use App\Models\TeachingSchedule;
use App\Models\PracticumDetails;
use App\Models\SkillslabDetails;

class ScheduleConflictService
{
    /**
     * Check if a lecturer has schedule conflict for given time slot
     */
    public function hasScheduleConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        if (!$date || !$startTime || !$endTime) {
            return false;
        }
        $teachingConflict = $this->hasTeachingScheduleConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $practicumConflict = $this->hasPracticumConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $pemicuConflict = $this->hasPemicuConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $skillslabConflict = $this->hasSkillslabConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $plenoConflict = $this->hasPlenoConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId, $semesterId);

        return $teachingConflict || $practicumConflict || $skillslabConflict || $pemicuConflict || $plenoConflict; 
    }

    private function hasTeachingScheduleConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = TeachingSchedule::where('lecturer_id', $lecturerId)
            ->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
            ->where('scheduled_date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        return $query->exists();
    }

    private function hasPracticumConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = PracticumDetails::where('lecturer_id', $lecturerId)->whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->exists();
    }

    private function hasPemicuConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = PemicuDetails::where('lecturer_id', $lecturerId)->whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->exists();
    }

    private function hasSkillslabConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = SkillslabDetails::where('lecturer_id', $lecturerId)->whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->exists();
    }

    private function hasPlenoConflict($lecturerId, $date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = PlenoDetails::where('lecturer_id', $lecturerId)->whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->exists();
    }

    public function getConflictingLecturers($date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        if (!$date || !$startTime || !$endTime) {
            return collect();
        }

        $conflictingLecturers = collect();

        // Get conflicts from teaching_schedules
        $teachingConflicts = $this->getTeachingScheduleConflicts($date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $conflictingLecturers = $conflictingLecturers->merge($teachingConflicts);

        // Get conflicts from practicum_details
        $practicumConflicts = $this->getPracticumConflicts($date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $conflictingLecturers = $conflictingLecturers->merge($practicumConflicts);
     
        // Get conflicts from pemicu_details
        $pemicuConflicts = $this->getPemicuConflicts($date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $conflictingLecturers = $conflictingLecturers->merge($pemicuConflicts);

        // Get conflicts from skillslab_details
        $skillslabConflicts = $this->getSkillslabConflicts($date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $conflictingLecturers = $conflictingLecturers->merge($skillslabConflicts);
        
        // Get conflicts from pleno_details
        $plenoConflicts = $this->getPlenoConflicts($date, $startTime, $endTime, $excludeScheduleId, $semesterId);
        $conflictingLecturers = $conflictingLecturers->merge($plenoConflicts);

        return $conflictingLecturers->filter()->unique();
    }

    private function getTeachingScheduleConflicts($date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = TeachingSchedule::whereNotNull(['scheduled_date', 'start_time', 'end_time'])
            ->where('scheduled_date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
            });

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        return $query->pluck('lecturer_id')->filter();
    }

    private function getPracticumConflicts($date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = PracticumDetails::whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->pluck('lecturer_id')->filter();
    }

    private function getPemicuConflicts($date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = PemicuDetails::whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->pluck('lecturer_id')->filter();
    }

    private function getSkillslabConflicts($date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = SkillslabDetails::whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->pluck('lecturer_id')->filter();
    }

    private function getPlenoConflicts($date, $startTime, $endTime, $excludeScheduleId = null, $semesterId = null)
    {
        $query = PlenoDetails::whereHas('teachingSchedule', function ($q) use ($date, $startTime, $endTime) {
            $q->whereNotNull(['scheduled_date', 'start_time', 'end_time'])
                ->where('scheduled_date', $date)
                ->where(function ($innerQ) use ($startTime, $endTime) {
                    $innerQ->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
        });

        if ($excludeScheduleId) {
            $query->where('teaching_schedule_id', '!=', $excludeScheduleId);
        }

        if ($semesterId) {
            $query->whereHas('teachingSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->pluck('lecturer_id')->filter();
    }

    public function getAvailableLecturers($lecturers, $scheduleDate, $scheduleStartTime, $scheduleEndTime, $excludeScheduleId = null, $semesterId = null)
    {
        if (!$scheduleDate || !$scheduleStartTime || !$scheduleEndTime) {
            return $lecturers;
        }

        $conflictingLecturers = $this->getConflictingLecturers($scheduleDate, $scheduleStartTime, $scheduleEndTime, $excludeScheduleId, $semesterId);

        return $lecturers->filter(function ($lecturer) use ($conflictingLecturers, $excludeScheduleId, $scheduleDate, $scheduleStartTime, $scheduleEndTime, $semesterId) {
            // If this lecturer is already assigned to current schedule, keep them
            if ($excludeScheduleId) {
                $currentSchedule = TeachingSchedule::find($excludeScheduleId);
                if ($currentSchedule && $currentSchedule->lecturer_id == $lecturer->id) {
                    return true;
                }

                // Also check if lecturer is assigned in practicum_details for this schedule
                $currentPracticum = PracticumDetails::where('teaching_schedule_id', $excludeScheduleId)->where('lecturer_id', $lecturer->id)->exists();
                if ($currentPracticum) {
                    return true;
                }

                $currentPemicu = PemicuDetails::where('teaching_schedule_id', $excludeScheduleId)->where('lecturer_id', $lecturer->id)->exists();
                if ($currentPemicu) {
                    return true;
                }

                // Also check if lecturer is assigned in skillslab_details for this schedule
                $currentSkillslab = SkillslabDetails::where('teaching_schedule_id', $excludeScheduleId)->where('lecturer_id', $lecturer->id)->exists();
                if ($currentSkillslab) {
                    return true;
                }
               
                $currentPleno = PlenoDetails::where('teaching_schedule_id', $excludeScheduleId)->where('lecturer_id', $lecturer->id)->exists();
                if ($currentPleno) {
                    return true;
                }
            }

            return !$conflictingLecturers->contains($lecturer->id);
        });
    }

    /**
     * Check time overlap between two time slots
     */
    public function hasTimeOverlap($date1, $start1, $end1, $date2, $start2, $end2)
    {
        if ($date1 != $date2) {
            return false;
        }

        $startTime1 = strtotime($start1);
        $endTime1 = strtotime($end1);
        $startTime2 = strtotime($start2);
        $endTime2 = strtotime($end2);

        return $startTime1 < $endTime2 && $endTime1 > $startTime2;
    }
}
