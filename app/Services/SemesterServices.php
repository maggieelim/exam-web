<?php
// app/Services/SemesterService.php
namespace App\Services;

use App\Models\Semester;
use Carbon\Carbon;

class SemesterService
{
  public function getActiveSemester()
  {
    $today = Carbon::today();
    return Semester::where('start_date', '<=', $today)
      ->where('end_date', '>=', $today)
      ->first();
  }

  public function getSemesterId($requestSemesterId = null)
  {
    if (!$requestSemesterId) {
      $activeSemester = $this->getActiveSemester();
      return $activeSemester ? $activeSemester->id : null;
    }

    return $requestSemesterId;
  }
}
