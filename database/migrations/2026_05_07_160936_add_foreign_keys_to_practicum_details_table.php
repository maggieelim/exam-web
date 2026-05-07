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
        Schema::table('practicum_details', function (Blueprint $table) {
            $table->foreign(['practicum_group_id'], 'fk_practicumdetails_group')->references(['id'])->on('practicum_groups')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['lecturer_id'], 'fk_practicumdetails_lecturer')->references(['id'])->on('lecturers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['teaching_schedule_id'], 'fk_practicumdetails_teachingschedule')->references(['id'])->on('teaching_schedules')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practicum_details', function (Blueprint $table) {
            $table->dropForeign('fk_practicumdetails_group');
            $table->dropForeign('fk_practicumdetails_lecturer');
            $table->dropForeign('fk_practicumdetails_teachingschedule');
        });
    }
};
