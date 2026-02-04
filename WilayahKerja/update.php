<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$wilayahID        = $_POST['wilayahID'];
$namaWilayah      = $_POST['namaWilayah'];
$jenisWilayah     = $_POST['jenisWilayah'];
$provinsi         = $_POST['provinsi'];
$kota             = $_POST['kota'];
$kecamatan        = $_POST['kecamatan'];
$kelurahan        = $_POST['kelurahan'];
$koordinatLat     = $_POST['koordinatLat'];
$koordinatLong    = $_POST['koordinatLong'];
$jumlahPenduduk   = $_POST['jumlahPenduduk'];

// query update
$stmt = $conn->prepare("
    UPDATE wilayahkerja
    SET
        namaWilayah = ?,
        jenisWilayah = ?,
        provinsi = ?,
        kota = ?,
        kecamatan = ?,
        kelurahan = ?,
        koordinatLat = ?,
        koordinatLong = ?,
        jumlahPenduduk = ?
    WHERE wilayahID = ?
");

// s = string, i = integer, d = double
$stmt->bind_param(
    "ssssssddii",
    $namaWilayah,
    $jenisWilayah,
    $provinsi,
    $kota,
    $kecamatan,
    $kelurahan,
    $koordinatLat,
    $koordinatLong,
    $jumlahPenduduk,
    $wilayahID
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data wilayah kerja berhasil diperbarui",
        "data" => [
            "wilayahID" => $wilayahID,
            "namaWilayah" => $namaWilayah,
            "jenisWilayah" => $jenisWilayah,
            "provinsi" => $provinsi,
            "kota" => $kota,
            "kecamatan" => $kecamatan,
            "kelurahan" => $kelurahan,
            "koordinatLat" => $koordinatLat,
            "koordinatLong" => $koordinatLong,
            "jumlahPenduduk" => $jumlahPenduduk
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