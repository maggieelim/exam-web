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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreign(['course_student_id'], 'fk_attendance_records_course_student')->references(['id'])->on('course_students')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['attendance_session_id'], 'fk_attendance_records_session')->references(['id'])->on('attendance_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['course_student_id'], 'fk_records_course_student')->references(['id'])->on('course_students')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['attendance_session_id'], 'fk_records_session')->references(['id'])->on('attendance_sessions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign('fk_attendance_records_course_student');
            $table->dropForeign('fk_attendance_records_session');
            $table->dropForeign('fk_records_course_student');
            $table->dropForeign('fk_records_session');
        });
    }
};
