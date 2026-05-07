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
        Schema::table('attendance_tokens', function (Blueprint $table) {
            $table->foreign(['attendance_session_id'], 'fk_qr_session')->references(['id'])->on('attendance_sessions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_tokens', function (Blueprint $table) {
            $table->dropForeign('fk_qr_session');
        });
    }
};
