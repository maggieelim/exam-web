<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AttendanceSessions;
use App\Models\AttendanceTokens;
use App\Models\Course;
use App\Models\CourseLecturer;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;
use Str;

class AttendanceSessionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private function getActiveSemester()
    {
        $today = Carbon::today();
        return Semester::where('start_date', '<=', $today)->where('end_date', '>=', $today)->first();
    }

    public function index(Request $request)
    {
        $semesterId = $request->get('semester_id');

        if (!$semesterId) {
            $activeSemester = $this->getActiveSemester();
            $semesterId = $activeSemester ? $activeSemester->id : null;
        }

        $activeSemester = $this->getActiveSemester();
        $semesters = Semester::with('academicYear')->get();
        $query = AttendanceSessions::query()->with(['course', 'activity']);

        $sort = $request->get('sort', 'start_time');
        $dir = $request->get('dir', 'asc');
        $allowedSorts = ['kode_blok', 'start_time'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'start_time';
        }

        $query->orderBy($sort, $dir);
        $attendances = $query->paginate(15)->appends($request->all());
        return view('attendance.index', compact('activeSemester', 'semesterId', 'semesters', 'sort', 'dir', 'attendances'));
    }

    public function create(Request $request)
    {
        $semester = $this->getActiveSemester();
        $semesterId = $request->get('semester_id', $semester?->id);
        $user = auth()->user();
        $today = Carbon::today();
        $lecturer = Lecturer::where('user_id', $user->id)->first();
        $activeSemester = Semester::where('start_date', '<=', $today)->where('end_date', '>=', $today)->first();

        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();
        $lecturers = Lecturer::with('courseLecturers')->get();
        $activity = Activity::all();

        if ($user->hasRole('lecturer')) {
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
                'tolerance_meter' => $request->tolerance,
                'semester_id' => $request->semester_id,
                'status' => 'active',
            ]);

            // Generate initial QR token
            $this->generateQrToken($attendanceSession->id);

            // Associate lecturers with this session
            foreach ($request->lecturers as $lecturerId) {
                $courseLecturer = CourseLecturer::firstOrCreate([
                    'course_id' => $request->course,
                    'lecturer_id' => $lecturerId,
                ]);

                // You might want to store lecturer association in a pivot table
                // This depends on your database structure
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
        // Delete old tokens
        AttendanceTokens::where('attendance_session_id', $attendanceSessionId)->delete();

        // Generate new token valid for 15 seconds
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

        // Generate atau ambil token yang masih valid
        $currentToken = AttendanceTokens::where('attendance_session_id', $attendanceSession->id)->where('expired_at', '>', Carbon::now())->first();

        if (!$currentToken) {
            $currentToken = $this->generateQrToken($attendanceSession->id);
        } else {
            $currentToken = $currentToken->token;
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

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
