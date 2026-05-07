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
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->foreign(['exam_id'])->references(['id'])->on('exams')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['exam_question_id'])->references(['id'])->on('exam_questions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropForeign('exam_answers_exam_id_foreign');
            $table->dropForeign('exam_answers_exam_question_id_foreign');
            $table->dropForeign('exam_answers_user_id_foreign');
        });
    }
};
