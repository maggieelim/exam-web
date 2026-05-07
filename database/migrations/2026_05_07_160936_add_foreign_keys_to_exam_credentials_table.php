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
        Schema::table('exam_credentials', function (Blueprint $table) {
            $table->foreign(['exam_id'])->references(['id'])->on('exams')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_credentials', function (Blueprint $table) {
            $table->dropForeign('exam_credentials_exam_id_foreign');
        });
    }
};
