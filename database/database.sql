CREATE DATABASE IF NOT EXISTS perpus_api;
USE perpus_api;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabel Buku
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_buku VARCHAR(20) NOT NULL,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    tahun_terbit YEAR NOT NULL,
    penerbit VARCHAR(100) NOT NULL
);

-- Insert Admin (Password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1j.O9d.XJgE7C.p9XvV4/6T2E3wBkW.'); 
-- Jika tanpa hash: masukkan plain text 'admin123' atau ganti di script login.

-- Insert Minimal 10 Data Buku Initial
INSERT INTO books (kode_buku, judul, penulis, kategori, tahun_terbit, penerbit) VALUES
('BK001', 'Laskar Pelangi', 'Andrea Hirata', 'Novel', 2005, 'Bentang Pustaka'),
('BK002', 'Bumi', 'Tere Liye', 'Novel', 2014, 'Gramedia'),
('BK003', 'Pemrograman PHP', 'Abdul Kadir', 'Teknologi', 2020, 'Andi'),
('BK004', 'Belajar HTML dan CSS', 'Jubilee Enterprise', 'Teknologi', 2021, 'Elex Media'),
('BK005', 'Dasar-Dasar JavaScript', 'Wahana Komputer', 'Teknologi', 2022, 'Andi'),
('BK006', 'Filosofi Teras', 'Henry Manampiring', 'Pengembangan Diri', 2018, 'Kompas'),
('BK007', 'Atomic Habits', 'James Clear', 'Pengembangan Diri', 2019, 'Gramedia'),
('BK008', 'Sebuah Seni Untuk Bersikap Bodo Amat', 'Mark Manson', 'Pengembangan Diri', 2018, 'Grasindo'),
('BK009', 'Clean Code', 'Robert C. Martin', 'Teknologi', 2008, 'Prentice Hall'),
('BK010', 'Hujan', 'Tere Liye', 'Novel', 2016, 'Gramedia');