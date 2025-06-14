<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    // 勤務ステータス定数
    public const STATUS_OFF = 0;
    public const STATUS_WORKING = 1;
    public const STATUS_BREAK = 2;
    public const STATUS_DONE = 3;

    // ステータスのラベル対応
    public const STATUS_LABELS = [
        self::STATUS_OFF => '勤務外',
        self::STATUS_WORKING => '出勤中',
        self::STATUS_BREAK => '休憩中',
        self::STATUS_DONE => '退勤済',
    ];

    // ステータス表示用アクセサ
    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->status] ?? '不明';
    }

    // 誰の出勤記録か？
    public function user():BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function breaks()
    {
        return $this->hasMany('App\Models\Break');
    }
}
