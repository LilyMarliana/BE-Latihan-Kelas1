<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST dengan pengecekan
$tantanganID = isset($_POST['tantanganID']) ? $_POST['tantanganID'] : null;

// Validasi input
if (empty($tantanganID)) {
    echo json_encode([
        "status" => "error",
        "message" => "tantanganID wajib diisi"
    ]);
    exit;
}

// Cek apakah tantangan ada
$checkSql = "SELECT tantanganID FROM tantangan WHERE tantanganID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $tantanganID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Data tantangan tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus tantangan
$sql = "DELETE FROM tantangan WHERE tantanganID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tantanganID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data tantangan berhasil dihapus"
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