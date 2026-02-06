<?php

namespace App\Http\Controllers\PSPd;

use App\Http\Controllers\Controller;
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
    public function index(Request $request, string $status)
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'])) {
            abort(404);
        }

        $semester = SemesterService::active();
        $user = Auth::id();
        $lecturer = Lecturer::where('user_id', $user)->firstOrFail();

        $logbooks = Logbook::with('studentKoas', 'activityKoas')
            ->where('lecturer_id', $lecturer->id)
            ->where('status', $status) // Filter berdasarkan status dari URL
            ->whereHas('studentKoas', function ($query) use ($semester) {
                $query->where('semester_id', $semester->id);
            })
            ->when($request->filled('hospital'), function ($query) use ($request) {
                $query->whereHas('studentKoas.hospitalRotation.hospital', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->hospital . '%');
                });
            })->when($request->filled('name'), function ($query) use ($request) {
                $query->whereHas('studentKoas.student.user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->name . '%');
                });
            })->when($request->filled('nim'), function ($query) use ($request) {
                $query->whereHas('studentKoas.student', function ($q) use ($request) {
                    $q->where('nim', 'like', '%' . $request->nim . '%');
                });
            })
            ->paginate(20);

        return view('pspd.logbook.lecturer.index', compact('logbooks', 'status'));
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
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $status, string $id)
    {
        $logbook = Logbook::findOrFail($id);
        if ($logbook->status !== $status) {
            abort(404);
        }
        return view('pspd.logbook.lecturer.edit', compact('logbook', 'status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $status, string $id)
    {
        $logbook = Logbook::findOrFail($id);

        // Validasi status URL
        if ($logbook->status !== $status) {
            abort(404);
        }

        // Validasi action
        if (!in_array($request->action, ['approved', 'rejected'])) {
            return redirect()->back()->with('error', 'Invalid action.');
        }

        $logbook->update([
            'status' => $request->action,
        ]);

        // Redirect ke halaman sesuai status baru
        return redirect()->route('logbook.index', ['status' => $request->action])
            ->with('success', 'Logbook status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
