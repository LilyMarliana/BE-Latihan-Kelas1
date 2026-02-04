<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$namaWilayah      = $_POST['namaWilayah'];
$jenisWilayah     = $_POST['jenisWilayah'];
$provinsi         = $_POST['provinsi'];
$kota             = $_POST['kota'];
$kecamatan        = $_POST['kecamatan'];
$kelurahan        = $_POST['kelurahan'];
$koordinatLat     = $_POST['koordinatLat'];
$koordinatLong    = $_POST['koordinatLong'];
$jumlahPenduduk   = $_POST['jumlahPenduduk'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO wilayahkerja
    (namaWilayah, jenisWilayah, provinsi, kota, kecamatan, kelurahan, koordinatLat, koordinatLong, jumlahPenduduk)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer, d = double
$stmt->bind_param(
    "ssssssddi",
    $namaWilayah,
    $jenisWilayah,
    $provinsi,
    $kota,
    $kecamatan,
    $kelurahan,
    $koordinatLat,
    $koordinatLong,
    $jumlahPenduduk
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data wilayah kerja berhasil ditambahkan",
        "data" => [
            "wilayahID" => $last_id,
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
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>