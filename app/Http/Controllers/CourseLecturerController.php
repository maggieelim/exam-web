<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\CourseLecturerActivity;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\TeachingSchedule;
use DB;
use Illuminate\Http\Request;

class CourseLecturerController extends Controller
{
    public function getLecturerData(Request $request, string $slug)
    {
        $semesterId = $request->query('semester_id');
        $course = Course::with(['lecturers'])
            ->where('slug', $slug)
            ->firstOrFail();

        $query = CourseLecturer::with('lecturer.user', 'activities.activity')->where('course_id', $course->id)->where('semester_id', $semesterId);
        if ($request->filled('bagian')) {
            $query->whereHas('lecturer', function ($q) use ($request) {
                $q->where('bagian', 'like', '%' . $request->bagian . '%');
            });
        }

        if ($request->filled('name')) {
            $query->whereHas('lecturer.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');

        if ($sort === 'bagian') {
            $query->join('lecturers', 'course_lecturer.lecturer_id', '=', 'lecturers.id')->orderBy('lecturers.bagian', $dir)->select('course_lecturer.*');
        } elseif ($sort === 'name') {
            $query->join('lecturers', 'course_lecturer.lecturer_id', '=', 'lecturers.id')->join('users', 'lecturers.user_id', '=', 'users.id')->orderBy('users.name', $dir)->select('course_lecturer.*');
        } else {
            $query->orderBy('course_lecturer.created_at', 'desc');
        }

        $lecturers = $query->get();

        return (object) [
            'course' => $course,
            'lecturers' => $lecturers,
            'sort' => $sort,
            'dir' => $dir,
            'semesterId' => $semesterId,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

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
    public function edit(string $slug, Request $request)
    {
        // Ambil data awal
        $activity = Activity::where('category', 'teaching')->get();
        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->query('semester_id');
        $semester = Semester::with('academicYear')->findOrFail($semesterId);

        $selectedActivity = $request->query('activity_id', '');
        $activityId = $selectedActivity;

        // Base query dosen
        $query = Lecturer::with('user');

        // 🔍 Filter bagian
        if ($request->filled('bagian')) {
            $query->where('bagian', 'ILIKE', '%' . $request->bagian . '%');
        }

        // 🔍 Filter nama dosen
        if ($request->filled('name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'ILIKE', '%' . $request->name . '%');
            });
        }

        // 🔍 Sorting
        $sort = $request->get('sort', 'name');
        $dir = $request->get('dir', 'asc');
        $allowedSorts = ['name', 'bagian'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        // Sorting berdasarkan kolom di tabel yang benar
        if ($sort === 'name') {
            $query->join('users', 'lecturers.user_id', '=', 'users.id')->orderBy('users.name', $dir)->select('lecturers.*'); 
        } elseif ($sort === 'bagian') {
            $query->join('users', 'lecturers.user_id', '=', 'users.id')->orderBy('lecturers.bagian', $dir)->select('lecturers.*'); 
        } else {
            $query->orderBy($sort, $dir);
        }

        $lecturers = $query->get();

        // 🎯 Dosen yang sudah di-assign ke activity dan semester tersebut
        $assignedLecturers = [];
        if ($activityId) {
            $assignedLecturers = CourseLecturer::where('course_id', $course->id)
                ->where('semester_id', $semesterId)
                ->whereHas('activities', function ($q) use ($activityId) {
                    $q->where('activity_id', $activityId);
                })
                ->pluck('lecturer_id')
                ->toArray();
        }

        return view('courses.dosen.add_lecturer', compact('sort', 'dir', 'course', 'lecturers', 'semester', 'semesterId', 'activity', 'assignedLecturers', 'activityId', 'selectedActivity'));
    }

    public function getLecturersByActivity(Request $request, string $slug)
    {
        try {
            $semesterId = $request->query('semester_id');
            $activityId = $request->query('activity_id');
            $course = Course::where('slug', $slug)->firstOrFail();

            // Ambil semua dosen dengan filter
            $query = Lecturer::with('user');

            if ($request->filled('bagian')) {
                $query->where('bagian', 'like', '%' . $request->bagian . '%');
            }

            if ($request->filled('name')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->name . '%');
                });
            }

            $lecturers = $query->get();

            // Ambil dosen yang sudah ditugaskan untuk aktivitas tertentu
            $assignedLecturers = [];
            if ($activityId) {
                $assignedLecturers = CourseLecturer::where('course_id', $course->id)
                    ->where('semester_id', $semesterId)
                    ->whereHas('activities', function ($q) use ($activityId) {
                        $q->where('activity_id', $activityId);
                    })
                    ->pluck('lecturer_id')
                    ->toArray();
            }

            // Format data untuk response
            $formattedLecturers = $lecturers->map(function ($lecturer) use ($assignedLecturers) {
                return [
                    'id' => $lecturer->id,
                    'user' => [
                        'name' => $lecturer->user->name ?? null,
                    ],
                    'bagian' => $lecturer->bagian,
                    'strata' => $lecturer->strata,
                    'gelar' => $lecturer->gelar,
                    'tipe_dosen' => $lecturer->tipe_dosen,
                    'nidn' => $lecturer->nidn,
                    'assigned' => in_array($lecturer->id, $assignedLecturers),
                ];
            });

            return response()->json([
                'lecturers' => $formattedLecturers,
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'error' => 'Failed to load lecturers',
                    'success' => false,
                ],
                500,
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $slug)
    {
        try {
            \Log::info('Update lecturer started', ['slug' => $slug, 'data' => $request->all()]);

            $course = Course::where('slug', $slug)->firstOrFail();

            DB::transaction(function () use ($request, $course) {
                $lecturersData = $request->input('lecturers', []);

                \Log::info('Lecturers data to process', ['count' => count($lecturersData)]);

                foreach ($lecturersData as $lecturerId => $activities) {
                    \Log::info('Processing lecturer', [
                        'course_lecturer_id' => $lecturerId,
                        'activities' => $activities,
                    ]);

                    // Cek apakah course_lecturer_id valid
                    $courseLecturer = CourseLecturer::find($lecturerId);
                    if (!$courseLecturer) {
                        \Log::warning('Invalid course_lecturer_id', ['id' => $lecturerId]);
                        continue;
                    }

                    // Ambil activity lama
                    $oldActivities = CourseLecturerActivity::where('course_lecturer_id', $lecturerId)->pluck('activity_id')->toArray();

                    // Hapus activity lama
                    CourseLecturerActivity::where('course_lecturer_id', $lecturerId)->delete();

                    $newActivityIds = [];

                    // Simpan activity baru yang dicentang
                    foreach ($activities as $activityName => $isChecked) {
                        if ($isChecked == '1') {
                            // Pastikan ini string '1'
                            $activity = Activity::where('activity_name', ucfirst($activityName))->first();
                            if ($activity) {
                                CourseLecturerActivity::create([
                                    'course_lecturer_id' => $lecturerId,
                                    'activity_id' => $activity->id,
                                ]);
                                $newActivityIds[] = $activity->id;
                                \Log::info('Activity created', [
                                    'course_lecturer_id' => $lecturerId,
                                    'activity' => $activityName,
                                    'activity_id' => $activity->id,
                                ]);
                            }
                        }
                    }

                    // Update teaching schedules jika perlu
                    $realLecturerId = $courseLecturer->lecturer_id;
                    $removedActivityIds = array_diff($oldActivities, $newActivityIds);

                    if (!empty($removedActivityIds) && $realLecturerId) {
                        TeachingSchedule::whereIn('activity_id', $removedActivityIds)
                            ->where('lecturer_id', $realLecturerId)
                            ->update(['lecturer_id' => null]);
                    }
                }
            });

            \Log::info('Update completed successfully');

            // Return JSON response yang konsisten
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data dosen berhasil diperbarui.',
                ]);
            }

            return redirect()
                ->to(url()->previous() . '#dosen')
                ->with('success', 'Data dosen berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating lecturer data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                    ],
                    500,
                );
            }

            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function addLecturer(Request $request, string $slug)
    {
        $validated = $request->validate(
            [
                'selected_activity' => 'required',
                'lecturers' => 'required|array',
                'lecturers.*' => 'exists:lecturers,id',
            ],
            [
                'selected_activity.required' => 'Silakan pilih tugas terlebih dahulu.',
                'lecturers.required' => 'Silakan pilih minimal satu dosen untuk ditugaskan.',
                'lecturers.*.exists' => 'Terdapat dosen yang tidak valid dalam pilihan.',
            ],
        );

        $course = Course::where('slug', $slug)->firstOrFail();
        $semesterId = $request->input('semester_id');
        $activity = $request->input('selected_activity');
        DB::transaction(function () use ($request, $course, $semesterId, $activity) {
            foreach ($request->input('lecturers', []) as $lecturerId) {
                $courseLecturer = CourseLecturer::updateOrCreate([
                    'course_id' => $course->id,
                    'lecturer_id' => $lecturerId,
                    'semester_id' => $semesterId,
                ]);

                CourseLecturerActivity::updateOrCreate([
                    'course_lecturer_id' => $courseLecturer->id,
                    'activity_id' => $activity,
                ]);
            }
        });

        return redirect()
            ->to(url()->previous() . '#dosen')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
