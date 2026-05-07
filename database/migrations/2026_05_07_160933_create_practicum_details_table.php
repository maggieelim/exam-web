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
        Schema::create('practicum_details', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id_unique');
            $table->unsignedBigInteger('teaching_schedule_id')->nullable()->index('fk_practicumdetails_teachingschedule');
            $table->unsignedBigInteger('practicum_group_id')->nullable()->index('fk_practicumdetails_group');
            $table->unsignedBigInteger('lecturer_id')->nullable()->index('fk_practicumdetails_lecturer');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practicum_details');
    }
};
