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
        Schema::table('hospital_rotations', function (Blueprint $table) {
            $table->foreign(['hospital_id'], 'hospital_rotations_ibfk_1')->references(['id'])->on('hospitals')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospital_rotations', function (Blueprint $table) {
            $table->dropForeign('hospital_rotations_ibfk_1');
        });
    }
};
