<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$wilayahID = $_POST['wilayahID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM wilayahkerja
    WHERE wilayahID = ?
");

// i = integer
$stmt->bind_param("i", $wilayahID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data wilayah kerja berhasil dihapus"
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