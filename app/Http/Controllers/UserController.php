<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\CourseLecturer;
use App\Models\CourseStudent;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentKoas;
use App\Models\User;
use App\Services\SemesterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    public function indexAdmin(Request $request, $type = null)
    {
        $agent = new Agent();
        $lastType = session('last_user_type');

        if ($lastType !== $type) {
            session()->forget('user_filter');
        }

        session(['last_user_type' => $type]);

        // Jika request mengandung filter baru, simpan ke session
        if ($request->all()) {
            session(['user_filter' => $request->all()]);
        } elseif (session('user_filter')) {
            $request->merge(session('user_filter'));
        }

        // === QUERY DASAR ===
        $query = User::with('roles');
        $today = Carbon::today();
        $semesterId = $request->get('semester_id');

        $activeSemester = SemesterService::active();
        $semesters = SemesterService::list();

        // === ROLE FILTER ===
        if ($type === 'student') {
            $query->role('student')
                ->with('student')
                ->whereHas('student', function ($q) {
                    $q->where('type', session('context'));
                });
        } elseif ($type === 'lecturer') {
            $query->role('lecturer')->with('lecturer');
        } elseif ($type === 'admin') {
            $query->role('admin');
        }

        // === FILTER TAMBAHAN ===
        if ($semesterId && $type === 'student') {
            $query->whereHas('student.courseStudents', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->filled('nim')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('nim', 'like', "%{$request->nim}%");
            });
        }

        if ($request->filled('nidn')) {
            $query->whereHas('lecturer', function ($q) use ($request) {
                $q->where('nidn', 'like', "%{$request->nidn}%");
            });
        }

        // === SORTING ===
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        if ($sort === 'nim') {
            $query->orderBy(Student::select('nim')->whereColumn('students.user_id', 'users.id'), $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $users = $query->paginate(30)->appends($request->all());

        if ($agent->isMobile()) {
            return view('admin.users.index_mobile', compact('users', 'type', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
        }
        return view('admin.users.index', compact('users', 'type', 'sort', 'dir', 'semesters', 'semesterId', 'activeSemester'));
    }

    public function create($type)
    {
        $session = session('context');
        if (!in_array($type, ['student', 'lecturer', 'admin'])) {
            abort(404);
        }
        return view('admin.users.create', compact('type', 'session'));
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required',
            'nim' => $type === 'student' ? 'required|string|unique:students,nim' : 'nullable',
            'nidn' => $type === 'lecturer' ? 'string|unique:lecturers,nidn' : 'nullable',
            'strata' => $type === 'lecturer' ? 'required' : 'nullable',
            'gelar' => $type === 'lecturer' ? 'nullable|string|max:50' : 'nullable',
            'tipe_dosen' => $type === 'lecturer' ? 'required|in:Asdos,CDT,DT,DTT' : 'nullable',
            'min_sks' => $type === 'lecturer' ? 'nullable|integer|min:0' : 'nullable',
            'max_sks' => $type === 'lecturer' ? 'nullable|integer|min:0' : 'nullable',
        ]);

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'gender' => $request['gender'],
            'password' => Hash::make('12345678'),
        ]);

        $user->assignRole($type);

        if ($type === 'student') {
            // Ambil dua digit tahun dari NIM
            $nim = $request->nim;
            $angkatan = null;
            $session = session('context');
            if (preg_match('/^.{3}(\d{2})/', $nim, $matches)) {
                $tahun = intval($matches[1]);
                $angkatan = 2000 + $tahun; // misal 18 -> 2018
            }

            Student::create([
                'user_id' => $user->id,
                'nim' => $nim,
                'type' => $session,
                'angkatan' => $angkatan,
            ]);
        } elseif ($type === 'lecturer') {
            Lecturer::create([
                'user_id' => $user->id,
                'nidn' => $request['nidn'],
                'bagian' => $request['bagian'] ?? null,
                'faculty' => $request['faculty'] ?? null,
                'strata' => $request['strata'],
                'gelar' => $request['gelar'],
                'tipe_dosen' => $request['tipe_dosen'],
                'min_sks' => $request['min_sks'],
                'max_sks' => $request['max_sks'],
            ]);

            // Sync role sesuai input
            if ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }
        }

        return redirect()
            ->route(session('context') . '.admin.users.index', $type)
            ->with('success', ucfirst($type) . ' berhasil ditambahkan.');
    }

    public function downloadTemplate($type): BinaryFileResponse
    {
        if ($type === 'student') {
            $fileName = 'template_student.xlsx';
        } elseif ($type === 'lecturer') {
            $fileName = 'template_lecturer.xlsx';
        } else {
            $fileName = 'template_admin.xlsx';
        }

        $filePath = public_path('templates/' . $fileName);
        if (!file_exists($filePath)) {
            abort(404, 'Template file not found.');
        }

        return response()->download($filePath, $fileName);
    }

    public function import(Request $request, $type)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $studentType = session('context');
        try {
            Excel::import(new UsersImport($type, $studentType), $request->file('file'));
            return redirect()
                ->route(session('context') . '.admin.users.index', $type)
                ->with('success', 'Data ' . $type . ' berhasil diimport.');
        } catch (\Exception $e) {
            // log error supaya bisa dicek di storage/logs/laravel.log
            Log::error('Import error: ' . $e->getMessage());

            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function export(Request $request, $type)
    {
        $date = Carbon::now()->format('d-M-Y');
        $label = match ($type) {
            'student' => 'Mahasiswa',
            'lecturer' => 'Dosen',
            'admin' => 'Admin',
            default => 'User',
        };

        $semesterPart = '';
        if ($type === 'student' && $request->filled('semester_id')) {
            $semester = Semester::with('academicYear')->find($request->semester_id);
            if ($semester) {
                $semesterName = strtolower($semester->semester_name ?? '');
                $yearName = str_replace('/', '_', $semester->academicYear->year_name);
                $semesterPart = "_{$semesterName}_{$yearName}";
            }
        }
        $fileName = "{$label}{$semesterPart}_{$date}.xlsx";
        return Excel::download(new UsersExport($type, $request->all()), $fileName);
    }

    public function edit($type, $id)
    {
        if (in_array($type, ['student', 'lecturer'])) {
            $user = User::with($type)->findOrFail($id);
        } else {
            $user = User::findOrFail($id);
        }

        $roles = Role::where('name', '!=', 'koordinator')->pluck('name', 'id');
        return view('admin.users.edit', compact('user', 'type', 'roles'));
    }

    public function update(Request $request, $type, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'gender' => 'required',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ];

        if ($type === 'student') {
            $rules['nim'] = 'required|string';
            $rules['angkatan'] = 'required|string';
        } elseif ($type === 'lecturer') {
            $rules['nidn'] = 'nullable|string';
            $rules['roles'] = 'required|exists:roles,name';
            $rules['strata'] = 'required';
            $rules['gelar'] = 'nullable|string|max:50';
            $rules['tipe_dosen'] = 'required|in:Asdos,CDT,DT,DTT';
            $rules['min_sks'] = 'nullable|integer|min:0';
            $rules['max_sks'] = 'nullable|integer|min:0';
        }

        $request->validate($rules);

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
        ]);

        if ($type === 'student') {
            $user->student()->update([
                'nim' => $request->nim,
                'angkatan' => $request->angkatan,
            ]);
        } elseif ($type === 'lecturer') {
            if ($user->lecturer) {
                $user->lecturer->update([
                    'nidn' => $request->nidn,
                    'faculty' => $request->faculty ?? null,
                    'strata' => $request->strata,
                    'gelar' => $request->gelar,
                    'tipe_dosen' => $request->tipe_dosen,
                    'min_sks' => $request->min_sks,
                    'max_sks' => $request->max_sks,
                ]);
            } else {
                $user->lecturer()->create([
                    'nidn' => $request->nidn,
                    'faculty' => $request->faculty ?? null,
                    'strata' => $request->strata,
                    'gelar' => $request->gelar,
                    'tipe_dosen' => $request->tipe_dosen,
                    'min_sks' => $request->min_sks,
                    'max_sks' => $request->max_sks,
                ]);
            }
        }
        $user->syncRoles($request->roles);

        return redirect()
            ->route(session('context') . '.admin.users.index', $type)
            ->with('success', ucfirst($type) . ' updated successfully.');
    }

    public function show($type, $id)
    {
        $user = User::with(['student', 'lecturer'])->findOrFail($id);

        $courses = collect(); // gunakan plural untuk lebih jelas
        $kepaniteraan = collect();

        if ($type === 'student') {
            $student = $user->student;
            if ($student->type === 'PSSK') {
                $courses = CourseStudent::with(['student', 'semester'])
                    ->where('student_id', $student->id)
                    ->orderByDesc('semester_id')
                    ->paginate(15);
            } else {
                $kepaniteraan = StudentKoas::with('hospitalRotation.hospital', 'hospitalRotation.clinicalRotation', 'semester')
                    ->where('student_id', $student->id)->orderByDesc('semester_id')
                    ->paginate(15);
            }
        } elseif ($type === 'lecturer') {
            $lecturer = $user->lecturer;
            if ($lecturer) {
                $courses = CourseLecturer::with(['lecturer', 'semester'])
                    ->where('lecturer_id', $lecturer->id)
                    ->orderByDesc('semester_id')
                    ->paginate(15);
            }
        }
        return view('admin.users.show', compact('user', 'type', 'courses', 'kepaniteraan'));
    }

    public function destroy($type, $id)
    {
        $user = User::findOrFail($id);

        // Optional: validasi type
        if ($type === 'student' && !$user->hasRole('student')) {
            return redirect()->back()->with('error', 'User ini bukan student.');
        }
        if ($type === 'lecturer' && !$user->hasRole('lecturer')) {
            return redirect()->back()->with('error', 'User ini bukan lecturer.');
        }

        // Hapus relasi student/lecturer jika ada
        if ($type === 'student' && $user->student) {
            $user->student->delete();
        }
        if ($type === 'lecturer' && $user->lecturer) {
            $user->lecturer->delete();
        }

        $user->delete();

        return redirect()->route(session('context') . '.admin.users.index', $type)->with('success', 'User berhasil dihapus.');
    }
}
