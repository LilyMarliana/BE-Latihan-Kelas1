<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$alertID = $_POST['alertID'];

// Cek apakah alert risiko ada
$checkSql = "SELECT alertID FROM alertrisiko WHERE alertID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $alertID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Alert Risiko tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus alert risiko
$sql = "DELETE FROM alertrisiko WHERE alertID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $alertID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Alert Risiko berhasil dihapus"
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