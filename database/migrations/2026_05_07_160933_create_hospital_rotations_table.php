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
        Schema::create('hospital_rotations', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('hospital_id');
            $table->bigInteger('clinical_rotation_id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->bigInteger('semester_id')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'clinical_rotation_id'], 'uniq_hospital_rotation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_rotations');
    }
};
