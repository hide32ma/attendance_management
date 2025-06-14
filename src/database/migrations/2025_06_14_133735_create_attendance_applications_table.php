<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_applications', function (Blueprint $table) {
            $table->id();
            // 外部キーにて、user_idに紐付け
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 外部キーにて、attendance_idに紐付け
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            // 修正申請（前）の出勤退勤時間
            $table->time('before_clock_in')->nullable();
            $table->time('after_clock_in')->nullable();
            // 修正申請（後）の出勤退勤時間
            $table->time('before_clock_out')->nullable();
            $table->time('after_clock_out')->nullable();
            // 修正申請（前後）の休憩時間
            $table->json('before_breaks_json')->nullable();
            $table->json('after_breaks_json')->nullable();
            // 備考・申請理由
            $table->text('reason')->nullable();
            // ステータス: 0=承認待ち 1=承認待ち
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
        Schema::dropIfExists('attendance_applications');
    }
}
