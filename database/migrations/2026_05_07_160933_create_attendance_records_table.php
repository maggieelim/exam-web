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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('attendance_session_id')->index('idx_session_id');
            $table->unsignedBigInteger('course_student_id')->index('idx_course_student_id');
            $table->string('nim', 20)->index('idx_nim');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('loc_name', 250)->nullable();
            $table->bigInteger('distance')->nullable();
            $table->string('wifi_ssid', 100)->nullable();
            $table->string('device_info')->nullable();
            $table->dateTime('scanned_at')->nullable()->index('idx_scanned_at');
            $table->enum('method', ['qr', 'manual'])->nullable()->default('qr');
            $table->enum('status', ['present', 'late', 'absent'])->nullable()->default('absent')->index('idx_status');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['attendance_session_id', 'status'], 'idx_session_status');
            $table->unique(['attendance_session_id', 'course_student_id'], 'unique_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
