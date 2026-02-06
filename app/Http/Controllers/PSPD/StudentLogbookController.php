<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\ActivityKoas;
use App\Models\LecturerKoas;
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
            })->when(request()->filled('status'), function ($query) {
                $query->where('status', request('status'));
            })->when(request()->filled('name'), function ($query) {
                $query->whereHas('lecturer.user', function ($q) {
                    $q->where('name', 'like', '%' . request('name') . '%');
                });
            })->orderByRaw("CASE WHEN status = 'approved' THEN 1 ELSE 0 END")
            ->orderBy('date', 'desc')
            ->paginate(20);

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
        $user = Auth::id();
        $semesterId = SemesterService::active();
        $student = Student::where('user_id', $user)->firstOrFail();
        $rotation = StudentKoas::where([
            ['student_id', $student->id],
            ['semester_id', $semesterId->id],
            ['status', 'active'],
        ])->first();
        $lecturers = LecturerKoas::where('hospital_rotation_id', $rotation->hospital_rotation_id)->get();

        return view('pspd.logbook.student.create', compact('activity', 'lecturers', 'rotation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'      => ['required', 'date'],
            'start_time' => ['required', 'before:end_time'],
            'end_time' => ['required', 'after:start_time'],
            'rotation'      => ['required', 'exists:student_koas,id'],
            'activity'  => ['required', 'exists:activity_koas,id'],
            'desc'      => ['required', 'string', 'max:1000'],
            'lecturer'  => ['required', 'exists:lecturers,id'],
            'proof'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $filePath = null;

        if ($request->hasFile('proof')) {
            $filePath = $request->file('proof')->store(
                'logbook/proofs', // folder
                'public'          // disk → storage/app/public
            );
        }

        Logbook::create([
            'student_koas_id'   => $validated['rotation'],
            'lecturer_id'       => $validated['lecturer'],
            'activity_koas_id'  => $validated['activity'],
            'date'              => $validated['date'],
            'start_time'        => Carbon::parse($validated['start_time'])->format('H:i'),
            'end_time'          => Carbon::parse($validated['end_time'])->format('H:i'),
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
        $logbook = Logbook::findOrFail($id);
        return view('pspd.logbook.student.show', compact('logbook'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::id();
        $activity = ActivityKoas::get();
        $semesterId = SemesterService::active();
        $student = Student::where('user_id', $user)->firstOrFail();
        $rotation = StudentKoas::where([
            ['student_id', $student->id],
            ['semester_id', $semesterId->id],
            ['status', 'active'],
        ])->first();
        $logbook = Logbook::where('id', $id)->firstOrFail();
        $lecturers = LecturerKoas::where('hospital_rotation_id', $rotation->hospital_rotation_id)->get();

        return view('pspd.logbook.student.edit', compact('logbook', 'rotation', 'activity', 'lecturers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'date'      => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'  => ['required', 'date_format:H:i', 'after:start_time'],
            'rotation'  => ['required', 'exists:student_koas,id'],
            'activity'  => ['required', 'exists:activity_koas,id'],
            'desc'      => ['required', 'string', 'max:1000'],
            'lecturer'  => ['required', 'exists:lecturers,id'],
            'proof'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $logbook = Logbook::findOrFail($id);

        // handle upload file jika ada
        if ($request->hasFile('proof')) {
            $filePath = $request->file('proof')->store('logbook', 'public');
        } else {
            $filePath = $logbook->file_path;
        }

        $logbook->update([
            'lecturer_id'       => $validated['lecturer'],
            'activity_koas_id'  => $validated['activity'],
            'date'              => $validated['date'],
            'start_time'        => $validated['start_time'],
            'end_time'          => $validated['end_time'],
            'description'       => $validated['desc'],
            'file_path'         => $filePath,
        ]);

        return redirect()->back()->with('success', 'Logbook berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $logbook = Logbook::findOrFail($id);
        $logbook->delete();
        return redirect()->route('student-logbook.index')->with('success', 'Logbook berhasil dihapus');
    }
}
