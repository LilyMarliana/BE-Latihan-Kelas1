# Aplikasi GrowWell - Sistem Monitoring Kesehatan Anak dan Remaja

Aplikasi ini adalah sistem monitoring kesehatan anak dan remaja yang dibangun dengan PHP dan MySQL. Sistem ini menyediakan fungsi CRUD (Create, Read, Update, Delete) untuk mengelola data kesehatan, nutrisi, dan pertumbuhan anak serta remaja.

## Deskripsi

Aplikasi ini menyediakan empat endpoint utama untuk mengelola data kesehatan:

CREATE: Menambahkan data baru (anak, remaja, ibu hamil, data antropometri, data nutrisi, dll)
READ: Membaca/menampilkan data yang telah tersimpan
UPDATE: Memperbarui data yang sudah ada
DELETE: Menghapus data yang tidak diperlukan
Semua endpoint mengembalikan respons dalam format JSON.

## Struktur Tabel

Aplikasi ini memiliki 27 tabel utama dengan struktur kolom sebagai berikut:

### Tabel Utama
**user**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| userID | INT | Primary Key, Auto Increment |
| email | VARCHAR(100) | Alamat email pengguna |
| passwordHash | VARCHAR(255) | Hash password pengguna |
| role | ENUM | Role pengguna (orangtua, ibu_hamil, remaja, tenaga_kesehatan) |
| tanggalRegistrasi | DATETIME | Waktu registrasi pengguna |
| tanggalUpdate | DATETIME | Waktu terakhir update data |
| statusAktif | TINYINT | Status aktif pengguna |
| nomorTelepon | VARCHAR(20) | Nomor telepon pengguna |
| fotoProfile | VARCHAR(255) | Nama file foto profile |

**orangtua**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| orangtuaID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| namaLengkap | VARCHAR(100) | Nama lengkap orang tua |
| jenisKelamin | ENUM | Jenis kelamin (L/P) |
| tanggalLahir | DATE | Tanggal lahir orang tua |
| alamat | TEXT | Alamat lengkap orang tua |
| provinsi | VARCHAR(50) | Provinsi tempat tinggal |
| kota | VARCHAR(50) | Kota tempat tinggal |
| kecamatan | VARCHAR(50) | Kecamatan tempat tinggal |
| pendidikanTerakhir | VARCHAR(50) | Pendidikan terakhir |
| pekerjaan | VARCHAR(100) | Pekerjaan orang tua |
| statusPernikahan | ENUM | Status pernikahan (menikah, single, cerai) |

**anak**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| anakID | INT | Primary Key, Auto Increment |
| orangtuaID | INT | Foreign Key ke tabel orangtua |
| namaLengkap | VARCHAR(100) | Nama lengkap anak |
| jenisKelamin | ENUM | Jenis kelamin (L/P) |
| tanggalLahir | DATE | Tanggal lahir anak |
| tempatLahir | VARCHAR(100) | Tempat lahir anak |
| beratBadanLahir | DECIMAL(5,2) | Berat badan saat lahir |
| tinggiLahir | DECIMAL(5,2) | Tinggi saat lahir |
| golonganDarah | ENUM | Golongan darah (A, B, AB, O) |
| riwayatKelahiran | TEXT | Catatan riwayat kelahiran |
| fotoProfile | VARCHAR(255) | Nama file foto profile |
| statusAktif | TINYINT | Status aktif anak |

**remaja**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| remajaID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| nama | VARCHAR(100) | Nama remaja |
| tanggalLahir | DATE | Tanggal lahir remaja |
| jenisKelamin | ENUM | Jenis kelamin (L/P) |
| sekolah | VARCHAR(100) | Nama sekolah |
| kelas | VARCHAR(20) | Kelas saat ini |
| tingkatAktivitas | ENUM | Tingkat aktivitas (rendah, sedang, tinggi) |
| targetKesehatan | TEXT | Target kesehatan remaja |
| totalPoin | INT | Total poin yang dikumpulkan |
| level | INT | Level berdasarkan poin |

**ibuhamil**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| ibuHamilID | INT | Primary Key, Auto Increment |
| orangtuaID | INT | Foreign Key ke tabel orangtua |
| usiaKehamilan | INT | Usia kehamilan dalam minggu |
| tanggalHPL | DATE | Tanggal Hari Perkiraan Lahir |
| trimester | ENUM | Trimester kehamilan (1, 2, 3) |
| beratBadanSebelumHamil | DECIMAL(5,2) | Berat badan sebelum hamil |
| tinggiBadan | DECIMAL(5,2) | Tinggi badan ibu |
| golonganDarah | ENUM | Golongan darah (A, B, AB, O) |
| riwayatPenyakit | TEXT | Riwayat penyakit |
| riwayatKehamilanSebelumnya | TEXT | Riwayat kehamilan sebelumnya |
| risikoKehamilan | ENUM | Risiko kehamilan (rendah, sedang, tinggi) |
| statusImunisasi | TEXT | Status imunisasi |

**tenagakesehatan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| tenagaID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| namaLengkap | VARCHAR(100) | Nama lengkap tenaga kesehatan |
| jenisKelamin | ENUM | Jenis kelamin (L/P) |
| spesialisasi | VARCHAR(100) | Spesialisasi (bidan, dokter, kader, ahli gizi) |
| nomorSTR | VARCHAR(50) | Nomor Surat Tanda Registrasi |
| instansi | VARCHAR(100) | Instansi tempat bekerja |
| alamatInstansi | TEXT | Alamat instansi |
| wilayahKerjaID | INT | Foreign Key ke tabel wilayahkerja |

### Tabel Monitoring
**dataantropometri**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| dataID | INT | Primary Key, Auto Increment |
| anakID | INT | Foreign Key ke tabel anak |
| remajaID | INT | Foreign Key ke tabel remaja |
| tanggalPengukuran | DATE | Tanggal pengukuran dilakukan |
| usiaSaatUkur | INT | Usia saat pengukuran dalam bulan |
| beratBadan | DECIMAL(5,2) | Berat badan dalam kg |
| tinggiBadan | DECIMAL(5,2) | Tinggi badan dalam cm |
| lingkarKepala | DECIMAL(5,2) | Lingkar kepala dalam cm |
| lingkarLengan | DECIMAL(5,2) | Lingkar lengan atas dalam cm |
| zScoreBeratTinggi | DECIMAL(4,2) | Z-score Weight-for-Height |
| zScoreTinggiUsia | DECIMAL(4,2) | Z-score Height-for-Age |
| zScoreBeratUsia | DECIMAL(4,2) | Z-score Weight-for-Age |
| zScoreIMT | DECIMAL(4,2) | Z-score BMI-for-Age |
| kategoriStatusGizi | ENUM | Kategori status gizi |
| kategoriStunting | ENUM | Kategori stunting |
| catatanTambahan | TEXT | Catatan tambahan |
| petugasID | INT | ID tenaga kesehatan yang melakukan pengukuran |

**datanutrisi**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| nutrisiID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| anakID | INT | Foreign Key ke tabel anak |
| tanggalCatat | DATE | Tanggal pencatatan |
| totalKalori | DECIMAL(7,2) | Total kalori harian |
| totalProtein | DECIMAL(6,2) | Total protein harian |
| totalKarbohidrat | DECIMAL(6,2) | Total karbohidrat harian |
| totalLemak | DECIMAL(6,2) | Total lemak harian |
| totalSerat | DECIMAL(6,2) | Total serat harian |
| catatanHarian | TEXT | Catatan harian nutrisi |

**asupanmakanan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| asupanID | INT | Primary Key, Auto Increment |
| nutrisiID | INT | Foreign Key ke tabel datanutrisi |
| waktuMakan | ENUM | Waktu makan (sarapan, makan_siang, makan_malam, snack) |
| jamMakan | TIME | Jam makan |
| namaMakanan | VARCHAR(200) | Nama makanan |
| porsi | DECIMAL(6,2) | Jumlah porsi |
| satuanPorsi | VARCHAR(50) | Satuan porsi (gram, porsi, gelas, dll) |
| kalori | DECIMAL(7,2) | Kalori |
| protein | DECIMAL(6,2) | Protein |
| karbohidrat | DECIMAL(6,2) | Karbohidrat |
| lemak | DECIMAL(6,2) | Lemak |
| serat | DECIMAL(6,2) | Serat |
| kategoriMakanan | VARCHAR(50) | Kategori makanan |
| fotoMakanan | VARCHAR(255) | Foto makanan |

**pemantauanjanin**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| pemantauanID | INT | Primary Key, Auto Increment |
| ibuHamilID | INT | Foreign Key ke tabel ibuhamil |
| tanggalPemeriksaan | DATE | Tanggal pemeriksaan |
| usiaKehamilanSaatIni | INT | Usia kehamilan saat pemeriksaan |
| beratBadanIbu | DECIMAL(5,2) | Berat badan ibu |
| tekananDarahSistolik | INT | Tekanan darah sistolik |
| tekananDarahDiastolik | INT | Tekanan darah diastolik |
| tinggiPundusFundus | DECIMAL(5,2) | Tinggi fundus uterus |
| denyutJantungJanin | INT | Denyut jantung janin |
| gerakanJanin | ENUM | Gerakan janin (aktif, normal, kurang) |
| posisiJanin | VARCHAR(50) | Posisi janin |
| estimasiBeratJanin | DECIMAL(5,2) | Estimasi berat janin |
| catatanDokter | TEXT | Catatan dari dokter |
| petugasID | INT | ID tenaga kesehatan |

### Tabel Interaksi
**konsultasi**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| konsultasiID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| tenagaKesehatanID | INT | Foreign Key ke tabel tenagakesehatan |
| tipeKonsultasi | ENUM | Tipe konsultasi (chat, video_call, phone_call) |
| statusKonsultasi | ENUM | Status konsultasi |
| tanggalJadwal | DATETIME | Tanggal dan waktu jadwal konsultasi |
| tanggalMulai | DATETIME | Tanggal dan waktu mulai konsultasi |
| tanggalSelesai | DATETIME | Tanggal dan waktu selesai konsultasi |
| durasi | INT | Durasi konsultasi dalam menit |
| topikKonsultasi | VARCHAR(200) | Topik konsultasi |
| keluhanUtama | TEXT | Keluhan utama |
| catatanDokter | TEXT | Catatan dari dokter |
| diagnosaSementara | TEXT | Diagnosa sementara |
| rekomendasiTindakan | TEXT | Rekomendasi tindakan |
| urlRekaman | VARCHAR(255) | URL rekaman konsultasi |
| rating | INT | Rating konsultasi |
| feedback | TEXT | Feedback dari pasien |

**pesan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| pesanID | INT | Primary Key, Auto Increment |
| konsultasiID | INT | Foreign Key ke tabel konsultasi |
| pengirimID | INT | ID pengirim (user atau tenaga kesehatan) |
| tipePengirim | ENUM | Tipe pengirim (user, tenaga_kesehatan) |
| isiPesan | TEXT | Isi pesan |
| fileLampiran | VARCHAR(255) | File lampiran |
| tipeFile | VARCHAR(50) | Tipe file lampiran |
| tanggalKirim | DATETIME | Tanggal dan waktu kirim pesan |
| statusBaca | TINYINT | Status pesan sudah dibaca |
| tanggalBaca | DATETIME | Tanggal dan waktu pesan dibaca |

**interaksikonten**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| interaksiID | INT | Primary Key, Auto Increment |
| kontenID | INT | Foreign Key ke tabel kontenedukasi |
| userID | INT | Foreign Key ke tabel user |
| tipeInteraksi | ENUM | Tipe interaksi (view, like, share, bookmark, complete) |
| tanggalInteraksi | DATETIME | Tanggal dan waktu interaksi |
| durasiView | INT | Durasi view dalam detik |

**notifikasi**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| notifID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| jenisNotif | ENUM | Jenis notifikasi |
| judul | VARCHAR(200) | Judul notifikasi |
| isiNotif | TEXT | Isi notifikasi |
| prioritas | ENUM | Prioritas notifikasi |
| tanggalKirim | DATETIME | Tanggal dan waktu kirim |
| tanggalBaca | DATETIME | Tanggal dan waktu baca |
| statusBaca | TINYINT | Status sudah dibaca |
| actionURL | VARCHAR(255) | URL untuk aksi |
| actionLabel | VARCHAR(50) | Label tombol aksi |
| referensiID | INT | ID dari entitas terkait |
| referensiTipe | VARCHAR(50) | Nama tabel referensi |

### Tabel Edukasi dan Gamifikasi
**kontenedukasi**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| kontenID | INT | Primary Key, Auto Increment |
| judul | VARCHAR(200) | Judul konten |
| slug | VARCHAR(200) | Slug konten |
| kategori | ENUM | Kategori konten |
| targetSegmen | ENUM | Target segmen (orangtua, remaja, ibu_hamil, umum) |
| formatKonten | ENUM | Format konten (artikel, video, infografis, quiz, podcast) |
| isiKonten | LONGTEXT | Isi konten |
| excerptSingkat | TEXT | Ringkasan konten |
| gambarUtama | VARCHAR(255) | Gambar utama |
| urlVideo | VARCHAR(255) | URL video |
| urlFile | VARCHAR(255) | URL file |
| authorID | INT | ID penulis (tenaga kesehatan) |
| sumberReferensi | TEXT | Sumber referensi |
| tanggalPublish | DATETIME | Tanggal publikasi |
| viewCount | INT | Jumlah tampilan |
| likeCount | INT | Jumlah suka |
| shareCount | INT | Jumlah berbagi |
| tags | TEXT | Tag konten |
| statusPublish | ENUM | Status publikasi |

**tantangan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| tantanganID | INT | Primary Key, Auto Increment |
| namaTantangan | VARCHAR(200) | Nama tantangan |
| deskripsi | TEXT | Deskripsi tantangan |
| tipeTarget | ENUM | Tipe target (nutrisi, aktivitas, pemantauan, edukasi, sosial) |
| targetValue | DECIMAL(10,2) | Nilai target |
| satuanTarget | VARCHAR(50) | Satuan target |
| periodeAktif | ENUM | Periode aktif (harian, mingguan, bulanan, custom) |
| tanggalMulai | DATE | Tanggal mulai |
| tanggalSelesai | DATE | Tanggal selesai |
| poinReward | INT | Poin reward |
| badgeRewardID | INT | ID badge reward |
| targetPeserta | ENUM | Target peserta (remaja, orangtua, semua) |

**remajatantangan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| partisipasiID | INT | Primary Key, Auto Increment |
| remajaID | INT | Foreign Key ke tabel remaja |
| tantanganID | INT | Foreign Key ke tabel tantangan |
| tanggalMulai | DATETIME | Tanggal mulai tantangan |
| tanggalSelesai | DATETIME | Tanggal selesai tantangan |
| progressSaatIni | DECIMAL(10,2) | Progress saat ini |
| targetValue | DECIMAL(10,2) | Target nilai |
| persentaseSelesai | DECIMAL(5,2) | Persentase penyelesaian |
| statusSelesai | TINYINT | Status selesai |
| poinDiperoleh | INT | Poin yang diperoleh |

**badge**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| badgeID | INT | Primary Key, Auto Increment |
| namaBadge | VARCHAR(100) | Nama badge |
| deskripsi | TEXT | Deskripsi badge |
| iconBadge | VARCHAR(255) | Icon badge |
| kategori | ENUM | Kategori badge |
| syaratPerolehan | TEXT | Syarat untuk mendapatkan badge |
| poinReward | INT | Poin reward |
| tingkatKesulitan | ENUM | Tingkat kesulitan (mudah, sedang, sulit, expert) |

**userbadge**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| userBadgeID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| badgeID | INT | Foreign Key ke tabel badge |
| tanggalPeroleh | DATETIME | Tanggal perolehan badge |

**wilayahkerja**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| wilayahID | INT | Primary Key, Auto Increment |
| namaWilayah | VARCHAR(100) | Nama wilayah kerja |
| jenisWilayah | ENUM | Jenis wilayah (posyandu, puskesmas, kelurahan, kecamatan) |
| provinsi | VARCHAR(50) | Nama provinsi |
| kota | VARCHAR(50) | Nama kota |
| kecamatan | VARCHAR(50) | Nama kecamatan |
| kelurahan | VARCHAR(50) | Nama kelurahan |
| koordinatLat | DECIMAL(10,8) | Koordinat latitude |
| koordinatLong | DECIMAL(11,8) | Koordinat longitude |
| jumlahPenduduk | INT | Jumlah penduduk di wilayah |

**referensimakanan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| makananID | INT | Primary Key, Auto Increment |
| namaMakanan | VARCHAR(200) | Nama makanan |
| kategori | VARCHAR(50) | Kategori makanan |
| jenisLokal | TINYINT | Apakah makanan lokal (1/0) |
| porsiStandar | DECIMAL(6,2) | Porsi standar (default 100.00) |
| satuanStandar | VARCHAR(20) | Satuan standar (gram, porsi, dll) |
| kalori | DECIMAL(7,2) | Kalori per porsi |
| protein | DECIMAL(6,2) | Protein per porsi |
| karbohidrat | DECIMAL(6,2) | Karbohidrat per porsi |
| lemak | DECIMAL(6,2) | Lemak per porsi |
| serat | DECIMAL(6,2) | Serat per porsi |
| vitamin | TEXT | Kandungan vitamin (JSON) |
| mineral | TEXT | Kandungan mineral (JSON) |
| sumberData | VARCHAR(100) | Sumber data nutrisi |
| fotoMakanan | VARCHAR(255) | Foto makanan |

**rekomendasipersonal**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| rekomendasiID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| anakID | INT | Foreign Key ke tabel anak |
| jenisRekomendasi | ENUM | Jenis rekomendasi |
| judulRekomendasi | VARCHAR(200) | Judul rekomendasi |
| deskripsi | TEXT | Deskripsi rekomendasi |
| prioritas | ENUM | Prioritas rekomendasi |
| targetNutrisi | TEXT | Target nutrisi (JSON) |
| contohMenu | TEXT | Contoh menu |
| tipsImplementasi | TEXT | Tips implementasi |
| tanggalDibuat | DATETIME | Tanggal rekomendasi dibuat |
| tanggalKadaluarsa | DATE | Tanggal kadaluarsa |
| statusDibaca | TINYINT | Status sudah dibaca |
| tanggalDibaca | DATETIME | Tanggal dibaca |
| sumberRekomendasi | ENUM | Sumber rekomendasi |
| pembuatID | INT | ID pembuat rekomendasi |

**riwayatpoin**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| riwayatID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| tipeAktivitas | ENUM | Tipe aktivitas |
| poinDiperoleh | INT | Poin yang diperoleh |
| deskripsi | VARCHAR(200) | Deskripsi aktivitas |
| referensiID | INT | ID referensi aktivitas |
| tanggalPeroleh | DATETIME | Tanggal poin diperoleh |

**logaktivitas**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| logID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| aktivitas | VARCHAR(100) | Jenis aktivitas |
| deskripsi | TEXT | Deskripsi aktivitas |
| targetTabel | VARCHAR(50) | Tabel target aktivitas |
| targetID | INT | ID dari target aktivitas |
| ipAddress | VARCHAR(45) | IP address pengguna |
| userAgent | TEXT | User agent browser |
| device | VARCHAR(100) | Device yang digunakan |
| tanggalAktivitas | DATETIME | Tanggal aktivitas |

**userpreferensi**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| preferensiID | INT | Primary Key, Auto Increment |
| userID | INT | Foreign Key ke tabel user |
| notifPemantauan | TINYINT | Notifikasi pemantauan |
| notifKonsultasi | TINYINT | Notifikasi konsultasi |
| notifEdukasi | TINYINT | Notifikasi edukasi |
| notifGamifikasi | TINYINT | Notifikasi gamifikasi |
| bahasaPreferensi | VARCHAR(10) | Bahasa preferensi |
| temaPilihan | ENUM | Tema pilihan (light, dark, auto) |
| profilPublik | TINYINT | Profil publik |
| bagikanDataAgregat | TINYINT | Bagikan data agregat |
| settingJSON | TEXT | Setting tambahan (JSON) |

**laporankesehatan**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| laporanID | INT | Primary Key, Auto Increment |
| tenagaKesehatanID | INT | Foreign Key ke tabel tenagakesehatan |
| wilayahKerjaID | INT | Foreign Key ke tabel wilayahkerja |
| periodeLaporan | ENUM | Periode laporan |
| tanggalMulai | DATE | Tanggal mulai laporan |
| tanggalSelesai | DATE | Tanggal selesai laporan |
| jumlahPasienDipantau | INT | Jumlah pasien dipantau |
| jumlahKasusStunting | INT | Jumlah kasus stunting |
| jumlahKasusGiziBuruk | INT | Jumlah kasus gizi buruk |
| jumlahKasusObesitas | INT | Jumlah kasus obesitas |
| jumlahAlertAktif | INT | Jumlah alert aktif |
| jumlahIntervensiDilakukan | INT | Jumlah intervensi dilakukan |
| jumlahKonsultasi | INT | Jumlah konsultasi |
| trendPertumbuhan | TEXT | Trend pertumbuhan (JSON) |
| dataStatistik | TEXT | Data statistik (JSON) |
| catatanLaporan | TEXT | Catatan laporan |
| statusLaporan | ENUM | Status laporan |
| tanggalDibuat | DATETIME | Tanggal laporan dibuat |
| tanggalUpdate | DATETIME | Tanggal laporan diupdate |

**alertrisiko**
| Kolom | Tipe Data | Deskripsi |
|-------|-----------|-----------|
| alertID | INT | Primary Key, Auto Increment |
| anakID | INT | Foreign Key ke tabel anak |
| remajaID | INT | Foreign Key ke tabel remaja |
| ibuHamilID | INT | Foreign Key ke tabel ibuhamil |
| jenisRisiko | ENUM | Jenis risiko |
| tingkatRisiko | ENUM | Tingkat risiko |
| tanggalDeteksi | DATETIME | Tanggal deteksi risiko |
| deskripsiRisiko | TEXT | Deskripsi risiko |
| parameterPemicu | TEXT | Parameter pemicu (JSON) |
| statusTindakLanjut | ENUM | Status tindak lanjut |
| tanggalTindakLanjut | DATETIME | Tanggal tindak lanjut |
| catatanTindakLanjut | TEXT | Catatan tindak lanjut |
| tenagaKesehatanID | INT | ID tenaga kesehatan PIC |
| prioritas | INT | Prioritas alert (1=highest, 5=lowest) |

## Endpoint

### 1. CREATE - Menambahkan Data Baru
URL: /[NamaTabel]/create.php

Metode: POST

Parameter: Bergantung pada tabel yang digunakan

Contoh Request (untuk tabel anak):
```bash
curl -X POST \
  -d "orangtuaID=1" \
  -d "namaLengkap=John Doe" \
  -d "jenisKelamin=L" \
  -d "tanggalLahir=2020-01-01" \
  http://localhost/BE-Latihan-kelas/Anak/create.php
```

Contoh Respons Sukses:
```json
{
  "status": "success",
  "message": "Anak berhasil ditambahkan",
  "data": {
    "anakID": 1,
    "namaLengkap": "John Doe",
    "jenisKelamin": "L",
    "tanggalLahir": "2020-01-01",
    "orangtuaID": 1
  }
}
```

Contoh Respons Error:
```json
{
  "status": "error",
  "message": "Error message here"
}
```

### 2. READ - Membaca Data
URL: /[NamaTabel]/read.php

Metode: GET

Parameter (Opsional): Bergantung pada tabel, biasanya ID untuk mendapatkan data spesifik

Jika tidak ada parameter, maka akan mengembalikan semua data dari tabel tersebut.

Contoh Request (Semua Data):
```bash
curl http://localhost/BE-Latihan-kelas/Anak/read.php
```

Contoh Request (Spesifik ID):
```bash
curl http://localhost/BE-Latihan-kelas/Anak/read.php?anakID=1
```

Contoh Respons Sukses (Semua Data):
```json
{
  "status": "success",
  "message": "Data ditemukan",
  "data": [
    {
      "anakID": 1,
      "orangtuaID": 1,
      "namaLengkap": "John Doe",
      "jenisKelamin": "L",
      "tanggalLahir": "2020-01-01",
      "tempatLahir": null,
      "beratBadanLahir": null,
      "tinggiLahir": null,
      "golonganDarah": null,
      "riwayatKelahiran": null,
      "fotoProfile": null,
      "statusAktif": 1
    }
  ]
}
```

Contoh Respons Sukses (Data Kosong):
```json
{
  "status": "success",
  "message": "Data kosong",
  "data": []
}
```

### 3. UPDATE - Memperbarui Data
URL: /[NamaTabel]/update.php

Metode: POST

Parameter: Bergantung pada tabel dan field yang ingin diperbarui

Contoh Request:
```bash
curl -X POST \
  -d "anakID=1" \
  -d "namaLengkap=John Updated" \
  -d "jenisKelamin=P" \
  http://localhost/BE-Latihan-kelas/Anak/update.php
```

Contoh Respons Sukses:
```json
{
  "status": "success",
  "message": "Anak berhasil diperbarui",
  "data": {
    "anakID": 1,
    "namaLengkap": "John Updated",
    "jenisKelamin": "P"
  }
}
```

Contoh Respons Error:
```json
{
  "status": "error",
  "message": "Error message here"
}
```

### 4. DELETE - Menghapus Data
URL: /[NamaTabel]/delete.php

Metode: POST

Parameter: ID dari data yang akan dihapus

Contoh Request:
```bash
curl -X POST \
  -d "anakID=1" \
  http://localhost/BE-Latihan-kelas/Anak/delete.php
```

Contoh Respons Sukses:
```json
{
  "status": "success",
  "message": "Anak berhasil dihapus"
}
```

Contoh Respons Error:
```json
{
  "status": "error",
  "message": "Error message here"
}
```

## Instalasi

1. Pastikan Anda memiliki server web dengan PHP dan MySQL
2. Salin semua file ke direktori web server Anda
3. Buat database MySQL dan import struktur tabel dari file `growwell_db.sql`
4. Konfigurasi koneksi database di file `db.php`
5. Akses endpoint sesuai kebutuhan

## Instalasi

1. Pastikan Anda memiliki server web dengan PHP dan MySQL
2. Salin semua file ke direktori web server Anda
3. Buat database MySQL dan import struktur tabel dari file `growwell_db.sql`
4. Konfigurasi koneksi database di file `db.php`:
   ```php
   $servername = "localhost";
   $username = "root";  // sesuaikan dengan username MySQL Anda
   $password = "";      // sesuaikan dengan password MySQL Anda
   $dbname = "growwell_db";
   ```
5. Akses endpoint sesuai kebutuhan

## Catatan

- Semua endpoint mengembalikan respons dalam format JSON
- Gunakan metode POST untuk CREATE, UPDATE, dan DELETE
- Gunakan metode GET untuk READ
- Gunakan prepared statements untuk mencegah SQL injection
- Pastikan untuk selalu mengecek status respons sebelum memproses data lebih lanjut
- Setiap tabel memiliki folder terpisah yang berisi endpoint-endpoint CRUD
- Beberapa tabel memiliki constraint foreign key yang harus dipenuhi sebelum membuat data
- Beberapa kolom memiliki tipe data ENUM yang hanya menerima nilai-nilai tertentu
- Tabel-tabel memiliki relasi antar tabel yang harus dipertahankan untuk menjaga integritas data