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
        Schema::create('exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('exam_code')->unique();
            $table->unsignedBigInteger('course_id')->nullable()->index('exams_course_id_foreign');
            $table->unsignedBigInteger('semester_id')->nullable()->index('fk_exams_semester');
            $table->unsignedBigInteger('teaching_schedule_id')->nullable()->index('fk_exams_teachingschedule');
            $table->string('title');
            $table->dateTime('exam_date')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'ended'])->default('upcoming');
            $table->boolean('is_published')->default(false);
            $table->string('room')->nullable();
            $table->integer('duration')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('exams_created_by_foreign');
            $table->unsignedBigInteger('updated_by')->nullable()->index('exams_updated_by_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
