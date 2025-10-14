<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoursesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Course::query();

        // Filter semester
        if (!empty($this->filters['semester_id'])) {
            $query->where('semester', $this->filters['semester_id']);
        }

        // Filter name
        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Kode Blok',
            'Nama',
            'Semester',
            'Total Dosen',
            'Total Mahasiswa',
        ];
    }

    public function map($course): array
    {
        return [
            $course->kode_blok,
            $course->name,
            $course->semester,
            $course->lecturer_count ?? 0,
            $course->student_count ?? 0,
        ];
    }
}
