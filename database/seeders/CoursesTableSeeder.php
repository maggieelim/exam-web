<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('courses')->delete();
        
        \DB::table('courses')->insert(array (
            0 => 
            array (
                'id' => 1,
                'kode_blok' => 'BL34001',
                'name' => 'Doctorpreneur',
                'slug' => 'doctorpreneur',
                'semester' => 'Ganjil',
                'sesi' => 5,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            1 => 
            array (
                'id' => 2,
                'kode_blok' => 'BL34003',
                'name' => 'Lifestyle Medicine',
                'slug' => 'lifestyle-medicine',
                'semester' => 'Ganjil',
                'sesi' => 5,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            2 => 
            array (
                'id' => 3,
                'kode_blok' => 'BL40701',
                'name' => 'Belajar Sepanjang Hayat',
                'slug' => 'belajar-sepanjang-hayat',
                'semester' => 'Ganjil',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'covers/1758592585_Logo-kedokteran-untar.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-23 08:56:25',
            ),
            3 => 
            array (
                'id' => 4,
                'kode_blok' => 'BL40702',
                'name' => 'Biomedik I',
                'slug' => 'biomedik-i',
                'semester' => 'Ganjil',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            4 => 
            array (
                'id' => 5,
                'kode_blok' => 'BL40703',
                'name' => 'Biomedik II',
                'slug' => 'biomedik-ii',
                'semester' => 'Ganjil',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            5 => 
            array (
                'id' => 6,
                'kode_blok' => 'BL40704',
                'name' => 'Biomedik III',
                'slug' => 'biomedik-iii',
                'semester' => 'Genap',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            6 => 
            array (
                'id' => 7,
                'kode_blok' => 'BL40705',
                'name' => 'Ilmu Kesehatan Masyarakat',
                'slug' => 'ilmu-kesehatan-masyarakat',
                'semester' => 'Genap',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            7 => 
            array (
                'id' => 8,
                'kode_blok' => 'BL40706',
                'name' => 'Humaniora',
                'slug' => 'humaniora',
                'semester' => 'Genap',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            8 => 
            array (
                'id' => 9,
                'kode_blok' => 'BL40707',
                'name' => 'Siklus Hidup',
                'slug' => 'siklus-hidup',
                'semester' => 'Ganjil',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            9 => 
            array (
                'id' => 10,
                'kode_blok' => 'BL40708',
                'name' => 'Sistim Hematologi',
                'slug' => 'sistim-hematologi',
                'semester' => 'Ganjil',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            10 => 
            array (
                'id' => 11,
                'kode_blok' => 'BL40709',
                'name' => 'Sistim Imun & Infeksi',
                'slug' => 'sistim-imun-infeksi',
                'semester' => 'Ganjil',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            11 => 
            array (
                'id' => 12,
                'kode_blok' => 'BL40710',
                'name' => 'Sistim Muskulo-skeletal',
                'slug' => 'sistim-muskulo-skeletal',
                'semester' => 'Genap',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            12 => 
            array (
                'id' => 13,
                'kode_blok' => 'BL40711',
                'name' => 'Sistim Kardiovaskuler',
                'slug' => 'sistim-kardiovaskuler',
                'semester' => 'Genap',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            13 => 
            array (
                'id' => 14,
                'kode_blok' => 'BL40712',
                'name' => 'Sistim Respirasi',
                'slug' => 'sistim-respirasi',
                'semester' => 'Genap',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            14 => 
            array (
                'id' => 15,
                'kode_blok' => 'BL40713',
                'name' => 'Sistim Gastro Intestinal',
                'slug' => 'sistim-gastro-intestinal',
                'semester' => 'Ganjil',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            15 => 
            array (
                'id' => 16,
                'kode_blok' => 'BL40714',
                'name' => 'Sistim Hepato-Bilier',
                'slug' => 'sistim-hepato-bilier',
                'semester' => 'Ganjil',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            16 => 
            array (
                'id' => 17,
                'kode_blok' => 'BL40715',
                'name' => 'Sistim Endokrin & Metabolisme',
                'slug' => 'sistim-endokrin-metabolisme',
                'semester' => 'Ganjil',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            17 => 
            array (
                'id' => 18,
                'kode_blok' => 'BL40716',
                'name' => 'Sistim Urogenital',
                'slug' => 'sistim-urogenital',
                'semester' => 'Genap',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            18 => 
            array (
                'id' => 19,
                'kode_blok' => 'BL40717',
                'name' => 'Sistim Reproduksi',
                'slug' => 'sistim-reproduksi',
                'semester' => 'Genap',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            19 => 
            array (
                'id' => 20,
                'kode_blok' => 'BL40718',
                'name' => 'Sistim Saraf & Kejiwaan',
                'slug' => 'sistim-saraf-kejiwaan',
                'semester' => 'Genap',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            20 => 
            array (
                'id' => 21,
                'kode_blok' => 'BL40719',
                'name' => 'Sistim Penginderaan',
                'slug' => 'sistim-penginderaan',
                'semester' => 'Ganjil/Genap',
                'sesi' => 1,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            21 => 
            array (
                'id' => 22,
                'kode_blok' => 'BL40720',
                'name' => 'Kegawatdaruratan Medik',
                'slug' => 'kegawatdaruratan-medik',
                'semester' => 'Ganjil/Genap',
                'sesi' => 2,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2026-03-16 17:48:59',
            ),
            22 => 
            array (
                'id' => 23,
                'kode_blok' => 'BL40721',
                'name' => 'Etika & Hukum Kedokteran',
                'slug' => 'etika-hukum-kedokteran',
                'semester' => 'Ganjil/Genap',
                'sesi' => 3,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => NULL,
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2025-09-22 15:57:04',
            ),
            23 => 
            array (
                'id' => 24,
                'kode_blok' => 'BL40722',
                'name' => 'Proposal Skripsi',
                'slug' => 'proposal-skripsi',
                'semester' => 'Ganjil/Genap',
                'sesi' => 4,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => '2026-04-01 23:30:18',
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2026-04-01 23:30:18',
            ),
            24 => 
            array (
                'id' => 25,
                'kode_blok' => 'BL40723',
                'name' => 'Skripsi',
                'slug' => 'skripsi',
                'semester' => 'Ganjil/Genap',
                'sesi' => 4,
                'coordinator_id' => NULL,
                'cover' => 'default.png',
                'deleted_at' => '2026-04-01 15:12:39',
                'created_at' => '2025-09-22 15:57:04',
                'updated_at' => '2026-04-01 15:12:39',
            ),
        ));
        
        
    }
}