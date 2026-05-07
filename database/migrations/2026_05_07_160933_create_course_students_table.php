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
        Schema::create('course_students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->nullable()->index('fk_course_students_student');
            $table->unsignedBigInteger('user_id')->index('course_students_user_id_foreign');
            $table->unsignedBigInteger('course_id')->index('course_students_course_id_foreign');
            $table->unsignedBigInteger('semester_id')->nullable()->index('fk_course_students_semester');
            $table->bigInteger('kelompok')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['course_id', 'kelompok'], 'idx_course_kelompok');
            $table->index(['course_id', 'student_id'], 'idx_course_student');
            $table->index(['student_id', 'semester_id'], 'idx_student_semester');
            $table->index(['user_id', 'course_id'], 'idx_user_course');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_students');
    }
};
