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
        Schema::create('teaching_schedules', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id');
            $table->unsignedBigInteger('course_schedule_id')->nullable()->index('fk_teachingschedules_courseschedule');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('semester_id')->nullable()->index('fk_teachingschedules_semester');
            $table->unsignedBigInteger('activity_id')->nullable()->index('fk_teachingschedules_activity');
            $table->integer('session_number')->nullable();
            $table->unsignedTinyInteger('pemicu_ke')->nullable();
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->date('scheduled_date')->nullable()->index('idx_schedule_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('zone', 10)->nullable();
            $table->string('room', 50)->nullable();
            $table->string('group', 20)->nullable();
            $table->string('topic', 75)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('fk_teachingschedules_creator');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->index(['course_id', 'activity_id'], 'idx_course_activity');
            $table->index(['lecturer_id', 'semester_id', 'scheduled_date', 'start_time', 'end_time'], 'idx_teaching_conflict');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_schedules');
    }
};
