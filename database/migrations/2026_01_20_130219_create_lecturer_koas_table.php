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
        Schema::create('lecturer_koas', function (Blueprint $table) {
            $table->id();
            $table->BigInteger('lecturer_id');
            $table->BigInteger('hospital_rotation_id');

            $table->timestamps();

            $table->foreign('lecturer_id')
                ->references('id')->on('lecturers')
                ->onDelete('cascade');

            $table->foreign('hospital_rotation_id')
                ->references('id')->on('hospital_rotations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturer_koas');
    }
};
