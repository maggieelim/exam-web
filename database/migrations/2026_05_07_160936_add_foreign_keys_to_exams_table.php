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
        Schema::table('exams', function (Blueprint $table) {
            $table->foreign(['course_id'])->references(['id'])->on('courses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['updated_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['semester_id'], 'fk_exams_semester')->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['teaching_schedule_id'], 'fk_exams_teachingschedule')->references(['id'])->on('teaching_schedules')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign('exams_course_id_foreign');
            $table->dropForeign('exams_created_by_foreign');
            $table->dropForeign('exams_updated_by_foreign');
            $table->dropForeign('fk_exams_semester');
            $table->dropForeign('fk_exams_teachingschedule');
        });
    }
};
