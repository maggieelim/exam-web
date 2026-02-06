<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\HospitalRotation;
use App\Models\Logbook;
use App\Models\Student;
use App\Models\StudentKoas;
use Illuminate\Http\Request;

class StudentRotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $student = $user->student;

        $rotations = StudentKoas::where('student_id', $student->id)
            ->with([
                'hospitalRotation.hospital',
                'semester'
            ])
            ->withCount([
                'logbooks as total_kegiatan' => function ($query) {
                    $query->where('status', 'approved')
                        ->wherenot('activity_koas_id', 10);
                },
                'logbooks as izin_count' => function ($query) {
                    $query->where('status', 'approved')
                        ->where('activity_koas_id', 10);
                },
                'logbooks as validasi_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'logbooks as logbook_count'
            ])
            ->paginate(20);

        return view('pspd.students.index', compact('rotations'));
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
        $rotation = StudentKoas::with([
            'hospitalRotation.hospital',
            'hospitalRotation.clinicalRotation',
        ])->findOrFail($id);

        $activitySummary = Logbook::where('student_koas_id', $rotation->id)
            ->where('status', 'approved')
            ->groupBy('activity_koas_id')
            ->with('activityKoas')
            ->selectRaw('activity_koas_id, count(*) as count')
            ->get();

        return view('pspd.students.show', compact('rotation', 'activitySummary'));
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
