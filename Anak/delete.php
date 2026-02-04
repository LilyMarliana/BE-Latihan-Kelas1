<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$anakID = $_POST['anakID'];

// Cek apakah anak ada
$checkSql = "SELECT anakID FROM anak WHERE anakID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $anakID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Anak tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus anak
$sql = "DELETE FROM anak WHERE anakID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $anakID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Anak berhasil dihapus"
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