<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('semester_id')->nullable()->index('idx_semester_id');
            $table->unsignedBigInteger('course_id')->nullable()->index('fk_sessions_course_idx');
            $table->unsignedBigInteger('teaching_schedule_id')->nullable()->unique('course_schedule_id_unique');
            $table->unsignedBigInteger('activity_id')->nullable()->index('fk_attendance_activity');
            $table->string('absensi_code', 100)->unique('absensi_code');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_long', 11, 8)->nullable();
            $table->string('loc_name', 250)->nullable();
            $table->integer('tolerance_meter')->nullable()->default(50);
            $table->enum('status', ['upcoming', 'active', 'finished'])->nullable()->default('upcoming');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
