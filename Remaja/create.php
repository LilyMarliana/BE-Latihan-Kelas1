<?php
// Koneksi database
include_once '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil data dari POST
$userID             = $_POST['userID'];
$nama               = $_POST['nama'];
$tanggalLahir       = $_POST['tanggalLahir'];
$jenisKelamin       = $_POST['jenisKelamin'];
$sekolah            = $_POST['sekolah'];
$kelas              = $_POST['kelas'];
$tingkatAktivitas   = $_POST['tingkatAktivitas'];
$targetKesehatan    = $_POST['targetKesehatan'];
$totalPoin          = $_POST['totalPoin'];
$level              = $_POST['level'];

// Prepared statement INSERT
$stmt = $conn->prepare("
    INSERT INTO remaja
    (userID, nama, tanggalLahir, jenisKelamin, sekolah, kelas, tingkatAktivitas, targetKesehatan, totalPoin, level)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// s = string, i = integer
$stmt->bind_param(
    "isssssssii",
    $userID,
    $nama,
    $tanggalLahir,
    $jenisKelamin,
    $sekolah,
    $kelas,
    $tingkatAktivitas,
    $targetKesehatan,
    $totalPoin,
    $level
);

// Eksekusi
if ($stmt->execute()) {

    $last_id = $stmt->insert_id;

    echo json_encode([
        "status"  => "success",
        "message" => "Data remaja berhasil ditambahkan",
        "data" => [
            "remajaID" => $last_id,
            "userID" => $userID,
            "nama" => $nama,
            "tanggalLahir" => $tanggalLahir,
            "jenisKelamin" => $jenisKelamin,
            "sekolah" => $sekolah,
            "kelas" => $kelas,
            "tingkatAktivitas" => $tingkatAktivitas,
            "targetKesehatan" => $targetKesehatan,
            "totalPoin" => $totalPoin,
            "level" => $level
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