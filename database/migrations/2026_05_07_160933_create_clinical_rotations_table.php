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
        Schema::create('clinical_rotations', function (Blueprint $table) {
            $table->bigInteger('id', true)->unique('id_unique');
            $table->string('code', 45)->nullable();
            $table->string('name', 125)->nullable();
            $table->timestamps();

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_rotations');
    }
};
