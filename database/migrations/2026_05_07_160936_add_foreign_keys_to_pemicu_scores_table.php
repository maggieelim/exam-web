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
        Schema::table('pemicu_scores', function (Blueprint $table) {
            $table->foreign(['course_student_id'], 'fk_ps_course_student')->references(['id'])->on('course_students')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pemicu_detail_id'], 'fk_ps_pemicu_detail')->references(['id'])->on('pemicu_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['teaching_schedule_id'], 'fk_ps_teaching_schedule')->references(['id'])->on('teaching_schedules')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemicu_scores', function (Blueprint $table) {
            $table->dropForeign('fk_ps_course_student');
            $table->dropForeign('fk_ps_pemicu_detail');
            $table->dropForeign('fk_ps_teaching_schedule');
        });
    }
};
