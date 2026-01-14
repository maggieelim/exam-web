<?php

namespace App\Http\Controllers\PSPD;

use App\Http\Controllers\Controller;
use App\Models\ClinicalRotation;
use Illuminate\Http\Request;

class ClinicalRotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClinicalRotation::query();
        if ($request->filled('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('code', 'like', '%' . $request->name . '%');
            });
        }
        $stases = $query->paginate(20);
        return view('pspd.stase.index', compact('stases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pspd.stase.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:clinical_rotations,code',
            'name' => 'required|string'
        ]);

        ClinicalRotation::create([
            'code' => $request->kode,
            'name' => $request->name
        ]);
        return redirect()->route('stase.index')->with('success', 'Stase Berhasil Ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stase = ClinicalRotation::findorfail($id);
        return view('pspd.stase.edit', compact('stase'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode' => 'required',
            'name' => 'required|string'
        ]);

        $stase = ClinicalRotation::findOrFail($id);
        $stase->update([
            'code' => $request->kode,
            'name' => $request->name
        ]);
        return redirect()->route('stase.index')->with('success', 'Stase Berhasil Diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stase = ClinicalRotation::findOrFail($id);
        $stase->delete();
        return redirect()->route(('stase.index'))->with('success', 'Stase Berhasil Dihapus');
    }
}
