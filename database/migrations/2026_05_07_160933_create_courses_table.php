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
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_blok')->unique('kode_blok_unique');
            $table->string('name');
            $table->string('slug');
            $table->string('semester', 45)->nullable();
            $table->bigInteger('sesi');
            $table->unsignedBigInteger('coordinator_id')->nullable()->index('fk_courses_coordinator');
            $table->string('cover')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
