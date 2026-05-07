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
        Schema::create('practicum_groups', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id_unique');
            $table->bigInteger('course_schedule_id')->nullable();
            $table->bigInteger('teaching_schedule_id')->nullable();
            $table->string('tipe', 45)->nullable();
            $table->string('group_code', 45)->nullable();
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
        Schema::dropIfExists('practicum_groups');
    }
};
