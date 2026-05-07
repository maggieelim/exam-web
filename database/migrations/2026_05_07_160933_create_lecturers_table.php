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
        Schema::create('lecturers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('lecturers_user_id_foreign');
            $table->string('nidn')->nullable()->unique();
            $table->enum('type', ['pssk', 'pspd', 'both'])->nullable();
            $table->string('bagian', 100)->nullable();
            $table->string('strata', 20)->nullable();
            $table->string('gelar', 100)->nullable();
            $table->enum('tipe_dosen', ['Asdos', 'CDT', 'DT', 'DTT'])->nullable();
            $table->integer('min_sks')->nullable();
            $table->integer('max_sks')->nullable();
            $table->enum('gender', ['Pria', 'Wanita', 'Laki-Laki', 'Perempuan', 'N/A'])->nullable();
            $table->string('faculty')->nullable()->default('Kedokteran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
