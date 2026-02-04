<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$notifID = $_POST['notifID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM notifikasi
    WHERE notifID = ?
");

// i = integer
$stmt->bind_param("i", $notifID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data notifikasi berhasil dihapus"
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