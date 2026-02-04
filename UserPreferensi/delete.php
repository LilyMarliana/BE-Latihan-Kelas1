<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$preferensiID = $_POST['preferensiID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM userpreferensi
    WHERE preferensiID = ?
");

// i = integer
$stmt->bind_param("i", $preferensiID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data user preferensi berhasil dihapus"
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