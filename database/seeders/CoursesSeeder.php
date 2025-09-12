<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CoursesSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            ['kode_blok' => 'BL34001', 'name' => 'DOCTORPRENEUR',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL34003', 'name' => 'LIFESTYLE MEDICINE',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40701', 'name' => 'BELAJAR SEPANJANG HAYAT',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40702', 'name' => 'BIOMEDIK I',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40703', 'name' => 'BIOMEDIK II',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40704', 'name' => 'BIOMEDIK III',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40705', 'name' => 'ILMU KESEHATAN MASYARAKAT',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40706', 'name' => 'HUMANIORA',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40707', 'name' => 'SIKLUS HIDUP',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40708', 'name' => 'SISTIM HEMATOLOGI',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40709', 'name' => 'SISTIM IMUN & INFEKSI',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40710', 'name' => 'SISTIM MUSKULO-SKELETAL',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40711', 'name' => 'SISTIM KARDIOVASKULER',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40712', 'name' => 'SISTIM RESPIRASI',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40713', 'name' => 'SISTIM GASTRO INTESTINAL',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40714', 'name' => 'SISTIM HEPATO-BILIER',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40715', 'name' => 'SISTIM ENDOKRIN & METABOLISME',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40716', 'name' => 'SISTIM UROGENITAL',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40717', 'name' => 'SISTIM REPRODUKSI',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40718', 'name' => 'SISTIM SARAF & KEJIWAAN',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40719', 'name' => 'SISTIM PENGINDERAAN',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40720', 'name' => 'KEGAWATDARURATAN MEDIK',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40721', 'name' => 'ETIKA KEDOKTERAN, HUKUM KED. DAN KEDOKTERAN FORENSIK',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40722', 'name' => 'PROPOSAL SKRIPSI',  'cover' => 'default.jpg'],
            ['kode_blok' => 'BL40723', 'name' => 'SKRIPSI',  'cover' => 'default.jpg'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
