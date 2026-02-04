<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$rekomendasiID = $_POST['rekomendasiID'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM rekomendasipersonal
    WHERE rekomendasiID = ?
");

// i = integer
$stmt->bind_param("i", $rekomendasiID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data rekomendasi personal berhasil dihapus"
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