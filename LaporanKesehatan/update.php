<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$laporanID             = $_POST['laporanID'];
$tenagaKesehatanID     = $_POST['tenagaKesehatanID'];
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

// query update
$stmt = $conn->prepare("
    UPDATE laporankesehatan
    SET
        tenagaKesehatanID = ?,
        wilayahKerjaID = ?,
        periodeLaporan = ?,
        tanggalMulai = ?,
        tanggalSelesai = ?,
        jumlahPasienDipantau = ?,
        jumlahKasusStunting = ?,
        jumlahKasusGiziBuruk = ?,
        jumlahKasusObesitas = ?,
        jumlahAlertAktif = ?,
        jumlahIntervensiDilakukan = ?,
        jumlahKonsultasi = ?,
        trendPertumbuhan = ?,
        dataStatistik = ?,
        catatanLaporan = ?,
        statusLaporan = ?
    WHERE laporanID = ?
");

// s = string, i = integer
$stmt->bind_param(
    "iisssi iiiiiiisssssi",
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
    $statusLaporan,
    $laporanID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data laporan kesehatan berhasil diperbarui",
        "data" => [
            "laporanID" => $laporanID,
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
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>