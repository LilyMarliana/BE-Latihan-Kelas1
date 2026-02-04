<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$ibuHamilID = isset($_POST['ibuHamilID']) ? $_POST['ibuHamilID'] : null;

// Validasi input
if (empty($ibuHamilID)) {
    echo json_encode([
        "status" => "error",
        "message" => "IbuHamilID wajib diisi"
    ]);
    exit;
}

// Cek apakah ibu hamil ada
$checkSql = "SELECT ibuHamilID FROM ibuhamil WHERE ibuHamilID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $ibuHamilID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Ibu Hamil tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus ibu hamil
$sql = "DELETE FROM ibuhamil WHERE ibuHamilID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ibuHamilID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Ibu Hamil berhasil dihapus"
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