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
        if (empty($row['nama'])) {
            throw new \Exception("Kolom 'nama' tidak boleh kosong.");
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
            'name' => $row['nama'],
            'email' => $row['email'],
            'password' => Hash::make('12345678'), // Password default
        ]);

        // Jika student
        if ($this->type === 'student') {
            $user->assignRole('student');

            if (empty($row['nim'])) {
                throw new \Exception("Kolom 'nim' wajib diisi untuk student {$row['nama']}.");
            }

            $nim = $row['nim'];
            $angkatan = null;

            if (preg_match('/^.{3}(\d{2})/', $nim, $matches)) {
                $tahun = intval($matches[1]);
                $angkatan = 2000 + $tahun;
            }

            Student::create([
                'user_id'  => $user->id,
                'nim'      => $nim,
                'angkatan' => $angkatan,
                'gender'   => $row['gender']
            ]);
        }

        // Jika lecturer
        elseif ($this->type === 'lecturer') {
            $user->assignRole('lecturer');

            Lecturer::create([
                'user_id'    => $user->id,
                'nidn'       => $row['nidn'] ?? null,
                'gender'    => $row['gender'] ?? null,
                'strata'     => $row['strata'] ?? null,
                'gelar'      => $row['gelar'] ?? null,
                'tipe_dosen' => $row['tipe_dosen'] ?? null,
                'min_sks'    => $row['min_sks'] ?? null,
                'max_sks'    => $row['max_sks'] ?? null,
            ]);
        }

        return $user;
    }
}
