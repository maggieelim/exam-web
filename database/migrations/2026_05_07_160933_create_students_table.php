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
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('students_user_id_foreign');
            $table->enum('type', ['PSSK', 'PSPD'])->nullable();
            $table->string('nim')->index('idx_student_nim');
            $table->enum('gender', ['Pria', 'Wanita', 'Laki-Laki', 'Perempuan'])->nullable();
            $table->year('angkatan')->nullable();
            $table->timestamps();

            $table->unique(['nim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
