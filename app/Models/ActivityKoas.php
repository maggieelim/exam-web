<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityKoas extends Model
{
    use HasFactory;
    protected $fillable = [
        'code_name',
        'name'
    ];
}
