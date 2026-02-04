<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$userBadgeID = $_POST['userBadgeID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM userbadge
    WHERE userBadgeID = ?
");

// i = integer
$stmt->bind_param("i", $userBadgeID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data user badge berhasil dihapus"
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