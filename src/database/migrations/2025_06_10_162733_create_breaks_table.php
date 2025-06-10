<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();
            // 外部キーにて、attendance_idに紐付け
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            // 休憩入り時間
            $table->timestamp('break_start')->nullable();
            // 休憩戻り時間
            $table->timestamp('break_end')->nullable();
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
        Schema::dropIfExists('breaks');
    }
}
