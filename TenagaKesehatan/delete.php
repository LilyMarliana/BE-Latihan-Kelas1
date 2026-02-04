<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST dengan pengecekan
$tenagaID = isset($_POST['tenagaID']) ? $_POST['tenagaID'] : null;

// Validasi input
if (empty($tenagaID)) {
    echo json_encode([
        "status" => "error",
        "message" => "tenagaID wajib diisi"
    ]);
    exit;
}

// Cek apakah tenaga kesehatan ada
$checkSql = "SELECT tenagaID FROM tenagakesehatan WHERE tenagaID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $tenagaID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Tenaga kesehatan tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus tenaga kesehatan
$sql = "DELETE FROM tenagakesehatan WHERE tenagaID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenagaID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data tenaga kesehatan berhasil dihapus"
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