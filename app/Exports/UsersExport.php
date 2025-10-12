<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
  protected $type;
  protected $filters;

  public function __construct($type, $filters = [])
  {
    $this->type = $type;
    $this->filters = $filters;
  }

  public function collection()
  {
    $query = User::query()->with(['student', 'lecturer']);

    if ($this->type === 'student') {
      $query->whereHas('roles', fn($q) => $q->where('name', 'student'));

      if (!empty($this->filters['nim'])) {
        $query->whereHas('student', fn($q) => $q->where('nim', 'like', '%' . $this->filters['nim'] . '%'));
      }
    }

    if ($this->type === 'lecturer') {
      $query->whereHas('roles', fn($q) => $q->where('name', 'lecturer'));

      if (!empty($this->filters['nidn'])) {
        $query->whereHas('lecturer', fn($q) => $q->where('nidn', 'like', '%' . $this->filters['nidn'] . '%'));
      }
    } elseif ($this->type === 'admin') {
      // ⬅️ Tambahkan filter ini supaya hanya ambil role admin
      $query->whereHas('roles', fn($q) => $q->where('name', 'admin'));
    }

    // Filter umum
    if (!empty($this->filters['name'])) {
      $query->where('name', 'like', '%' . $this->filters['name'] . '%');
    }

    if (!empty($this->filters['email'])) {
      $query->where('email', 'like', '%' . $this->filters['email'] . '%');
    }

    return $query->get();
  }

  public function headings(): array
  {
    if ($this->type === 'student') {
      return ['NIM', 'Nama', 'Email', 'Gender', 'Angkatan'];
    }

    if ($this->type === 'lecturer') {
      return ['NIDN', 'Nama', 'Email', 'Gender', 'Strata', 'Gelar', 'Tipe Dosen', 'Min SKS', 'Max SKS'];
    }
    if ($this->type === 'admin') {
      return ['Nama', 'Email', 'Role'];
    }
    return ['Nama', 'Email'];
  }

  public function map($user): array
  {
    if ($this->type === 'student') {
      return [
        $user->student->nim ?? '-',
        $user->name,
        $user->email,
        $user->student->gender ?? '-',
        $user->student->angkatan ?? '-',
      ];
    }

    if ($this->type === 'lecturer') {
      return [
        $user->lecturer->nidn ?? '-',
        $user->name,
        $user->email,
        $user->lecturer->gender ?? '-',
        $user->lecturer->strata ?? '-',
        $user->lecturer->gelar ?? '-',
        $user->lecturer->tipe_dosen ?? '-',
        $user->lecturer->min_sks ?? '-',
        $user->lecturer->max_sks ?? '-',
      ];
    }
    if ($this->type === 'admin') {
      return [
        $user->name,
        $user->email,
        $user->roles->pluck('name')->implode(', ') ?? '-',
      ];
    }
    return [
      $user->name,
      $user->email,
    ];
  }

  public function styles(Worksheet $sheet)
  {
    // Gaya untuk header baris pertama
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
      'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'], // putih
        'size' => 12,
      ],
      'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
      ],
      'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF5CB6ED'], // warna biru muda #5cb6ed
      ],
    ]);

    // Tambahkan border tipis untuk semua sel
    $sheet->getStyle('A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
      ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    return [];
  }
}
