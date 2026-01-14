<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\StudentKoas;
use App\Services\SemesterService;
use Illuminate\Http\Request;

class StudentKoasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activeSemester = SemesterService::active();
        $semesterId = $request->get('semester_id') ?? optional($activeSemester)->id;
        $semesters = SemesterService::list();

        $query = StudentKoas::with(['hospitalRotation.hospital', 'hospitalRotation.clinicalRotation', 'student.user', 'semester']);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $query
            ->when($request->filled('name'), function ($q) use ($request) {
                $q->whereHas('student', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->name . '%');
                });
            })
            ->when($request->filled('kepaniteraan'), function ($q) use ($request) {
                $q->whereHas('hospitalRotation.hospital', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->kepaniteraan . '%')
                        ->orWhere('code', 'like', '%' . $request->kepaniteraan . '%');
                });
            });

        $students = $query->paginate(20);

        return view('pspd.koas.index', compact(
            'students',
            'semesterId',
            'semesters',
            'activeSemester'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
