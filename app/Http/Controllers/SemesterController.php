<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private function getActiveSemester()
    {
        $today = Carbon::today();
        return Semester::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }

    public function index()
    {
        $activeSemester = $this->getActiveSemester();
        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('admin.semester.index', compact(
            'semesters',
            'activeSemester'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeSemester = $this->getActiveSemester();
        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')
            ->paginate(15);
        $currentYear = date('Y');
        $academicYears = [];

        for ($i = $currentYear; $i <= $currentYear + 3; $i++) {
            $academicYears[] = "{$i}/" . ($i + 1);
        }
        return view('admin.semester.create', compact(
            'semesters',
            'activeSemester',
            'academicYears'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'odd_start' => 'required|date',
            'odd_end' => 'required|date|after:odd_start',
            'even_start' => 'required|date',
            'even_end' => 'required|date|after:even_start',
        ]);

        // Simpan Tahun Akademik
        $academicYear = AcademicYear::create([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        // Buat Semester Ganjil & Genap berdasarkan input
        Semester::insert([
            [
                'academic_year_id' => $academicYear->id,
                'semester_name' => 'Ganjil',
                'start_date' => $request->odd_start,
                'end_date' => $request->odd_end,
            ],
            [
                'academic_year_id' => $academicYear->id,
                'semester_name' => 'Genap',
                'start_date' => $request->even_start,
                'end_date' => $request->even_end,
            ],
        ]);

        return redirect()->route('admin.semester.index')
            ->with('success', 'Tahun Akademik dan Semester berhasil dibuat.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $semester = Semester::with('academicYear')->where('id', $id)
            ->firstOrFail();
        return view('admin.semester.show', compact(
            'semester',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $activeSemester = $this->getActiveSemester();
        $semester = Semester::with('academicYear')->findOrFail($id);
        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();
        $currentYear = date('Y');
        $academicYears = [];

        for ($i = $currentYear - 2; $i <= $currentYear + 3; $i++) {
            $academicYears[] = "{$i}/" . ($i + 1);
        }
        return view('admin.semester.edit', compact(
            'semester',
            'semesters',
            'activeSemester',
            'academicYears'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $semester = Semester::where('id', $id)->first();

        $request->validate([
            'year_name'      => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'semester_start' => 'required|date',
            'semester_end' => 'required|date|after:semester_start',
        ]);

        Semester::where('id', $semester->id)->update([

            'start_date' => $request->semester_start,
            'end_date' => $request->semester_end,

        ]);

        AcademicYear::where('id', $semester->academic_year_id)->update([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        return redirect()->route('admin.semester.edit', $id)
            ->with('success', 'Tahun Akademik dan Semester berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
