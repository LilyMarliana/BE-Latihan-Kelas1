<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$tenagaKesehatanID      = $_POST['tenagaKesehatanID'];
$wilayahKerjaID        = $_POST['wilayahKerjaID'];
$periodeLaporan        = $_POST['periodeLaporan'];
$tanggalMulai          = $_POST['tanggalMulai'];
$tanggalSelesai        = $_POST['tanggalSelesai'];
$jumlahPasienDipantau  = $_POST['jumlahPasienDipantau'];
$jumlahKasusStunting   = $_POST['jumlahKasusStunting'];
$jumlahKasusGiziBuruk  = $_POST['jumlahKasusGiziBuruk'];
$jumlahKasusObesitas   = $_POST['jumlahKasusObesitas'];
$jumlahAlertAktif      = $_POST['jumlahAlertAktif'];
$jumlahIntervensiDilakukan = $_POST['jumlahIntervensiDilakukan'];
$jumlahKonsultasi      = $_POST['jumlahKonsultasi'];
$trendPertumbuhan      = $_POST['trendPertumbuhan'];
$dataStatistik         = $_POST['dataStatistik'];
$catatanLaporan        = $_POST['catatanLaporan'];
$statusLaporan         = $_POST['statusLaporan'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO laporankesehatan
    (tenagaKesehatanID, wilayahKerjaID, periodeLaporan, tanggalMulai, tanggalSelesai, jumlahPasienDipantau, jumlahKasusStunting, jumlahKasusGiziBuruk, jumlahKasusObesitas, jumlahAlertAktif, jumlahIntervensiDilakukan, jumlahKonsultasi, trendPertumbuhan, dataStatistik, catatanLaporan, statusLaporan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "iisssi iiiiiiisssss",
    $tenagaKesehatanID,
    $wilayahKerjaID,
    $periodeLaporan,
    $tanggalMulai,
    $tanggalSelesai,
    $jumlahPasienDipantau,
    $jumlahKasusStunting,
    $jumlahKasusGiziBuruk,
    $jumlahKasusObesitas,
    $jumlahAlertAktif,
    $jumlahIntervensiDilakukan,
    $jumlahKonsultasi,
    $trendPertumbuhan,
    $dataStatistik,
    $catatanLaporan,
    $statusLaporan
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data laporan kesehatan berhasil ditambahkan",
        "data" => [
            "laporanID" => $last_id,
            "tenagaKesehatanID" => $tenagaKesehatanID,
            "wilayahKerjaID" => $wilayahKerjaID,
            "periodeLaporan" => $periodeLaporan,
            "tanggalMulai" => $tanggalMulai,
            "tanggalSelesai" => $tanggalSelesai,
            "jumlahPasienDipantau" => $jumlahPasienDipantau,
            "jumlahKasusStunting" => $jumlahKasusStunting,
            "jumlahKasusGiziBuruk" => $jumlahKasusGiziBuruk,
            "jumlahKasusObesitas" => $jumlahKasusObesitas,
            "jumlahAlertAktif" => $jumlahAlertAktif,
            "jumlahIntervensiDilakukan" => $jumlahIntervensiDilakukan,
            "jumlahKonsultasi" => $jumlahKonsultasi,
            "trendPertumbuhan" => $trendPertumbuhan,
            "dataStatistik" => $dataStatistik,
            "catatanLaporan" => $catatanLaporan,
            "statusLaporan" => $statusLaporan
        ]
    ]);

} else {

    echo json_encode([
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>