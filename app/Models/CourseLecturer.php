<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLecturer extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $fillable = ["lecturer_id", "course_id", "created_at", "updated_at"];
}
