<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
        if (!in_array($type, ['student', 'lecturer'])) {
            abort(404);
        }
        return view('admin.users.create', compact('type'));
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nim'      => $type === 'student' ? 'required|string|unique:students,nim' : 'nullable',
            'nidn'     => $type === 'lecturer' ? 'required|string|unique:lecturers,nidn' : 'nullable',
        ]);

        $user = User::create([
            'name'     => $request['name'],
            'email'    => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        $user->assignRole($type);

        if ($type === 'student') {
            Student::create([
                'user_id' => $user->id,
                'nim'     => $request['nim'],
                'tahun_ajaran' => $request->tahun_ajaran,
                'kelas' => $request->kelas,
                'angkatan' => $request->angkatan,
            ]);
        } elseif ($type === 'lecturer') {
            Lecturer::create([
                'user_id' => $user->id,
                'nidn'    => $request['nidn'],
                'faculty' => $request->faculty,
            ]);
        }

        return redirect()->route('admin.users.index', $type)->with('success', ucfirst($type) . ' berhasil ditambahkan.');
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


    public function edit($type, $id)
    {
        $user = User::with($type)->findOrFail($id); // ambil user + relasi
        return view('admin.users.edit', compact('user', 'type'));
    }

    public function update(Request $request, $type, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($type === 'student') {
            $user->student()->update([
                'nim' => $request->nim,
                'tahun_ajaran' => $request->tahun_ajaran,
                'kelas' => $request->kelas,
                'angkatan' => $request->angkatan,
            ]);
        } elseif ($type === 'lecturer') {
            $user->lecturer()->update([
                'nidn' => $request->nidn,
                'faculty' => $request->faculty,
            ]);
        }

        return redirect()->route('admin.users.index', $type)->with('success', ucfirst($type) . ' updated successfully.');
    }

    public function show($type, $id)
    {
        $user = User::with(['student', 'lecturer'])->findOrFail($id);

        return view('admin.users.show', compact('user', 'type'));
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
