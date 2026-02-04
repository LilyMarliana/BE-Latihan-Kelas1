<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$nutrisiID = isset($_POST['nutrisiID']) ? $_POST['nutrisiID'] : null;

// Validasi input
if (empty($nutrisiID)) {
    echo json_encode([
        "status" => "error",
        "message" => "NutrisiID wajib diisi"
    ]);
    exit;
}

// Cek apakah data nutrisi ada
$checkSql = "SELECT nutrisiID FROM datanutrisi WHERE nutrisiID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $nutrisiID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Data Nutrisi tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus data nutrisi
$sql = "DELETE FROM datanutrisi WHERE nutrisiID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $nutrisiID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data Nutrisi berhasil dihapus"
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