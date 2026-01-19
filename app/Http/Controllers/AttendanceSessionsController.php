<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AttendanceRecords;
use App\Models\AttendanceSessions;
use App\Models\AttendanceTokens;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\Lecturer;
use App\Models\LecturerAttendanceRecords;
use App\Models\Semester;
use App\Services\SemesterService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Str;

class AttendanceSessionsController extends Controller
{
    public function getEvents()
    {
        $userId = Auth::id();
        $lecturer = Lecturer::with('courseLecturers')->where('user_id', $userId)->first();

        $courseLecturerIds = $lecturer->courseLecturers->pluck('id');

        $attendances =  AttendanceSessions::with(['course', 'activity', 'lecturerRecords'])
            ->whereHas('lecturerRecords', function ($q) use ($courseLecturerIds) {
                $q->whereIn('course_lecturer_id', $courseLecturerIds);
            })
            ->get()
            ->map(function ($attendance) use ($courseLecturerIds) {
                $hasAttendance = LecturerAttendanceRecords::where('attendance_session_id', $attendance->id)
                    ->whereIn('course_lecturer_id', $courseLecturerIds)
                    ->where('status', 'pending')
                    ->exists();

                // Tambahkan pengecekan jika sudah finished
                if ($attendance->status === 'finished') {
                    $url = route('attendances.report.show', [
                        'course'      => $attendance->course->slug,
                        'semester_id' => $attendance->semester_id,
                        'session'     => $attendance->id
                    ]);
                } else {
                    $url = $hasAttendance
                        ? route('attendance.edit', $attendance->absensi_code)
                        : route('attendance.show', $attendance->absensi_code);
                }
                return [
                    'title' => $attendance->course->name . ' - ' . $attendance->activity->activity_name,
                    'start' => $attendance->start_time,
                    'end'   => $attendance->end_time,
                    'url'   => $url,
                    'extendedProps' => [
                        'status' => $attendance->status,
                        'total'  => $attendance->total_attendance,
                    ],
                    'color' => match ($attendance->status) {
                        'finished' => '#5fb374ff', // hijau
                        'active'  => '#5da4f0ff', // biru
                        default    => '#ecc13eff', // kuning
                    },
                ];
            });

        return response()->json($attendances);
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $lecturer = Lecturer::with('courseLecturers')->where('user_id', $userId)->first();

        $courseLecturerIds = $lecturer->courseLecturers->pluck('id');

        $activeSemester = SemesterService::active();
        $semesterId = $request->get('semester_id', $activeSemester?->id);
        $semesters = SemesterService::list();

        // Sorting
        $allowedSorts = ['kode_blok', 'start_time'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'start_time';
        $dir = $request->get('dir', 'desc');

        // Query dasar + filter by lecturer
        $query = AttendanceSessions::with(['course', 'activity'])
            ->whereHas('lecturerRecords', function ($q) use ($courseLecturerIds) {
                $q->whereIn('course_lecturer_id', $courseLecturerIds);
            });

        // 🔍 Filter berdasarkan semester (jika ada)
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        // 🔍 Filter berdasarkan nama/kode blok (jika ada input 'name')
        if ($request->filled('name')) {
            $query->whereHas('course', function ($q) use ($request) {
                $q->where('kode_blok', 'LIKE', "%{$request->name}%")
                    ->orWhere('name', 'LIKE', "%{$request->name}%");
            });
        }

        // Urutkan hasil
        $attendances = $query->orderBy($sort, $dir)
            ->paginate(15)
            ->appends($request->all());

        // Format waktu dan update status otomatis
        $attendances->getCollection()->transform(function ($attendance) {
            $attendance->updateStatusIfExpired();

            $start = Carbon::parse($attendance->start_time);
            $end = Carbon::parse($attendance->end_time);

            $attendance->total_attendance = AttendanceRecords::where('id', $attendance->id)->count();
            $attendance->formatted_time = $start->translatedFormat('d M Y H:i') . ' - ' . $end->translatedFormat('H:i');

            return $attendance;
        });

        return view('attendance.index', compact(
            'activeSemester',
            'semesterId',
            'semesters',
            'sort',
            'dir',
            'attendances'
        ));
    }

    public function create(Request $request)
    {
        $semester = $this->getActiveSemester();
        $semesterId = $request->get('semester_id', $semester?->id);
        $user = auth()->user();
        $today = Carbon::today();
        $lecturer = Lecturer::where('user_id', $user->id)->first();

        $activeSemester = SemesterService::active();
        $semesters = SemesterService::list();

        $lecturers = Lecturer::with('courseLecturers')->get();
        $activity = Activity::all();
        /** @var \App\Models\User|\Spatie\Permission\Traits\HasRoles $user */

        if ($user->hasRole('lecturer') || $user->hasRole('koordinator')) {
            $courses = Course::whereHas('courseLecturer', function ($query) use ($lecturer, $semesterId) {
                $query->where('lecturer_id', $lecturer->id)->where('semester_id', $semesterId);
            })
                ->with([
                    'courseLecturer' => function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    },
                ])
                ->get();
        } elseif ($user->hasRole('admin')) {
            $courses = Course::whereHas('courseLecturer', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
                ->with([
                    'courseLecturer' => function ($q) use ($semesterId) {
                        $q->where('semester_id', $semesterId);
                    },
                ])
                ->get();
        }

        return view('attendance.create', compact('lecturer', 'courses', 'lecturers', 'activity', 'semesters', 'semesterId', 'activeSemester'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course' => 'required|exists:courses,id',
            'lecturers' => 'required|array',
            'lecturers.*' => 'exists:lecturers,id',
            'activity_id' => 'required',
            'date' => 'required|date',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i|after:startTime',
            'location_lat' => 'required',
            'location_long' => 'required',
            'tolerance' => 'required|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Gabungkan date dengan time
            $startDateTime = $request->date . ' ' . $request->startTime;
            $endDateTime = $request->date . ' ' . $request->endTime;

            // Generate unique attendance code
            $attendanceCode = 'ABS-' . strtoupper(Str::random(8));

            // Create attendance session
            $attendanceSession = AttendanceSessions::create([
                'course_id' => $request->course,
                'activity_id' => $request->activity_id,
                'absensi_code' => $attendanceCode,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'location_lat' => $request->location_lat,
                'location_long' => $request->location_long,
                'loc_name' => $request->location_address,
                'tolerance_meter' => $request->tolerance,
                'semester_id' => $request->semester_id,
                'status' => 'active',
            ]);

            // Generate initial QR token
            $this->generateQrToken($attendanceSession->id);

            // Associate lecturers with this session
            foreach ($request->lecturers as $lecturerId) {
                // pastikan course_lecturer_id ada
                $courseLecturer = CourseLecturer::firstOrCreate([
                    'course_id' => $request->course,
                    'lecturer_id' => $lecturerId,
                ]);

                // Simpan ke tabel LecturerAttendanceRecords
                LecturerAttendanceRecords::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'course_lecturer_id' => $courseLecturer->id,
                    'status' => 'not_checked_in', // default, bisa diganti sesuai kebutuhan
                    'checked_in_at' => null,
                ]);
            }

            DB::commit();

            return redirect()->route('attendance.index')->with('success', 'Attendance session created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to create attendance session: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function generateQrToken($attendanceSessionId)
    {
        $attendanceSession = AttendanceSessions::find($attendanceSessionId);
        if ($attendanceSession->isExpired()) {
            $attendanceSession->update(['status' => 'finished']);
            return null;
        }

        if (!$attendanceSession->isActive()) {
            return null;
        }

        AttendanceTokens::where('attendance_session_id', $attendanceSessionId)->delete();
        $token = Str::random(32);

        AttendanceTokens::create([
            'attendance_session_id' => $attendanceSessionId,
            'token' => $token,
            'expired_at' => Carbon::now()->addSeconds(60),
        ]);

        return $token;
    }

    public function show($attendanceCode)
    {
        $attendanceSession = AttendanceSessions::where('absensi_code', $attendanceCode)
            ->with(['course', 'token'])
            ->firstOrFail();

        return view('attendance.show', compact('attendanceSession'));
    }

    public function getQrCode($attendanceCode)
    {
        $attendanceSession = AttendanceSessions::where('absensi_code', $attendanceCode)->firstOrFail();
        if ($attendanceSession->isExpired()) {
            // Update status to finished if not already
            if ($attendanceSession->status !== 'finished') {
                $attendanceSession->update(['status' => 'finished']);
            }

            return response()->json([
                'expired' => true,
                'message' => 'Attendance session has ended. QR code is no longer available.',
            ]);
        }
        // Generate atau ambil token yang masih valid
        if ($attendanceSession->isActive()) {
            $currentToken = AttendanceTokens::where('attendance_session_id', $attendanceSession->id)->where('expired_at', '>', Carbon::now())->first();

            if (!$currentToken) {
                $currentToken = $this->generateQrToken($attendanceSession->id);
            } else {
                $currentToken = $currentToken->token;
            }
        } else {
            // Session hasn't started yet or has ended
            return response()->json([
                'expired' => true,
                'message' => 'Attendance session is not active.',
            ]);
        }

        $attendanceUrl = url("/student/attendance/{$attendanceSession->id}?token={$currentToken}");

        $qrData = [
            'url' => $attendanceUrl,
            'attendance_code' => $attendanceCode,
            'token' => $currentToken,
            'timestamp' => Carbon::now()->timestamp,
            'session_id' => $attendanceSession->id,
        ];

        return response()->json($qrData);
    }

    public function verifyAttendance(Request $request)
    {
        $request->validate([
            'attendance_code' => 'required|exists:attendance_sessions,absensi_code',
            'token' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'wifi_ssid' => 'nullable|string',
            'device_info' => 'nullable|string',
        ]);

        $attendanceSession = AttendanceSessions::where('absensi_code', $request->attendance_code)->first();

        if (!$attendanceSession) {
            return response()->json(['error' => 'Attendance session not found'], 404);
        }

        if ($attendanceSession->isExpired()) {
            // Update status to finished
            $attendanceSession->update(['status' => 'finished']);
            return response()->json(['error' => 'Attendance session has ended'], 400);
        }

        // Check if token is valid
        $validToken = AttendanceTokens::where('attendance_session_id', $attendanceSession->id)->where('token', $request->token)->where('expired_at', '>', Carbon::now())->first();

        if (!$validToken) {
            return response()->json(['error' => 'Invalid or expired QR code'], 400);
        }

        // Check location tolerance
        $distance = $this->calculateDistance($attendanceSession->location_lat, $attendanceSession->location_long, $request->latitude, $request->longitude);

        if ($distance > $attendanceSession->tolerance_meter) {
            return response()->json(['error' => 'You are outside the allowed attendance area'], 400);
        }

        // Check time validity
        $currentTime = Carbon::now();
        $startTime = Carbon::parse($attendanceSession->start_time);
        $endTime = Carbon::parse($attendanceSession->end_time);

        if ($currentTime->lt($startTime)) {
            return response()->json(['error' => 'Attendance session has not started yet'], 400);
        }

        if ($currentTime->gt($endTime)) {
            $attendanceSession->update(['status' => 'finished']);
            return response()->json(['error' => 'Attendance session has ended'], 400);
        }

        // Here you would save the attendance record
        // You'll need to get the student ID from the authenticated user

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'distance' => round($distance, 2),
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function edit($attendanceCode)
    {
        $attendance = AttendanceSessions::with('activity', 'semester.academicYear', 'course')->where('absensi_code', $attendanceCode)->first();
        $lecturers = LecturerAttendanceRecords::with('courseLecturer.lecturer.user')->where('attendance_session_id', $attendance->id)->get();

        return view('attendance.edit', compact('lecturers', 'attendance'));
    }

    public function update(Request $request, $attendanceCode)
    {
        $request->validate([
            'tolerance' => 'required|numeric|min:1',
            'location_lat' => 'required',
            'location_long' => 'required',
            'location_address' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $user = Auth::id();
            $lecturer = Lecturer::where('user_id', $user)->firstOrFail();

            $attendance = AttendanceSessions::where('absensi_code', $attendanceCode)->firstOrFail();
            $startTime = Carbon::parse($attendance->start_time);
            $endTime   = Carbon::parse($attendance->end_time);
            $now       = Carbon::now();
            if ($now->lt($startTime->copy()->subMinutes(10))) {
                return redirect()
                    ->back()
                    ->with('error', 'Absensi belum bisa di-update. Anda hanya bisa update maksimal 10 menit sebelum waktu mulai.')
                    ->withInput();
            }
            if ($now->gt($endTime)) {
                return redirect()
                    ->back()
                    ->with('error', 'Absensi sudah berakhir.')
                    ->withInput();
            }
            $courseLecturer = CourseLecturer::where('lecturer_id', $lecturer->id)->where('semester_id', $attendance->semester_id)->where('course_id', $attendance->course_id)->firstOrFail();
            $lecturerAttendance = LecturerAttendanceRecords::where('attendance_session_id', $attendance->id)->where('course_lecturer_id', $courseLecturer->id)->first();
            // Create attendance session
            $attendance->update([
                'location_lat' => $request->location_lat,
                'status' => 'active',
                'location_long' => $request->location_long,
                'loc_name' => $request->location_address,
                'tolerance_meter' => $request->tolerance,
            ]);

            if ($lecturerAttendance) {
                $lecturerAttendance->update([
                    'status' => 'checked_in',
                    'checked_in_at' => Carbon::now(),
                ]);
            }
            // Generate initial QR token
            $this->generateQrToken($attendance->id);

            DB::commit();

            return redirect()->route('attendance.show', $attendance->absensi_code)->with('success', 'Attendance session created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to create attendance session: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(string $id)
    {
        //
    }
}
