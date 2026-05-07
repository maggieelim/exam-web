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
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->foreign(['activity_id'], 'fk_attendance_activity')->references(['id'])->on('activities')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['course_id'], 'fk_attendance_course')->references(['id'])->on('courses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['semester_id'], 'fk_attendance_semester')->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['teaching_schedule_id'], 'fk_attendance_teachingschedule')->references(['id'])->on('teaching_schedules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['course_id'], 'fk_sessions_course')->references(['id'])->on('courses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['semester_id'], 'fk_sessions_semester')->references(['id'])->on('semesters')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropForeign('fk_attendance_activity');
            $table->dropForeign('fk_attendance_course');
            $table->dropForeign('fk_attendance_semester');
            $table->dropForeign('fk_attendance_teachingschedule');
            $table->dropForeign('fk_sessions_course');
            $table->dropForeign('fk_sessions_semester');
        });
    }
};
