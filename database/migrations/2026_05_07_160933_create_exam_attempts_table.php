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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exam_id');
            $table->bigInteger('credential_id')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'idle', 'timeout', 'paused'])->default('in_progress');
            $table->json('question_order')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('grading_status', ['ungraded', 'graded', 'published'])->nullable()->default('ungraded');
            $table->timestamps();
            $table->timestamp('started_at')->nullable()->index('idx_started_at');
            $table->dateTime('finished_at')->nullable()->index('idx_finished_at');
            $table->boolean('is_paused')->nullable()->default(false);
            $table->timestamp('paused_at')->nullable();
            $table->bigInteger('total_pause_seconds')->default(0);

            $table->index(['user_id', 'exam_id', 'status'], 'idx_active_attempt');
            $table->index(['exam_id', 'status', 'started_at'], 'idx_exam_status_time');
            $table->index(['user_id', 'status', 'started_at'], 'idx_user_status_time');
            $table->unique(['user_id', 'exam_id'], 'unique_user_exam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
