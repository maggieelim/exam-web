<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nidn',
        'faculty',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
