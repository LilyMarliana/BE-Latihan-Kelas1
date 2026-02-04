<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$riwayatID = $_POST['riwayatID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM riwayatpoin
    WHERE riwayatID = ?
");

// i = integer
$stmt->bind_param("i", $riwayatID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data riwayat poin berhasil dihapus"
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