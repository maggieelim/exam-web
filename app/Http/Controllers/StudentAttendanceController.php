<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSessions;
use App\Models\AttendanceRecords;
use App\Models\CourseStudent;
use App\Models\AttendanceTokens;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $activeSemester = $this->getActiveSemester();
        $semesterId = $request->query('semester_id', $activeSemester->id);
        $statusFilter = $request->query('status');
        $courseStudent = CourseStudent::where('user_id', $userId)->pluck('id');

        $attendances = AttendanceRecords::with(['courseStudent.course', 'session.activity'])
            ->whereIn('course_student_id', $courseStudent)
            ->whereHas('session', function ($q) use ($semesterId) {
                $q->whereIn('status', ['active', 'finished']);
                $q->where('semester_id', $semesterId);
            });

        if (!empty($statusFilter)) {
            $attendances->where('status', $statusFilter);
        }

        $attendances = $attendances->orderBy('scanned_at', 'desc')->paginate(20);
        $semesters = Semester::with('academicYear')->get();

        return view(
            'students.attendance.index',
            compact('attendances', 'semesters', 'semesterId', 'activeSemester')
        );
    }


    public function showAttendanceForm($attendanceSessionId)
    {
        $userId = auth()->id();
        $student = User::with('student')->where('id', $userId)->firstOrFail();
        $attendanceSession = AttendanceSessions::with(['course', 'activity'])->findOrFail($attendanceSessionId);

        $now = Carbon::now();
        $startTime = Carbon::parse($attendanceSession->start_time);
        $endTime = Carbon::parse($attendanceSession->end_time);

        if ($now < $startTime) {
            return view('students.attendance.attendance-error')->with('error', 'Attendance session has not started yet.');
        }

        if ($now > $endTime) {
            return view('students.attendance.attendance-error')->with('error', 'Attendance session has ended.');
        }

        return view('students.attendance.attendance-form', compact('attendanceSession', 'student'));
    }

    public function submitAttendance(Request $request, $attendanceSessionId)
    {
        $request->validate([
            'nim' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'token' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'wifi_ssid' => 'nullable|string',
        ]);

        $attendanceSession = AttendanceSessions::findOrFail($attendanceSessionId);

        try {
            $validToken = AttendanceTokens::where('attendance_session_id', $attendanceSession->id)->where('token', $request->token)->where('expired_at', '>', Carbon::now())->first();

            if (!$validToken) {
                return back()->withInput()->with('error', 'Invalid or expired QR code. Please scan again.');
            }

            $now = Carbon::now();
            $startTime = Carbon::parse($attendanceSession->start_time);
            $endTime = Carbon::parse($attendanceSession->end_time);

            if ($now < $startTime || $now > $endTime) {
                return back()->withInput()->with('error', 'Attendance session is not active.');
            }

            // Validasi lokasi
            $distance = $this->calculateDistance($attendanceSession->location_lat, $attendanceSession->location_long, $request->latitude, $request->longitude);

            if ($distance > $attendanceSession->tolerance_meter) {
                return back()
                    ->withInput()
                    ->with('error', 'You are outside the allowed attendance area. Distance: ' . round($distance, 2) . 'm');
            }

            $userId = auth()->id();
            $student = Student::where('nim', $request->nim)->firstOrFail();

            // Cek apakah mahasiswa sudah terdaftar di course
            $courseStudent = CourseStudent::where('student_id', $student->id)
                ->whereHas('course', function ($query) use ($attendanceSession) {
                    $query->where('id', $attendanceSession->course_id);
                })
                ->first();

            if (!$courseStudent) {
                return back()->withInput()->with('error', 'NIM not found in this course.');
            }
            if ($courseStudent->user_id !== $userId) {
                return back()->withInput()->with('error', 'User credential doesn’t match.');
            }

            // Cek apakah sudah absen
            $existingAttendance = AttendanceRecords::where('attendance_session_id', $attendanceSession->id)->where('nim', $request->nim)->whereNotNull('scanned_at')->first();

            if ($existingAttendance) {
                return back()->withInput()->with('error', 'You have already submitted attendance for this session.');
            }

            $gracePeriod = $startTime->copy()->addMinutes(20);
            $status = $now->greaterThan($gracePeriod) ? 'late' : 'present';

            // Simpan attendance record
            AttendanceRecords::updateOrCreate([
                'attendance_session_id' => $attendanceSessionId,
                'course_student_id' => $courseStudent->id,
                'nim' => $request->nim,
            ], [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'loc_name' => $request->loc_name,
                'distance' => $request->distance,
                'wifi_ssid' => $request->wifi_ssid,
                'device_info' => $request->header('User-Agent'),
                'scanned_at' => $now,
                'status' => $status,
            ]);

            return view('students.attendance.attendance-success')->with('success', 'Attendance submitted successfully!')->with('attendanceSession', $attendanceSession);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to submit attendance: ' . $e->getMessage());
        }
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
}
