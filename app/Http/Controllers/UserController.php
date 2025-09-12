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
    public function indexAdmin($type = null)
    {
        $query = User::with('roles');

        if ($type === 'student') {
            $query->role('student')->with('student');
        } elseif ($type === 'lecturer') {
            $query->role('lecturer')->with('lecturer');
        }

        $users = $query->get();

        return view('admin.users.index', compact('users', 'type'));
    }


    public function indexLecturer()
    {
        $users = User::role('student')
            ->with(['roles', 'student'])
            ->get();
        return view('lecturer.students.index', compact('users'));
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
}
