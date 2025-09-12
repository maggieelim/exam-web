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
        // Ubah tabel courses
        Schema::table('courses', function (Blueprint $table) {
            // Hapus foreign key jika ada
            if (Schema::hasColumn('courses', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }

            // Tambahkan kolom kode_blok sebelum name
            $table->string('kode_blok')->after('id');
        });

        // Hapus tabel categories
        Schema::dropIfExists('categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali tabel categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        // Balikkan perubahan pada tabel courses
        Schema::table('courses', function (Blueprint $table) {
            $table->string('cover')->nullable()->change();
        });
    }
};
