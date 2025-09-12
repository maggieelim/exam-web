<?php

namespace App\Imports;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function model(array $row)
    {
        if (empty($row['name'])) {
            throw new \Exception("Kolom 'name' tidak boleh kosong.");
        }

        if (empty($row['email'])) {
            throw new \Exception("Kolom 'email' tidak boleh kosong.");
        }

        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Format email {$row['email']} tidak valid.");
        }

        if (User::where('email', $row['email'])->exists()) {
            throw new \Exception("Email {$row['email']} sudah terdaftar.");
        }

        // Simpan user dulu
        $user = User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => Hash::make($row['password'] ?? '123456'),
        ]);

        // Jika student
        if ($this->type === 'student') {
            $user->assignRole('student');
            Student::create([
                'user_id'      => $user->id,
                'nim'          => $row['nim'],
                'tahun_ajaran' => $row['tahun_ajaran'],
                'kelas'        => $row['kelas'],
                'angkatan'     => $row['angkatan'],
            ]);
        }

        // Jika lecturer
        elseif ($this->type === 'lecturer') {
            $user->assignRole('lecturer');
            Lecturer::create([
                'user_id' => $user->id,
                'nidn'    => $row['nidn'],
                'faculty' => $row['faculty'],
            ]);
        }

        return $user;
    }
}
