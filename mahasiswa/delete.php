<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$id = $_POST['id'];

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM pengguna_growwell
    WHERE id = ?
");

// i = integer
$stmt->bind_param("i", $id);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data pengguna berhasil dihapus"
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
