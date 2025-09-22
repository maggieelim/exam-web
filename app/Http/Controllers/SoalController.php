<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\SoalImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Soal;
use App\Models\JadwalUjian;

class SoalController extends Controller
{
    public function uploadForm()
    {
        return view('exams.soal.upload');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'nama_blok' => 'required|string',
            'judul' => 'required|string',
            'tanggal' => 'required|date',
            'jam' => 'required',
            'durasi' => 'required|integer',
            'ruangan' => 'required|string',
        ]);

        $jadwal = JadwalUjian::create([
            'judul' => $request->judul,
            'tanggal' => $request->tanggal,
            'jam' => $request->jam,
            'durasi' => $request->durasi,
            'ruangan' => $request->ruangan,
        ]);

        $namaBlok = $request->nama_blok;
        $kodeBatch = $this->generateKodeSoal();

        // Import soal
        Excel::import(new SoalImport($kodeBatch, $namaBlok, $jadwal->id), $request->file('file'));

        return redirect()->back()->with('success', 'Soal dan Jadwal berhasil dibuat!');
    }

    public function listKode()
    {
        $kodes = Soal::select('kode_soal', 'nama_blok')
            ->distinct()
            ->get();

        return view('soal.list_kode', compact('kodes'));
    }


    public function showByKode($kode)
    {
        $soals = Soal::where('kode_soal', $kode)->get();
        return view('soal.show_by_kode', compact('soals', 'kode'));
    }

    private function generateKodeSoal()
    {
        $lastSoal = Soal::orderBy('id', 'desc')->first();

        if ($lastSoal) {
            $lastNumber = (int) substr($lastSoal->kode_soal, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SWG-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
