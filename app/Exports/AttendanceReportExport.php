<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceReportExport implements WithMultipleSheets
{
    protected $studentRecords;
    protected $lecturerRecords;
    protected $activityName;
    protected $scheduleTime;

    public function __construct($studentRecords, $lecturerRecords, $activityName, $scheduleTime)
    {
        $this->studentRecords = $studentRecords;
        $this->lecturerRecords = $lecturerRecords;
        $this->activityName = $activityName;
        $this->scheduleTime = $scheduleTime;
    }

    public function sheets(): array
    {
        return [
            new StudentAttendanceSheet($this->studentRecords, $this->activityName, $this->scheduleTime),
            new LecturerAttendanceSheet($this->lecturerRecords, $this->activityName, $this->scheduleTime),
        ];
    }
}
