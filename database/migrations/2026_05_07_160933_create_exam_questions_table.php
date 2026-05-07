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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exam_id')->index('exam_questions_exam_id_foreign');
            $table->unsignedBigInteger('category_id')->nullable()->index('exam_questions_category_id_foreign');
            $table->bigInteger('cpmk')->nullable()->default(1);
            $table->unsignedBigInteger('created_by')->nullable()->index('exam_questions_created_by_foreign');
            $table->unsignedBigInteger('updated_by')->nullable()->index('exam_questions_updated_by_foreign');
            $table->text('badan_soal')->nullable();
            $table->text('kalimat_tanya');
            $table->string('image')->nullable();
            $table->string('kode_soal')->nullable();
            $table->timestamps();
            $table->boolean('is_anulir')->nullable()->default(false);
            $table->enum('type', ['mcq', 'essay'])->nullable()->default('mcq');
            $table->float('score')->nullable()->default(1);

            $table->index(['exam_id', 'kode_soal'], 'idx_questions_exam_kode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
