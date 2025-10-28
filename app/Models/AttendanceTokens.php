<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTokens extends Model
{
    use HasFactory;
    protected $fillable = ['attendance_session_id', 'token', 'expired_at', 'created_at'];
    protected $dates = ['expired_at'];
    public $timestamps = false;

    public function AttendanceSession()
    {
        return $this->belongsTo(AttendanceSessions::class,'attendance_session_id');
    }
}
