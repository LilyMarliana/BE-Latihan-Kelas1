<?php
// koneksi database
include '../db.php';

// format response JSON
header('Content-Type: application/json');

// ambil data dari form (POST)
$id                = $_POST['id'];
$nama              = $_POST['nama'];
$kategori_pengguna = $_POST['kategori_pengguna'];
$status_risiko     = $_POST['status_risiko'];
$tahun_registrasi  = $_POST['tahun_registrasi'];

// query update
$stmt = $conn->prepare("
    UPDATE pengguna_growwell
    SET 
        nama = ?,
        kategori_pengguna = ?,
        status_risiko = ?,
        tahun_registrasi = ?
    WHERE id = ?
");

// s = string, i = integer
$stmt->bind_param(
    "sssii",
    $nama,
    $kategori_pengguna,
    $status_risiko,
    $tahun_registrasi,
    $id
);

// eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data pengguna berhasil diperbarui",
        "data" => [
            "id" => $id,
            "nama" => $nama,
            "kategori_pengguna" => $kategori_pengguna,
            "status_risiko" => $status_risiko,
            "tahun_registrasi" => $tahun_registrasi
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
