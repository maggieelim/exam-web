<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Hospital::with('hospitalRotations.clinicalRotation');
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('code', 'like', '%' . $request->name . '%');
            });
        }
        $hospitals = $query->paginate(15);
        return view('pspd.hospital.index', compact('hospitals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pspd.hospital.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:hospitals,code',
            'name' => 'required|string'
        ]);

        Hospital::create([
            'code' => $request->kode,
            'name' => $request->name
        ]);
        return redirect()->route('rumah-sakit.index')->with('success', 'Rumah Sakit Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hospital = Hospital::with('hospitalRotations.clinicalRotation')->findOrFail($id);
        return view('pspd.hospital.show', compact('hospital'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $hospital = Hospital::findOrFail($id);
        return view('pspd.hospital.edit', compact('hospital'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $hospital = Hospital::findOrFail($id);
        $request->validate([
            'kode' => 'required',
            'name' => 'required|string'
        ]);
        $hospital->update([
            'code' => $request->kode,
            'name' => $request->name
        ]);
        return redirect()->route('rumah-sakit.index')->with('success', 'Rumah Sakit Berhasil Diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->delete();
        return redirect()->route('rumah-sakit.index')->with('success', 'Rumah Sakit Berhasil Dihapus');
    }
}
