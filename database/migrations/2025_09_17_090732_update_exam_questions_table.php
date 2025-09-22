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
        Schema::table('exam_questions', function (Blueprint $table) {
            // Hapus kolom lama yang tidak dipakai
            $table->dropColumn(['question_text', 'question_type', 'options', 'answer']);

            // Tambahkan kolom baru sesuai struktur soals
            $table->text('badan_soal')->nullable()->after('exam_id');
            $table->text('kalimat_tanya')->after('badan_soal');
            $table->string('opsi_a')->after('kalimat_tanya');
            $table->string('opsi_b')->after('opsi_a');
            $table->string('opsi_c')->after('opsi_b');
            $table->string('opsi_d')->after('opsi_c');
            $table->string('opsi_e')->nullable()->after('opsi_d');
            $table->string('jawaban')->after('opsi_e');
            $table->string('kode_soal')->nullable()->after('jawaban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            // Rollback: hapus kolom baru
            $table->dropColumn([
                'badan_soal',
                'kalimat_tanya',
                'opsi_a',
                'opsi_b',
                'opsi_c',
                'opsi_d',
                'opsi_e',
                'jawaban',
                'kode_soal'
            ]);

            // Tambahkan kembali kolom lama
            $table->text('question_text')->nullable();
            $table->string('question_type')->nullable();
            $table->json('options')->nullable();
            $table->string('answer')->nullable();
        });
    }
};
