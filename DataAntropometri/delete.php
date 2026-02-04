<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST dengan pengecekan
$dataID = isset($_POST['dataID']) ? $_POST['dataID'] : null;

// Validasi input
if (empty($dataID)) {
    echo json_encode([
        "status" => "error",
        "message" => "dataID wajib diisi"
    ]);
    exit;
}

// Cek apakah data antropometri ada
$checkSql = "SELECT dataID FROM dataantropometri WHERE dataID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $dataID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Data Antropometri tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus data antropometri
$sql = "DELETE FROM dataantropometri WHERE dataID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dataID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data Antropometri berhasil dihapus"
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