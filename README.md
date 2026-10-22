# PERPUS API - Ujian Praktik ASTS Ganjil XII RPL 1

Aplikasi Data Buku Perpustakaan berbasis PHP Native yang memenuhi jobsheet:
- Login Admin + Session
- Dashboard statistik dari database
- JSON API
- Fetch API
- CRUD buku
- Pencarian, filter kategori, sorting tahun
- Upload gambar dari folder komputer
- Gambar dari URL
- Export Excel (.xlsx)
- Export PDF (.pdf)
- Logout
- Responsive interface

## 1. Kebutuhan

- XAMPP
- Apache aktif
- MySQL/MariaDB aktif
- PHP 8.2 atau lebih baru
- Composer
- Browser
- Visual Studio Code

## 2. Instalasi Project

Salin folder `perpus_api` ke:

`C:\xampp\htdocs\perpus_api`

Pastikan folder upload berikut ada:

`C:\xampp\htdocs\perpus_api\assets\uploads`

## 3. Buat Database

Buka:

`http://localhost/phpmyadmin`

Import file:

`database.sql`

Database yang dibuat:

`perpus_api`

Tabel:

- `books`
- `admins`

Akun login:

Username: `admin`
Password: `admin123`

## 4. Koneksi Database

File:

`koneksi.php`

Konfigurasi bawaan XAMPP:

- host: localhost
- username: root
- password: kosong
- database: perpus_api

Jika konfigurasi MySQL kamu berbeda, ubah bagian tersebut.

## 5. Install Library Export

Buka CMD:

```text
cd C:\xampp\htdocs\perpus_api
composer install
```

Composer akan membuat folder `vendor`.

Library:
- PhpSpreadsheet untuk Excel
- Dompdf untuk PDF

## 6. Jalankan

Buka:

`http://localhost/perpus_api/`

Login menggunakan:

`admin`

`admin123`

## 7. Alur Pengujian

1. Login berhasil.
2. Login salah harus menampilkan pesan.
3. Dashboard menampilkan statistik database.
4. Buka Data Buku.
5. Data buku harus muncul melalui `data_json.php` dan JavaScript `fetch()`.
6. Tambah buku.
7. Pada bagian gambar, pilih:
   - Upload dari Folder, atau
   - Gunakan URL.
8. Edit buku.
9. Hapus buku.
10. Export Excel.
11. Export PDF.
12. Logout.
13. Coba buka `dashboard.php` setelah logout; harus diarahkan ke login.

## 8. API

URL:

`http://localhost/perpus_api/data_json.php`

API menghasilkan JSON dari tabel `books`.

## 9. Struktur Project

```text
perpus_api/
├── assets/
│   └── uploads/
├── vendor/                 # dibuat oleh composer install
├── auth.php
├── buku.php
├── composer.json
├── dashboard.php
├── data_json.php
├── database.sql
├── edit.php
├── export_excel.php
├── export_pdf.php
├── hapus.php
├── index.php
├── koneksi.php
├── login.php
├── logout.php
├── README.md
├── sidebar.php
├── style.css
└── tambah.php
```

## 10. Catatan Gambar

Jika memilih upload dari folder, file disimpan ke:

`assets/uploads/`

Database menyimpan path relatif seperti:

`assets/uploads/cover_xxxxx.jpg`

Jika memilih URL, database menyimpan URL gambar secara langsung.

Saat data dihapus, file upload lokal juga ikut dihapus. Gambar dari URL tidak dihapus karena file tersebut berada di server eksternal.

## 11. Fitur Tambahan

Selain fitur wajib jobsheet, project ini sudah memiliki:
- Search
- Filter kategori
- Sorting tahun
- Preview gambar sebelum disimpan
- Validasi upload gambar
- Tampilan responsive
- Status API
- Password admin menggunakan `password_hash()` / `password_verify()`
