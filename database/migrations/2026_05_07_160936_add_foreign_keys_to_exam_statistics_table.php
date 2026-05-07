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
        Schema::table('exam_statistics', function (Blueprint $table) {
            $table->foreign(['exam_id'], 'fk_exam_statistics_exam')->references(['id'])->on('exams')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['exam_question_id'], 'fk_exam_statistics_question')->references(['id'])->on('exam_questions')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_statistics', function (Blueprint $table) {
            $table->dropForeign('fk_exam_statistics_exam');
            $table->dropForeign('fk_exam_statistics_question');
        });
    }
};
