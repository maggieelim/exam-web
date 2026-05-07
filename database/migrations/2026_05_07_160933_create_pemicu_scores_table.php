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
        Schema::create('pemicu_scores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pemicu_detail_id')->index('fk_ps_pemicu_detail');
            $table->unsignedBigInteger('teaching_schedule_id')->index('fk_ps_teaching_schedule');
            $table->unsignedBigInteger('course_student_id')->index('fk_ps_course_student');
            $table->unsignedTinyInteger('disiplin')->default(0);
            $table->unsignedTinyInteger('keaktifan')->default(0);
            $table->unsignedTinyInteger('berpikir_kritis')->default(0);
            $table->unsignedTinyInteger('info_baru')->nullable();
            $table->unsignedTinyInteger('analisis_rumusan')->nullable();
            $table->unsignedTinyInteger('total_score')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemicu_scores');
    }
};
