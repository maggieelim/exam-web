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
        Schema::table('course_lecturer', function (Blueprint $table) {
            $table->foreign(['course_id'])->references(['id'])->on('courses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lecturer_id'], 'fk_course_lecturer_lecturer_id')->references(['id'])->on('lecturers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['semester_id'], 'fk_course_lecturer_semester')->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_lecturer', function (Blueprint $table) {
            $table->dropForeign('course_lecturer_course_id_foreign');
            $table->dropForeign('fk_course_lecturer_lecturer_id');
            $table->dropForeign('fk_course_lecturer_semester');
        });
    }
};
