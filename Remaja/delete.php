<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$remajaID = $_POST['remajaID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM remaja
    WHERE remajaID = ?
");

// i = integer
$stmt->bind_param("i", $remajaID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data remaja berhasil dihapus"
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