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
        Schema::create('skillslab_details', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('idskillslab_details_unique');
            $table->bigInteger('course_schedule_id')->nullable();
            $table->bigInteger('teaching_schedule_id')->nullable();
            $table->string('group_code', 5)->nullable();
            $table->bigInteger('kelompok_num')->nullable();
            $table->bigInteger('lecturer_id')->nullable();
            $table->timestamps();

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skillslab_details');
    }
};
