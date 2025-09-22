<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->onDelete('cascade');
            $table->string('option'); // A, B, C, D, E
            $table->text('text')->nullable(); // Isi jawaban
            $table->boolean('is_correct')->default(false); // Menandai jawaban benar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_answers');
    }
};
