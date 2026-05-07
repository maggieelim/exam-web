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
        Schema::create('pleno_details', function (Blueprint $table) {
            $table->bigIncrements('id')->unique('id_unique');
            $table->bigInteger('teaching_schedule_id')->nullable();
            $table->bigInteger('practicum_group_id')->nullable();
            $table->bigInteger('lecturer_id')->nullable();
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
        Schema::dropIfExists('pleno_details');
    }
};
