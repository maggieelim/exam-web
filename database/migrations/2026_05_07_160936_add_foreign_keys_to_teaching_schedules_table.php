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
        Schema::table('teaching_schedules', function (Blueprint $table) {
            $table->foreign(['activity_id'], 'fk_teachingschedules_activity')->references(['id'])->on('activities')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['course_id'], 'fk_teachingschedules_course')->references(['id'])->on('courses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['course_schedule_id'], 'fk_teachingschedules_courseschedule')->references(['id'])->on('course_schedules')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_teachingschedules_creator')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lecturer_id'], 'fk_teachingschedules_lecturer')->references(['id'])->on('lecturers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['semester_id'], 'fk_teachingschedules_semester')->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teaching_schedules', function (Blueprint $table) {
            $table->dropForeign('fk_teachingschedules_activity');
            $table->dropForeign('fk_teachingschedules_course');
            $table->dropForeign('fk_teachingschedules_courseschedule');
            $table->dropForeign('fk_teachingschedules_creator');
            $table->dropForeign('fk_teachingschedules_lecturer');
            $table->dropForeign('fk_teachingschedules_semester');
        });
    }
};
