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
        Schema::table('course_students', function (Blueprint $table) {
            $table->foreign(['course_id'])->references(['id'])->on('courses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['semester_id'], 'fk_course_students_semester')->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['student_id'], 'fk_course_students_student')->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_students', function (Blueprint $table) {
            $table->dropForeign('course_students_course_id_foreign');
            $table->dropForeign('course_students_user_id_foreign');
            $table->dropForeign('fk_course_students_semester');
            $table->dropForeign('fk_course_students_student');
        });
    }
};
