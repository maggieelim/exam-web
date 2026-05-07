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
        Schema::create('exam_credentials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exam_id')->index('exam_credentials_exam_id_foreign');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('plain_password', 45)->nullable();
            $table->string('nim')->nullable()->index('idx_credential_nim');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'username']);
            $table->unique(['exam_id', 'nim'], 'unique_exam_nim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_credentials');
    }
};
