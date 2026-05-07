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
        Schema::table('lecturer_attendance_records', function (Blueprint $table) {
            $table->foreign(['course_lecturer_id'], 'fk_lect_att_course_lecturer')->references(['id'])->on('course_lecturer')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['attendance_session_id'], 'fk_lect_att_session')->references(['id'])->on('attendance_sessions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['attendance_session_id'], 'lecturer_attendance_records_ibfk_1')->references(['id'])->on('attendance_sessions')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturer_attendance_records', function (Blueprint $table) {
            $table->dropForeign('fk_lect_att_course_lecturer');
            $table->dropForeign('fk_lect_att_session');
            $table->dropForeign('lecturer_attendance_records_ibfk_1');
        });
    }
};
