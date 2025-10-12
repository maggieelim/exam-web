<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::with('roles');


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

        // SORTING
        $sort = $request->get('sort', 'name'); // default name
        $dir  = $request->get('dir', 'asc');   // default asc

        if ($sort === 'nim') {
            $query->whereHas('student')
                ->join('students', 'users.id', '=', 'students.user_id')
                ->orderBy('students.nim', $dir)
                ->select('users.*');
        } elseif ($sort === 'nidn') {
            $query->whereHas('lecturer')
                ->join('lecturers', 'users.id', '=', 'lecturers.user_id')
                ->orderBy('lecturers.nidn', $dir)
                ->select('users.*');
        } else {
            $query->orderBy($sort, $dir);
        }

        $users = $query->paginate(15)->appends($request->all());

        return view('admin.users.index', compact('users', 'type', 'sort', 'dir'));
    }


    public function indexAdmin(Request $request, $type = null)
    {
        $query = User::with('roles');

        if ($type === 'student') {
            $query->role('student')->with('student');
        } elseif ($type === 'lecturer') {
            $query->role('lecturer')->with('lecturer');
        } elseif ($type === 'admin') {
            $query->role('admin');
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

        // SORTING
        $sort = $request->get('sort', 'name'); // default name
        $dir  = $request->get('dir', 'asc');   // default asc

        if ($sort === 'nim') {
            $query->whereHas('student')
                ->join('students', 'users.id', '=', 'students.user_id')
                ->orderBy('students.nim', $dir)
                ->select('users.*');
        } elseif ($sort === 'nidn') {
            $query->whereHas('lecturer')
                ->join('lecturers', 'users.id', '=', 'lecturers.user_id')
                ->orderBy('lecturers.nidn', $dir)
                ->select('users.*');
        } else {
            $query->orderBy($sort, $dir);
        }


        $users = $query->paginate(15)->appends($request->all());

        return view('admin.users.index', compact('users', 'type', 'sort', 'dir'));
    }

    public function create($type)
    {
        if (!in_array($type, ['student', 'lecturer', 'admin'])) {
            abort(404);
        }
        return view('admin.users.create', compact('type'));
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'gender'   => 'required',
            'nim'      => $type === 'student' ? 'required|string|unique:students,nim' : 'nullable',
            'nidn'     => $type === 'lecturer' ? 'required|string|unique:lecturers,nidn' : 'nullable',
            'strata'       => $type === 'lecturer' ? 'required|in:S1,S2,S3,Sp1,Sp2' : 'nullable',
            'gelar'        => $type === 'lecturer' ? 'nullable|string|max:50' : 'nullable',
            'tipe_dosen'   => $type === 'lecturer' ? 'required|in:Asdos,CDT,DT,DTT' : 'nullable',
            'min_sks'      => $type === 'lecturer' ? 'nullable|integer|min:0' : 'nullable',
            'max_sks'      => $type === 'lecturer' ? 'nullable|integer|min:0' : 'nullable',
        ]);

        $user = User::create([
            'name'     => $request['name'],
            'email'    => $request['email'],
            'password' => Hash::make('12345678'),
        ]);

        $user->assignRole($type);

        if ($type === 'student') {
            // Ambil dua digit tahun dari NIM
            $nim = $request->nim;
            $angkatan = null;

            if (preg_match('/^.{3}(\d{2})/', $nim, $matches)) {
                $tahun = intval($matches[1]);
                $angkatan = 2000 + $tahun; // misal 18 -> 2018
            }

            Student::create([
                'user_id'  => $user->id,
                'nim'      => $nim,
                'angkatan' => $angkatan,
                'gender'   => $request->gender,
            ]);
        } elseif ($type === 'lecturer') {
            Lecturer::create([
                'user_id'    => $user->id,
                'nidn'       => $request['nidn'],
                'faculty'    => $request['faculty'] ?? null,
                'gender'     => $request['gender'],
                'strata'     => $request['strata'],
                'gelar'      => $request['gelar'],
                'tipe_dosen' => $request['tipe_dosen'],
                'min_sks'    => $request['min_sks'],
                'max_sks'    => $request['max_sks'],
            ]);

            // Sync role sesuai input
            if ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }
        }

        return redirect()->route('admin.users.index', $type)
            ->with('success', ucfirst($type) . ' berhasil ditambahkan.');
    }

    public function downloadTemplate($type): BinaryFileResponse
    {
        $fileName = $type === 'student' ? 'template_student.xlsx' : 'template_lecturer.xlsx';
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

        try {
            Excel::import(new UsersImport($type), $request->file('file'));
            return redirect()
                ->route('admin.users.index', $type)
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
            'student'  => 'Mahasiswa',
            'lecturer' => 'Dosen',
            'admin'    => 'Admin',
            default    => 'User',
        };
        $fileName = "{$label}-{$date}.xlsx";
        return Excel::download(new UsersExport($type, $request->all()), $fileName);
    }

    public function edit($type, $id)
    {
        if (in_array($type, ['student', 'lecturer'])) {
            $user = User::with($type)->findOrFail($id);
        } else {
            $user = User::findOrFail($id); // untuk admin atau role lain
        }

        $roles = Role::pluck('name', 'id'); // Ambil semua role
        return view('admin.users.edit', compact('user', 'type', 'roles'));
    }


    public function update(Request $request, $type, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ];

        if ($type === 'student') {
            $rules['nim']      = 'required|string';
            $rules['angkatan'] = 'required|string';
            $rules['gender']   = 'required';
        } elseif ($type === 'lecturer') {
            $rules['nidn']       = 'required|string';
            $rules['role']       = 'required|exists:roles,name';
            $rules['strata']     = 'required|in:S1,S2,S3,Sp1,Sp2';
            $rules['gelar']      = 'nullable|string|max:50';
            $rules['tipe_dosen'] = 'required|in:Asdos,CDT,DT,DTT';
            $rules['min_sks']    = 'nullable|integer|min:0';
            $rules['max_sks']    = 'nullable|integer|min:0';
            $rules['gender']     = 'required';
        }

        $request->validate($rules);

        // Update user
        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($type === 'student') {
            $user->student()->update([
                'nim'      => $request->nim,
                'angkatan' => $request->angkatan,
                'gender'   => $request->gender,
            ]);
        } elseif ($type === 'lecturer') {
            if ($user->lecturer) {
                $user->lecturer->update([
                    'nidn'       => $request->nidn,
                    'faculty'    => $request->faculty ?? null,
                    'gender'     => $request->gender,
                    'strata'     => $request->strata,
                    'gelar'      => $request->gelar,
                    'tipe_dosen' => $request->tipe_dosen,
                    'min_sks'    => $request->min_sks,
                    'max_sks'    => $request->max_sks,
                ]);
            } else {
                $user->lecturer()->create([
                    'nidn'       => $request->nidn,
                    'faculty'    => $request->faculty ?? null,
                    'gender'     => $request->gender,
                    'strata'     => $request->strata,
                    'gelar'      => $request->gelar,
                    'tipe_dosen' => $request->tipe_dosen,
                    'min_sks'    => $request->min_sks,
                    'max_sks'    => $request->max_sks,
                ]);
            }

            // Sync role
            if ($request->filled('role')) {
                $user->syncRoles([$request->role]);
            }
        }

        return redirect()->route('admin.users.index', $type)
            ->with('success', ucfirst($type) . ' updated successfully.');
    }


    public function show($type, $id)
    {
        $user = User::with(['student', 'lecturer'])->findOrFail($id);

        $courses = collect(); // gunakan plural untuk lebih jelas

        if ($type === 'student') {
            // Ambil student_id berdasarkan user_id
            $student = $user->student;

            if ($student) {
                $courses = Course::with(['lecturers', 'students', 'courseStudents.semester'])
                    ->whereHas('students', function ($q) use ($student) {
                        $q->where('student_id', $student->id);
                    })
                    ->get();
            }
        } elseif ($type === 'lecturer') {
            $lecturer = $user->lecturer;
            if ($lecturer) {
                $courses = Course::with(['lecturers', 'students', 'courseLecturer.semester'])
                    ->whereHas('lecturers', function ($q) use ($lecturer) {
                        $q->where('lecturer_id', $lecturer->user_id);
                    })
                    ->get();
            }
        }

        return view('admin.users.show', compact('user', 'type', 'courses'));
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

        // Hapus user
        $user->delete();

        return redirect()->route('admin.users.index', $type)->with('success', 'User berhasil dihapus.');
    }
}
