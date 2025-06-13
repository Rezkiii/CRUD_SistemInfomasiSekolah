# Sistem Informasi Sekolah

Sistem informasi sekolah yang menyediakan informasi tentang kegiatan sekolah, jadwal penerimaan siswa baru, dan manajemen konten oleh admin.

## Fitur

### Untuk Pengunjung (Tanpa Login)
- Informasi kegiatan sekolah
- Jadwal penerimaan siswa baru
- Download formulir pendaftaran
- Informasi umum sekolah

### Untuk Admin
- Login sistem
- Manajemen informasi sekolah
- Upload file (formulir pendaftaran)
- Manajemen konten

## Teknologi yang Digunakan
- PHP 8.x
- MySQL
- HTML5
- CSS3 (Bootstrap 5)
- JavaScript

## Struktur Database
Database terdiri dari beberapa tabel utama:
- users (untuk admin)
- activities (informasi kegiatan)
- admissions (jadwal penerimaan siswa baru)
- documents (file-file yang diupload)

## Instalasi
1. Clone repository ini
2. Import database dari file `database/school_db.sql`
3. Konfigurasi koneksi database di `config/database.php`
4. Akses melalui web server (Apache/Nginx)

## Keamanan
- Password hashing
- Prepared statements untuk query
- Validasi input
- Sanitasi output
- Session management 