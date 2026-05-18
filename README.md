# Exam Web Documentation

## Overview

Exam Web adalah aplikasi berbasis web untuk manajemen ujian / evaluasi akademik yang dibangun menggunakan framework Laravel.
Sistem ini dirancang untuk membantu proses:

* Manajemen data ujian
* Pengelolaan soal
* Penilaian
* Manajemen user
* Monitoring hasil ujian
* Dashboard admin

Project menggunakan arsitektur MVC Laravel dan memanfaatkan Blade Template untuk frontend.

---

# Tech Stack

## Backend

* PHP
* Laravel
* PostgreSQL / MySQL
* Eloquent ORM

## Frontend

* Blade Template
* Bootstrap / Admin Template
* JavaScript
* AJAX

## Tools

* Composer
* Node.js & NPM
* Git

---

# Project Structure

```bash
exam-web/
│
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Providers/
│
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── public/
├── resources/
│   ├── views/
│   ├── js/
│   └── css/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
├── tests/
├── vendor/
├── .env
├── artisan
├── composer.json
└── package.json
```

---

# Installation Guide

## 1. Clone Repository

```bash
git clone https://github.com/maggieelim/exam-web.git
cd exam-web
```

---

## 2. Install Dependency PHP

```bash
composer install
```

---

## 3. Install Dependency Frontend

```bash
npm install
```

---

## 4. Copy Environment File

```bash
cp .env.example .env
```

Windows:

```bash
copy .env.example .env
```

---

## 5. Generate Laravel Key

```bash
php artisan key:generate
```

---

# Database Configuration

Edit file `.env`

## PostgreSQL Example

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=exam_web
DB_USERNAME=postgres
DB_PASSWORD=password
```

## MySQL Example

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exam_web
DB_USERNAME=root
DB_PASSWORD=
```

---

# Run Migration

```bash
php artisan migrate
```

Jika terdapat seeder:

```bash
php artisan db:seed
```

Atau:

```bash
php artisan migrate --seed
```

---

# Run Application

## Backend Laravel

```bash
php artisan serve
```

Default:

```bash
http://127.0.0.1:8000
```

---

# Authentication

Sistem memiliki beberapa role user, misalnya:

* Admin
* Dosen
* Mahasiswa
* Penguji

Role dapat dicek pada:

* migration users
* model User
* middleware role
* controller auth

---

# Main Modules

## 1. Authentication

Fitur:

* Login
* Logout
* Session management
* Middleware authentication

Lokasi:

```bash
app/Http/Controllers/Auth/
```

---

## 2. Dashboard

Menampilkan:

* Statistik ujian
* Data peserta
* Grafik hasil
* Ringkasan aktivitas

Lokasi:

```bash
resources/views/dashboard/
```

---

## 3. Exam Management

Fitur:

* Create exam
* Update exam
* Delete exam
* Schedule exam

Controller:

```bash
app/Http/Controllers/ExamController.php
```

---

## 4. Question Management

Fitur:

* Tambah soal
* Edit soal
* Multiple choice
* Essay

Relasi:

* Exam hasMany Questions

---

## 5. Student Exam

Fitur:

* Start exam
* Submit answer
* Auto timer
* Auto grading

---

## 6. Scoring & Evaluation

Fitur:

* Auto calculate score
* Result history
* Score analytics

---

# Routing

Semua routing web terdapat di:

```bash
routes/web.php
```

API route:

```bash
routes/api.php
```

Contoh:

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('exam', ExamController::class);
});
```

---

# Database Design

## Main Tables

| Table     | Function            |
| --------- | ------------------- |
| users     | Menyimpan data user |
| exams     | Data ujian          |
| questions | Data soal           |
| answers   | Jawaban peserta     |
| results   | Hasil ujian         |
| roles     | Hak akses           |

---

# Important Laravel Commands

## Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Optimize

```bash
php artisan optimize
```

---

## Queue

Jika menggunakan queue:

```bash
php artisan queue:work
```

---

## Schedule

Cek scheduler:

```bash
php artisan schedule:list
```

Run scheduler:

```bash
php artisan schedule:work
```

Cron Linux:

```bash
* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
```

---

# Storage Configuration

Jika menggunakan upload file:

```bash
php artisan storage:link
```

Akses file:

```bash
/storage/filename.ext
```

---

# Common Errors

## 1. Vendor Missing

```bash
composer install
```

---

## 2. APP_KEY Missing

```bash
php artisan key:generate
```

---

## 3. Storage 404

```bash
php artisan storage:link
```

---

## 4. Migration Error

Pastikan:

* database sudah dibuat
* konfigurasi `.env` benar

---

## 5. Permission Error Linux

```bash
chmod -R 775 storage bootstrap/cache
```

---

# Deployment Guide

## Production Build

```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan optimize
```

---

## Queue & Scheduler

Gunakan:

* Supervisor
* Cronjob

---

## Web Server

Disarankan:

* Nginx
* Apache

Root folder:

```bash
/public
```

---

# Coding Standard

## Naming

### Controller

```bash
ExamController
```

### Model

```bash
Exam
```

### Migration

```bash
create_exams_table
```

---

# Best Practice

## Saat Menambahkan Fitur Baru

1. Buat migration
2. Buat model
3. Buat controller
4. Tambahkan route
5. Tambahkan view
6. Tambahkan validation
7. Testing

---

# Recommended Improvements

## Backend

* Gunakan Form Request Validation
* Gunakan Service Layer
* Tambahkan Repository Pattern

## Security

* CSRF Protection
* Rate Limiting
* Activity Logging

## Performance

* Redis cache
* Queue optimization
* Lazy loading relation

---

# Contribution Guide

## Branch Naming

```bash
feature/exam-module
fix/login-bug
```

---

## Commit Convention

```bash
feat: add exam timer
fix: repair login validation
refactor: optimize dashboard query
```

---

# Future Development Suggestions

## Potential Features

* CBT mode
* Randomized questions
* Anti-cheat monitoring
* Real-time exam
* Webcam proctoring
* PDF export
* Excel export
* Notification system

---

# Developer Notes

## Important Files

| File                 | Function           |
| -------------------- | ------------------ |
| routes/web.php       | Main route         |
| app/Models           | Database models    |
| app/Http/Controllers | Business logic     |
| resources/views      | Frontend           |
| config/app.php       | Application config |
| .env                 | Environment config |

---

# Maintenance Checklist

## Sebelum Deploy

* [ ] Run test
* [ ] Clear cache
* [ ] Optimize app
* [ ] Backup database
* [ ] Check .env production
* [ ] Run migration
