# GrowWell - Aplikasi Monitoring Kesehatan Anak dan Remaja

GrowWell adalah aplikasi berbasis web yang dirancang untuk memonitoring pertumbuhan dan kesehatan anak, remaja, serta ibu hamil. Aplikasi ini menyediakan fitur pelacakan antropometri, nutrisi, konsultasi, dan edukasi kesehatan.

## Fitur Utama

- **Monitoring Pertumbuhan**: Pelacakan berat badan, tinggi badan, dan parameter antropometri lainnya
- **Manajemen Nutrisi**: Catatan asupan makanan dan kebutuhan nutrisi harian
- **Sistem Konsultasi**: Platform komunikasi antara pengguna dan tenaga kesehatan
- **Konten Edukasi**: Artikel, video, dan materi edukasi kesehatan
- **Sistem Notifikasi**: Pemberitahuan penting dan pengingat
- **Gamifikasi**: Sistem badge dan tantangan untuk motivasi pengguna
- **Dashboard Analitik**: Visualisasi data kesehatan dan pertumbuhan

## Teknologi yang Digunakan

- **Backend**: PHP Native
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Web Server**: Apache (melalui Laragon)

## Struktur Database

Aplikasi ini menggunakan database MySQL dengan skema yang terdiri dari 27 tabel utama:

### Tabel Utama
- `user` - Manajemen akun pengguna
- `orangtua` - Informasi orang tua/wali
- `anak` - Data profil anak
- `remaja` - Data profil remaja
- `ibuhamil` - Data profil ibu hamil
- `tenagakesehatan` - Profil tenaga kesehatan

### Tabel Monitoring
- `dataantropometri` - Data pengukuran antropometri
- `datanutrisi` - Catatan nutrisi harian
- `asupanmakanan` - Detail asupan makanan
- `pemantauanjanin` - Monitoring janin untuk ibu hamil

### Tabel Interaksi
- `konsultasi` - Riwayat konsultasi
- `pesan` - Pesan dalam konsultasi
- `interaksikonten` - Interaksi dengan konten edukasi
- `notifikasi` - Sistem notifikasi

### Tabel Edukasi dan Gamifikasi
- `kontenedukasi` - Konten edukasi kesehatan
- `tantangan` - Tantangan kesehatan
- `remajatantangan` - Partisipasi tantangan remaja
- `badge` - Sistem pencapaian
- `userbadge` - Badge yang dimiliki pengguna

## Instalasi

### Prasyarat
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Web server (Apache/Nginx)

### Langkah-langkah Instalasi

1. **Clone repositori**
   ```bash
   git clone https://github.com/LilyMarliana/BE-Latihan-Kelas1.git
   ```

2. **Impor database**
   - Import file `growwell_db.sql` ke dalam database MySQL Anda
   - Pastikan nama database adalah `growwell_db`

3. **Konfigurasi koneksi database**
   - Buka file `db.php`
   - Sesuaikan konfigurasi database:
     ```php
     $servername = "localhost";
     $username = "root";  // sesuaikan dengan username MySQL Anda
     $password = "";      // sesuaikan dengan password MySQL Anda
     $dbname = "growwell_db";
     ```

4. **Jalankan aplikasi**
   - Tempatkan folder proyek di direktori web server (htdocs untuk XAMPP/Laragon)
   - Akses aplikasi melalui browser

## API Endpoints

Aplikasi ini menyediakan API RESTful untuk setiap tabel. Struktur endpoint:

```
http://localhost/BE-Latihan-kelas/[NamaTabel]/[operasi].php
```

### Operasi yang Tersedia
- `create.php` - Menambahkan data baru
- `read.php` - Membaca data
- `update.php` - Memperbarui data
- `delete.php` - Menghapus data

### Contoh Penggunaan API

#### Membaca semua data anak
```
GET /Anak/read.php
```

#### Membaca data anak spesifik
```
GET /Anak/read.php?anakID=1
```

#### Membuat data anak baru
```
POST /Anak/create.php
Content-Type: application/x-www-form-urlencoded

orangtuaID=1
namaLengkap=John Doe
jenisKelamin=L
tanggalLahir=2020-01-01
```

#### Memperbarui data anak
```
POST /Anak/update.php
Content-Type: application/x-www-form-urlencoded

anakID=1
namaLengkap=John Smith
```

#### Menghapus data anak
```
POST /Anak/delete.php
Content-Type: application/x-www-form-urlencoded

anakID=1
```

## Struktur Folder

```
BE-Latihan-kelas/
├── db.php                    # Konfigurasi database
├── growwell_db.sql          # Skema database
├── test_connection.php      # Uji koneksi database
├── convert_all_folders.php  # Script konversi
├── AlertRisiko/            # API untuk alert risiko
├── Anak/                   # API untuk data anak
├── AsupanMakanan/          # API untuk asupan makanan
├── Badge/                  # API untuk badge
├── DataAntropometri/       # API untuk data antropometri
├── DataNutrisi/            # API untuk data nutrisi
├── IbuHamil/               # API untuk ibu hamil
├── InteraksiKonten/        # API untuk interaksi konten
├── Konsultasi/             # API untuk konsultasi
├── KontenEdukasi/          # API untuk konten edukasi
├── LaporanKesehatan/       # API untuk laporan kesehatan
├── LogAktivitas/           # API untuk log aktivitas
├── Notifikasi/             # API untuk notifikasi
├── OrangTua/               # API untuk orang tua
├── PemantauanJanin/        # API untuk pemantauan janin
├── Pesan/                  # API untuk pesan
├── ReferensiMakanan/       # API untuk referensi makanan
├── RekomendasiPersonal/    # API untuk rekomendasi personal
├── Remaja/                 # API untuk remaja
├── RemajaTantangan/        # API untuk tantangan remaja
├── RiwayatPoin/            # API untuk riwayat poin
├── Tantangan/              # API untuk tantangan
├── TenagaKesehatan/        # API untuk tenaga kesehatan
├── User/                   # API untuk user
├── UserBadge/              # API untuk user badge
├── UserPreferensi/         # API untuk preferensi user
└── WilayahKerja/           # API untuk wilayah kerja
```

## Kontribusi

Kami menyambut kontribusi dari komunitas. Silakan fork repositori ini dan kirimkan pull request untuk perbaikan atau penambahan fitur.

## Lisensi

Proyek ini dilisensikan di bawah MIT License - lihat file [LICENSE](LICENSE) untuk detailnya.

## Kontak

Jika Anda memiliki pertanyaan atau masukan, silakan hubungi kami melalui:
- Email: lilymarliana@example.com
- GitHub: [LilyMarliana](https://github.com/LilyMarliana)

---

© 2026 GrowWell. Dibuat dengan ❤️ untuk kesehatan anak dan remaja Indonesia.