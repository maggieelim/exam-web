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
        Schema::create('pemicu_details', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id_unique');
            $table->bigInteger('teaching_schedule_id')->nullable()->index('idx_teaching_schedule');
            $table->bigInteger('kelompok_num')->nullable();
            $table->bigInteger('lecturer_id')->nullable()->index('idx_lecturer');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();

            $table->index(['lecturer_id', 'teaching_schedule_id'], 'idx_lecturer_schedule');
            $table->index(['teaching_schedule_id', 'kelompok_num'], 'idx_schedule_kelompok');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemicu_details');
    }
};
