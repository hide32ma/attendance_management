<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // 外部キーにて、user_idに紐付け
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 出勤年月日
            $table->date('work_date');
            // 出勤時間
            $table->timestamp('clock_in')->nullable();
            // 退勤時間
            $table->timestamp('clock_out')->nullable();
            // 勤務ステータス(状態)（勤務外 出勤中 休憩中 退勤済）
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
