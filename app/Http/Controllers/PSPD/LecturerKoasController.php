<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\LecturerKoas;
use App\Services\SemesterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerKoasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {}

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
        $request->validate([
            'rotation'      => 'required|exists:hospital_rotations,id',
            'lecturers'     => 'required|array',
            'lecturers.*'   => 'exists:lecturers,id',
        ]);
        $rotationId = $request->rotation;
        $lecturerIds = $request->lecturers;

        DB::transaction(function () use ($rotationId, $lecturerIds) {
            LecturerKoas::where('hospital_rotation_id', $rotationId)
                ->whereNotIn('lecturer_id', $lecturerIds)->delete();

            foreach ($lecturerIds as $lecturerId) {
                LecturerKoas::firstOrCreate([
                    'hospital_rotation_id' => $rotationId,
                    'lecturer_id' => $lecturerId,
                ]);
            }
        });
        return redirect()
            ->back()->with('success', 'Dosen berhasil ditambahkan');
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
    public function destroy(string $id, $rotation) {}
}
