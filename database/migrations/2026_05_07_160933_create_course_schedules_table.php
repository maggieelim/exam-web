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
        Schema::create('course_schedules', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id');
            $table->bigInteger('semester_id')->nullable();
            $table->bigInteger('course_id')->nullable();
            $table->integer('year_level')->nullable();
            $table->bigInteger('kelompok')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_schedules');
    }
};
