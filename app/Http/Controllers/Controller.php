<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getActiveSemester()
    {
        $today = Carbon::today();
        $active = Semester::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->with('academicYear')
            ->first();

        if (!$active) {
            $active = Semester::where('end_date', '<', $today)
                ->orderBy('end_date', 'desc')
                ->first();

            if (!$active) {
                $active = Semester::where('start_date', '>', $today)
                    ->orderBy('start_date', 'asc')
                    ->first();
            }
        }
        return $active;
    }
}
