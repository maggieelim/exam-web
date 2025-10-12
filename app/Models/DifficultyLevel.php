<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DifficultyLevel extends Model
{
    protected $table = 'difficulty_levels';
    protected $fillable = ['name', 'min_ratio', 'max_ratio'];
    public $timestamps = true;

    public function scopeForRatio($query, $ratio)
    {
        return $query->where('min_ratio', '<=', $ratio)
            ->where('max_ratio', '>=', $ratio);
    }
}
