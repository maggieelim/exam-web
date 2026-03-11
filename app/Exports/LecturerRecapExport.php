<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LecturerRecapExport implements FromView, WithEvents
{
    protected $lecturers;
    protected $courses;
    protected $activities;
    protected $summary;

    public function __construct($lecturers, $courses, $activities, $summary)
    {
        $this->lecturers = $lecturers;
        $this->courses = $courses;
        $this->activities = $activities;
        $this->summary = $summary;
    }

    public function view(): View
    {
        return view('admin.courseRecap.export', [
            'lecturers' => $this->lecturers,
            'courses' => $this->courses,
            'activities' => $this->activities,
            'summary' => $this->summary,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->freezePane('B4');
            }
        ];
    }
}
