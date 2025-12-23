<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Support\Facades\Cache;

class SemesterService
{
    public static function list()
    {
        return Cache::remember('semesters_list', 3600, function () {
            return Semester::with('academicYear')
                ->orderBy('start_date', 'desc')
                ->get();
        });
    }

    public static function active()
    {
        return Cache::remember('active_semester', 3600, function () {
            return Semester::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();
        });
    }
}
