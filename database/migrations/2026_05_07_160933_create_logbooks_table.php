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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('student_koas_id')->nullable()->index('fk_logbook_student_koas');
            $table->bigInteger('lecturer_id')->nullable();
            $table->bigInteger('activity_koas_id')->nullable();
            $table->timestamp('date')->nullable();
            $table->string('description', 545)->nullable();
            $table->string('file_path', 545)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('note', 545)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
