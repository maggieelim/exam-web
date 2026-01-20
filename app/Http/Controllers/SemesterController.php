<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\SemesterService;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class SemesterController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $agent = new Agent();
        $activeSemester = SemesterService::active();
        $sort = $request->get('sort', 'start_date'); // default sort
        $dir = $request->get('dir', 'desc');

        $query = Semester::with('academicYear');
        switch ($sort) {
            case 'name':
                $query->orderBy('semester_name', $dir);
                break;

            case 'academic_year':
                $query->join('academic_years', 'semesters.academic_year_id', '=', 'academic_years.id')
                    ->orderBy('academic_years.year_name', $dir)
                    ->select('semesters.*');
                break;
            default:
                $query->orderBy('start_date', $dir);
                break;
        }
        $semesters = $query->paginate(20)->appends($request->query());

        if ($agent->isMobile()) {
            return view('admin.semester.index_mobile', compact('semesters', 'activeSemester'));
        }
        return view('admin.semester.index', compact('sort', 'dir', 'semesters', 'activeSemester'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeSemester = SemesterService::active();
        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->paginate(15);
        $currentYear = date('Y');
        $academicYears = [];

        $existingYears = AcademicYear::pluck('year_name')->toArray();
        for ($i = $currentYear; $i <= $currentYear + 3; $i++) {
            $yearName = "{$i}/" . ($i + 1);

            // Hanya tambahkan jika belum ada di database
            if (!in_array($yearName, $existingYears)) {
                $academicYears[] = $yearName;
            }
        }
        return view('admin.semester.create', compact('semesters', 'activeSemester', 'academicYears'));
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

        if ($request->odd_end >= $request->even_start) {
            return back()
                ->withInput()
                ->withErrors(['even_start' => 'Tanggal mulai semester genap tidak boleh sebelum atau sama dengan akhir semester ganjil.']);
        }
        if ($request->even_end >= $request->end_date) {
            return back()
                ->withInput()
                ->withErrors(['even_start' => 'Tanggal berakhir semester genap tidak boleh melebihi atau sama dengan akhir tahun akademik.']);
        }

        $overlap = Semester::where(function ($query) use ($request) {
            $query->whereBetween('start_date', [$request->odd_start, $request->even_end])->orWhereBetween('end_date', [$request->odd_start, $request->even_end]);
        })->exists();

        // if ($overlap) {
        //     return back()
        //         ->withInput()
        //         ->withErrors(['start_date' => 'Rentang tanggal semester yang diajukan tumpang tindih dengan semester lain yang sudah ada.']);
        // }

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

        return redirect()->route(session('context') . '.admin.semester.index')->with('success', 'Tahun Akademik dan Semester berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $semester = Semester::with('academicYear')->where('id', $id)->firstOrFail();
        return view('admin.semester.show', compact('semester'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $activeSemester = SemesterService::active();
        $semester = Semester::with('academicYear')->findOrFail($id);
        $semesters = SemesterService::list();
        $currentYear = date('Y');
        $academicYears = [];

        for ($i = $currentYear - 2; $i <= $currentYear + 3; $i++) {
            $academicYears[] = "{$i}/" . ($i + 1);
        }
        return view('admin.semester.edit', compact('semester', 'semesters', 'activeSemester', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $semester = Semester::where('id', $id)->first();

        $request->validate([
            'year_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'semester_start' => 'required|date',
            'semester_end' => 'required|date|after:semester_start',
        ]);

        if ($request->start_date >= $request->semester_start) {
            return back()
                ->withInput()
                ->withErrors(['Tanggal mulai semester tidak boleh sebelum dengan Tahun Akademik.']);
        }

        if ($request->semester_end >= $request->end_date) {
            return back()
                ->withInput()
                ->withErrors(['even_start' => 'Tanggal berakhir semester tidak boleh melebihi atau sama dengan akhir tahun akademik.']);
        }
        Semester::where('id', $semester->id)->update([
            'start_date' => $request->semester_start,
            'end_date' => $request->semester_end,
        ]);

        AcademicYear::where('id', $semester->academic_year_id)->update([
            'year_name' => $request->year_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        return redirect()->route(session('context') . '.admin.semester.edit', $id)->with('success', 'Tahun Akademik dan Semester berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $semester = Semester::findorfail($id);
        $academicYear = AcademicYear::findOrFail($semester->academic_year_id);
        $semester->delete();
        $academicYear->delete();
        return redirect()->route(session('context') . '.admin.semester.index')->with('success', 'Semester Berhasil Dihapus');
    }
}
