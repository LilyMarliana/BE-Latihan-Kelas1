<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$userID = isset($_POST['userID']) ? $_POST['userID'] : null;

// Validasi input
if (empty($userID)) {
    echo json_encode([
        "status" => "error",
        "message" => "UserID wajib diisi"
    ]);
    exit;
}

// Cek apakah user ada
$checkSql = "SELECT userID FROM user WHERE userID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $userID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus user
$sql = "DELETE FROM user WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "User berhasil dihapus"
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