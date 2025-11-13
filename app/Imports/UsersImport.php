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
        $gender = strtolower(trim($row['gender'] ?? ''));

        // Pastikan gender tidak kosong
        if (empty($gender)) {
            throw new \Exception('Kolom gender tidak boleh kosong.');
        }

        // Hanya boleh 'Laki-Laki' atau 'Perempuan'
        $allowedGenders = ['laki-laki', 'perempuan'];
        if (!in_array($gender, $allowedGenders)) {
            throw new \Exception("Gender '{$row['gender']}' tidak valid. Hanya boleh 'Laki-Laki' atau 'Perempuan'.");
        }
        // Simpan user dulu
        $user = User::create([
            'name' => $row['nama'],
            'email' => $row['email'],
            'gender' => $row['gender'],
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
                'user_id' => $user->id,
                'nim' => $nim,
                'angkatan' => $angkatan,
                'gender' => $row['gender'],
            ]);
        }

        // Jika lecturer
        elseif ($this->type === 'lecturer') {
            $user->assignRole('lecturer');

            Lecturer::create([
                'user_id' => $user->id,
                'nidn' => $row['nidn'] ?? null,
                'gender' => $row['gender'] ?? null,
                'bagian' => $row['bagian'] ?? null,
                'strata' => $row['strata'] ?? null,
                'gelar' => $row['gelar'] ?? null,
                'tipe_dosen' => $row['tipe_dosen'] ?? null,
                'min_sks' => $row['min_sks'] ?? null,
                'max_sks' => $row['max_sks'] ?? null,
            ]);
        } elseif ($this->type === 'admin') {
            $user->assignRole('admin');
        }

        return $user;
    }
}
