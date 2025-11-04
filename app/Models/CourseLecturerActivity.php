<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseLecturerActivity extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = ['course_lecturer_id', 'activity_id'];

    public function activity(){
        return $this->belongsTo(Activity::class);
    }

    public function lecturer(){
        return $this->belongsTo(CourseLecturer::class);
    }
}
