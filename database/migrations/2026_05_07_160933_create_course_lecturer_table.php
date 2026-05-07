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
        Schema::create('course_lecturer', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lecturer_id')->index('fk_course_lecturer_lecturer_id');
            $table->unsignedBigInteger('course_id')->index('course_lecturer_course_id_foreign');
            $table->unsignedBigInteger('semester_id')->nullable()->index('fk_course_lecturer_semester');
            $table->timestamps();

            $table->index(['course_id', 'semester_id'], 'idx_course_semester');
            $table->index(['lecturer_id', 'semester_id'], 'idx_lecturer_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_lecturer');
    }
};
