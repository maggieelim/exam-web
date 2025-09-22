<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------- Courses --------------------
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('kode_blok')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('cover')->nullable();
            $table->timestamps();
        });

        // -------------------- Pivot: course_student --------------------
        Schema::create('course_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // mahasiswa
            $table->timestamps();
        });

        // -------------------- Pivot: course_lecturer --------------------
        Schema::create('course_lecturer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // lecturer
            $table->timestamps();
        });

        // -------------------- Exams --------------------
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('exam_code')->unique();
            $table->string('title');
            $table->dateTime('exam_date');
            $table->integer('duration');
            $table->string('room')->nullable();
            $table->timestamps();
        });

        // -------------------- Exam Questions --------------------
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->text('badan_soal');
            $table->text('kalimat_tanya')->nullable();
            $table->timestamps();
        });

        // -------------------- Exam Question Answers --------------------
        Schema::create('exam_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_question_id')->constrained()->onDelete('cascade');
            $table->string('text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_answers');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('course_lecturer');
        Schema::dropIfExists('course_student');
        Schema::dropIfExists('courses');
    }
};
