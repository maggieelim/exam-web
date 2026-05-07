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
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exam_id')->index('exam_answers_exam_id_foreign');
            $table->unsignedBigInteger('exam_question_id')->index('exam_answers_exam_question_id_foreign');
            $table->unsignedBigInteger('user_id')->index('exam_answers_user_id_foreign');
            $table->text('answer')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('marked_doubt')->default(false);
            $table->boolean('is_correct')->nullable()->index('idx_exam_answers_is_correct');
            $table->decimal('score', 5)->nullable();
            $table->timestamps();
            $table->text('answer_text')->nullable();

            $table->index(['exam_id', 'exam_question_id'], 'idx_exam_answers_exam_question');
            $table->index(['exam_id', 'user_id'], 'idx_exam_answers_exam_user');
            $table->index(['exam_id', 'exam_question_id', 'is_correct'], 'idx_exam_question_correct');
            $table->index(['exam_id', 'user_id', 'is_correct'], 'idx_exam_user_correct');
            $table->index(['exam_question_id', 'user_id'], 'idx_question_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
