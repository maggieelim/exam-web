<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\ClinicalRotation;
use App\Models\Hospital;
use App\Models\HospitalRotation;
use App\Models\StudentKoas;
use App\Services\SemesterService;
use Illuminate\Http\Request;

class HospitalRotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activeSemester = SemesterService::active();
        $semesterId = $request->get('semester_id') ?? optional($activeSemester)->id;
        $semesters = SemesterService::list();
        $query = HospitalRotation::with('hospital', 'clinicalRotation', 'semester')->withCount(['studentKoas as total_active_koas' => function ($q) {
            $q->where('status', 'active');
        }]);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }
        $query->when($request->filled('hospital_name'), function ($q) use ($request) {
            $q->whereHas('hospital', function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->hospital_name . '%')
                    ->orWhere('code', 'like', '%' . $request->hospital_name . '%');
            });
        })->when($request->filled('stase_name'), function ($q) use ($request) {
            $q->whereHas('clinicalRotation', function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->stase_name . '%')
                    ->orWhere('code', 'like', '%' . $request->stase_name . '%');
            });
        });

        $rotations = $query->orderBy('hospital_id')->paginate(20);
        return view('pspd.kepaniteraan.index', compact('rotations', 'semesterId', 'semesters', 'activeSemester'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $semesters = SemesterService::list();
        $activeSemester = SemesterService::active();
        $semesterId = optional($activeSemester)->id;
        $hospitals = Hospital::get();
        $stases = ClinicalRotation::get();
        return view('pspd.kepaniteraan.create', compact('semesters', 'activeSemester', 'hospitals', 'stases', 'semesterId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital' => ['required', 'string'],
            'stase' => ['required', 'string'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $hospital = Hospital::where('name', $request->hospital)->first();
        $stase = ClinicalRotation::where('name', $request->stase)->first();
        if (!$hospital) {
            return back()->withErrors(['hospital' => 'Rumah Sakit Tidak Ditemukan'])
                ->withInput();
        }
        if (!$stase) {
            return back()->withErrors(['stase' => 'Stase Sakit Tidak Ditemukan'])
                ->withInput();
        }
        HospitalRotation::create([
            'hospital_id' => $hospital->id,
            'clinical_rotation_id' => $stase->id,
            'semester_id' => $request->semester_id,
            'end_date' => $request->end_date,
            'start_date' => $request->start_date,
        ]);

        return redirect()->route('kepaniteraan.index')->with('success', 'Kepaniteraan Berhasil Dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rotation = HospitalRotation::findOrFail($id);
        $students = StudentKoas::with('student.user')->where('hospital_rotation_id', $id)->paginate(20);

        return view('pspd.kepaniteraan.show', compact('rotation', 'students'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rotation = HospitalRotation::findOrFail($id);
        $semesters = SemesterService::list();
        $activeSemester = SemesterService::active();
        $semesterId = optional($activeSemester)->id;
        $hospitals = Hospital::get();
        $stases = ClinicalRotation::get();
        return view('pspd.kepaniteraan.edit', compact('rotation', 'semesters', 'activeSemester', 'semesterId', 'hospitals', 'stases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rotation = HospitalRotation::findOrFail($id);
        $request->validate([
            'hospital' => ['required', 'string'],
            'stase' => ['required', 'string'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $hospital = Hospital::where('name', $request->hospital)->first();
        $stase = ClinicalRotation::where('name', $request->stase)->first();
        if (!$hospital) {
            return back()->withErrors(['hospital' => 'Rumah Sakit Tidak Ditemukan'])
                ->withInput();
        }
        if (!$stase) {
            return back()->withErrors(['stase' => 'Stase Sakit Tidak Ditemukan'])
                ->withInput();
        }
        $rotation->update([
            'hospital_id' => $hospital->id,
            'clinical_rotation_id' => $stase->id,
            'semester_id' => $request->semester_id,
            'end_date' => $request->end_date,
            'start_date' => $request->start_date,
        ]);

        return redirect()->route('kepaniteraan.index')->with('success', 'Kepaniteraan Berhasil Diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rotation = HospitalRotation::findOrFail($id);
        $rotation->delete();
        return redirect()->route('kepaniteraan.index')->with('success', 'Kepaniteraan Berhasil Dihapus');
    }
}
