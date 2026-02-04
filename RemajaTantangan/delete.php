<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$partisipasiID = $_POST['partisipasiID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM remajatantangan
    WHERE partisipasiID = ?
");

// i = integer
$stmt->bind_param("i", $partisipasiID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data remaja tantangan berhasil dihapus"
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