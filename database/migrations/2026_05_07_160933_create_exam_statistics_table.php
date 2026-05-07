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
        Schema::create('exam_statistics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exam_id')->index('idx_exam_id');
            $table->unsignedBigInteger('exam_question_id')->index('idx_exam_question_id');
            $table->integer('total_students')->default(0);
            $table->integer('correct_count')->default(0);
            $table->decimal('correct_percentage', 5)->default(0);
            $table->decimal('discrimination_index', 6, 3)->default(0);
            $table->string('difficulty_level', 50)->nullable();
            $table->json('options_summary')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->unique(['exam_id', 'exam_question_id'], 'unique_exam_question');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_statistics');
    }
};
