<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$badgeID = isset($_POST['badgeID']) ? $_POST['badgeID'] : null;

// Validasi input
if (empty($badgeID)) {
    echo json_encode([
        "status" => "error",
        "message" => "BadgeID wajib diisi"
    ]);
    exit;
}

// Cek apakah badge ada
$checkSql = "SELECT badgeID FROM badge WHERE badgeID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $badgeID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Badge tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Prepared statement DELETE
$stmt = $conn->prepare("
    DELETE FROM badge
    WHERE badgeID = ?
");

// i = integer
$stmt->bind_param("i", $badgeID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data badge berhasil dihapus"
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