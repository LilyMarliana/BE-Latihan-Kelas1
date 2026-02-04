<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$kontenID = $_POST['kontenID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM kontenedukasi
    WHERE kontenID = ?
");

// i = integer
$stmt->bind_param("i", $kontenID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data konten edukasi berhasil dihapus"
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