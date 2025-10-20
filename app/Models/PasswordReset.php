<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * Kolom yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token',
    ];

    /**
     * Kolom yang harus disembunyikan dari array dan JSON.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Kolom yang harus di-cast ke tipe data native.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Cari record berdasarkan email
     *
     * @param  string  $email
     * @return \App\Models\PasswordReset|null
     */
    public static function findByEmail($email)
    {
        return static::where('email', $email)->first();
    }

    /**
     * Cari record berdasarkan token
     *
     * @param  string  $token
     * @return \App\Models\PasswordReset|null
     */
    public static function findByToken($token)
    {
        return static::where('token', $token)->first();
    }

    /**
     * Hapus token berdasarkan email
     *
     * @param  string  $email
     * @return bool
     */
    public static function deleteByEmail($email)
    {
        return static::where('email', $email)->delete();
    }

    /**
     * Cek apakah token masih valid (belum kadaluarsa)
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->created_at->gt(now()->subHours(2)); // Token valid 2 jam
    }
}
