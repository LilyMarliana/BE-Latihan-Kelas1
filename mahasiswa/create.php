<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$nama              = $_POST['nama'];
$kategori_pengguna = $_POST['kategori_pengguna'];
$status_risiko     = $_POST['status_risiko'];
$tahun_registrasi  = $_POST['tahun_registrasi'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO pengguna_growwell 
    (nama, kategori_pengguna, status_risiko, tahun_registrasi)
    VALUES (?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "sssi",
    $nama,
    $kategori_pengguna,
    $status_risiko,
    $tahun_registrasi
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data pengguna berhasil ditambahkan",
        "data" => [
            "id" => $last_id,
            "nama" => $nama,
            "kategori_pengguna" => $kategori_pengguna,
            "status_risiko" => $status_risiko,
            "tahun_registrasi" => $tahun_registrasi
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
