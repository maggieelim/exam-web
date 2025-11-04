<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticumGroupMember extends Model
{
    use HasFactory;

    protected $table = 'practicum_group_members';
    protected $fillable = [
        'practicum_group_id',
        'kelompok_num',
    ];

    // Member milik satu grup praktikum
    public function group()
    {
        return $this->belongsTo(PracticumGroup::class, 'practicum_group_id');
    }
}
