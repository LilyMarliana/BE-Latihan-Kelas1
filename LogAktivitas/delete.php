<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$logID = $_POST['logID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM logaktivitas
    WHERE logID = ?
");

// i = integer
$stmt->bind_param("i", $logID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data log aktivitas berhasil dihapus"
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