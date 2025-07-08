<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Attendance;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    protected static function booted()
    {
        static::created(function ($user) {
            // ユーザーが作成された直後に勤務外レコードを作成
            // Attendance::create([
                // 'user_id' => $user->id,
                // 'status' => Attendance::STATUS_OFF,

                // ↓こちらがnotnullの為、ユーザー作成時、作成した時間を一時的に登録させる
                // 'work_date' => today(),
                // 'clock_in' => now(),
            // ]);
        });
    }


    // 複数あるattendancesテーブルのレコード（データ）を取得するリレーション
    public function attendances()
    {
        return $this->hasMany('App\Models\Attendance');
    }

}
