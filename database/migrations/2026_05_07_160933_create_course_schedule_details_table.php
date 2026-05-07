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
        Schema::create('course_schedule_details', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id');
            $table->bigInteger('course_schedule_id')->nullable();
            $table->bigInteger('activity_id')->nullable();
            $table->integer('total_sessions')->nullable()->default(0);

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_schedule_details');
    }
};
