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
        Schema::table('course_coordinators', function (Blueprint $table) {
            $table->foreign(['course_id'], 'course_coordinators_ibfk_1')->references(['id'])->on('courses')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lecturer_id'], 'course_coordinators_ibfk_2')->references(['id'])->on('lecturers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['semester_id'], 'course_coordinators_ibfk_3')->references(['id'])->on('semesters')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_coordinators', function (Blueprint $table) {
            $table->dropForeign('course_coordinators_ibfk_1');
            $table->dropForeign('course_coordinators_ibfk_2');
            $table->dropForeign('course_coordinators_ibfk_3');
        });
    }
};
