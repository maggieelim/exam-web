<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\ActivityKoas;
use App\Models\HospitalRotation;
use App\Models\Lecturer;
use App\Models\Logbook;
use App\Models\Student;
use App\Models\StudentKoas;
use App\Services\SemesterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentLogbookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $semester = SemesterService::active();
        $user = Auth::id();
        $student = Student::where('user_id', $user)->firstOrFail();
        $logbooks = Logbook::with(['studentKoas', 'activityKoas'])
            ->whereHas('studentKoas', function ($query) use ($semester, $student) {
                $query->where('semester_id', $semester->id)
                    ->where('student_id', $student->id);
            })->paginate(20);
        $rotation = StudentKoas::where([
            ['student_id', $student->id],
            ['semester_id', $semester->id],
            ['status', 'active'],
        ])->first();
        return view('pspd.logbook.student.index', compact('logbooks', 'rotation'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activity = ActivityKoas::get();
        $lecturers = Lecturer::whereIn('type', ['pspd', 'both'])->get();
        $user = Auth::id();
        $semesterId = SemesterService::active();
        $student = Student::where('user_id', $user)->firstOrFail();
        $rotation = StudentKoas::where([
            ['student_id', $student->id],
            ['semester_id', $semesterId->id],
            ['status', 'active'],
        ])->first();

        return view('pspd.logbook.student.create', compact('activity', 'lecturers', 'rotation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'      => ['required', 'date'],
            'rotation'      => ['required', 'exists:student_koas,id'],
            'activity'  => ['required', 'exists:activity_koas,id'],
            'desc'      => ['required', 'string', 'max:1000'],
            'lecturer'  => ['required', 'exists:lecturers,id'],
            'proof'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $filePath = null;

        if ($request->hasFile('proof')) {
            $filePath = $request->file('proof')
                ->store('logbook_proofs', 'public');
        }

        Logbook::create([
            'student_koas_id'   => $validated['rotation'],
            'lecturer_id'       => $validated['lecturer'],
            'activity_koas_id'  => $validated['activity'],
            'date'              => $validated['date'],
            'description'       => $validated['desc'],
            'file_path'         => $filePath,
            'status'            => 'pending',
        ]);
        return redirect()
            ->route('student-logbook.index')
            ->with('success', 'Logbook berhasil dibuat');
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
