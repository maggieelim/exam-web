<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\CourseStudent;
use App\Models\HospitalRotation;
use App\Models\Student;
use App\Models\StudentKoas;
use App\Services\SemesterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
    public function create($rotationId)
    {
        $rotation = HospitalRotation::findOrFail($rotationId);
        return view('pspd.kepaniteraan.assign', compact('rotation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rotation' => 'required|exists:hospital_rotations,id',
            'semester' => 'required|exists:semesters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $rotationId = $request->rotation;
        $added = [];
        $notFound = [];
        $exists = [];

        if ($request->filled('nim')) {
            $nims = preg_split('/\r\n|\r|\n/', trim($request->nim));

            foreach ($nims as $nim) {
                $nim = trim($nim);
                if (!$nim) continue;

                $student = Student::where('nim', $nim)
                    ->where('type', 'pspd')
                    ->first();

                if (!$student) {
                    $notFound[] = $nim;
                    continue;
                }

                $existing = StudentKoas::withTrashed()
                    ->where('hospital_rotation_id', $rotationId)
                    ->where('student_id', $student->id)
                    ->where('semester_id', $request->semester)
                    ->first();
                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                        $existing->update([
                            'start_date' => $request->start_date,
                            'end_date'   => $request->end_date,
                            'status'     => 'active',
                        ]);
                        $added[] = "$nim (dipulihkan)";
                    } else {
                        $exists[] = $nim;
                    }
                } else {
                    StudentKoas::create([
                        'student_id'            => $student->id,
                        'hospital_rotation_id'  => $rotationId,
                        'semester_id'           => $request->semester,
                        'status'                => 'active',
                        'start_date'            => $request->start_date,
                        'end_date'              => $request->end_date,
                    ]);
                    $added[] = $nim;
                }
            }
        } elseif ($request->hasFile('excel')) {

            $rows = Excel::toArray([], $request->file('excel'))[0];

            foreach ($rows as $index => $row) {
                if ($index === 0) continue; // skip header

                $nim = trim($row[0] ?? '');
                if (!$nim) continue;

                $student = Student::where('nim', $nim)
                    ->where('type', 'pspd')
                    ->first();

                if (!$student) {
                    $notFound[] = $nim;
                    continue;
                }

                $existsKoas = StudentKoas::where(
                    [
                        'hospital_rotation_id' => $rotationId,
                        'student_id'           => $student->id,
                        'semester_id'          => $request->semester,
                    ]
                )->exists();

                if ($existsKoas) {
                    $exists[] = $nim;
                    continue;
                }

                StudentKoas::create([
                    'student_id'            => $student->id,
                    'hospital_rotation_id'  => $rotationId,
                    'semester_id'           => $request->semester,
                    'status'                => 'active',
                    'start_date'            => $request->start_date,
                    'end_date'              => $request->end_date,
                ]);

                $added[] = $nim;
            }
        } else {
            return back()->withErrors(['error' => 'Tidak ada data yang dikirim.']);
        }

        return redirect()
            ->route('kepaniteraan.index')
            ->with([
                'success'    => $added ? 'Berhasil ditambahkan: ' . implode(', ', $added) : null,
                'warning'    => $exists ? 'Sudah terdaftar: ' . implode(', ', $exists) : null,
                'error'      => $notFound ? 'NIM tidak ditemukan: ' . implode(', ', $notFound) : null,
            ]);
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
    public function destroy(string $id, $rotation)
    {
        $exists = StudentKoas::where('id', $id)->where('hospital_rotation_id', $rotation)->exists();
        if (!$exists) {
            return back()->with('error', 'Mahasiswa tidak ditemukan di kepaniteraan ini');
        }

        StudentKoas::where('id', $id)->where('hospital_rotation_id', $rotation)->delete();
        return back()->with('success', 'Mahasiswa berhasil dihapus dari Kepaniteraan');
    }
}
