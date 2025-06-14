<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance_application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'before_clock_in',
        'after_clock_in',
        'before_clock_out',
        'after_clock_out',
        'before_breaks_json',
        'after_breaks_json',
        'reason',
        'status',
    ];

    /**
     * 出勤修正対象の勤怠
     */
    public function attendance()
    {
        return $this->belongsTo('App\Models\Attendance');
    }

    /**
     * 申請したユーザー
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    // JSON型カラムを自動的に配列として扱いたいとき
    protected $casts = [
        'before_breaks_json' => 'array',
        'after_breaks_json' => 'array',
    ];
}
