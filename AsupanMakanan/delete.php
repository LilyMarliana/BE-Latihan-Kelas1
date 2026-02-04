<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$asupanID = $_POST['asupanID'];

// Cek apakah asupan makanan ada
$checkSql = "SELECT asupanID FROM AsupanMakanan WHERE asupanID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $asupanID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Asupan Makanan tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus asupan makanan
$sql = "DELETE FROM AsupanMakanan WHERE asupanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $asupanID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Asupan Makanan berhasil dihapus"
    ]);
} else {
    echo json_encode([
        "status"  => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>