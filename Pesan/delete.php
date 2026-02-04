<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$pesanID = $_POST['pesanID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM pesan
    WHERE pesanID = ?
");

// i = integer
$stmt->bind_param("i", $pesanID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data pesan berhasil dihapus"
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