<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CourseParticipantsExport implements WithMultipleSheets
{
  protected $course;
  protected $semesterId;

  public function __construct($course, $semesterId)
  {
    $this->course = $course;
    $this->semesterId = $semesterId;
  }

  public function sheets(): array
  {
    return [
      new CourseStudentsSheet($this->course, $this->semesterId),
      new CourseLecturersSheet($this->course, $this->semesterId),
    ];
  }
}
