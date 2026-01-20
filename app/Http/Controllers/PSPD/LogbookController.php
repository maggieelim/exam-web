<?php

namespace App\Http\Controllers\PSPd;

use App\Http\Controllers\Controller;
use App\Models\ActivityKoas;
use App\Models\Lecturer;
use App\Models\Logbook;
use App\Services\SemesterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $semester = SemesterService::active();
        $user = Auth::id();
        $lecturer = Lecturer::where('user_id', $user)->firstOrFail();
        $logbooks = Logbook::with('studentKoas', 'activityKoas')->where('lecturer_id', $lecturer->id)
            ->whereHas('studentKoas', function ($query) use ($semester) {
                $query->where('semester_id', $semester->id);
            })->paginate(20);

        return view('pspd.logbook.lecturer.index', compact('logbooks'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
