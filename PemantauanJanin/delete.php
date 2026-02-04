<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST dengan pengecekan
$pemantauanID = isset($_POST['pemantauanID']) ? $_POST['pemantauanID'] : null;

// Validasi input
if (empty($pemantauanID)) {
    echo json_encode([
        "status" => "error",
        "message" => "pemantauanID wajib diisi"
    ]);
    exit;
}

// Cek apakah pemantauan janin ada
$checkSql = "SELECT pemantauanID FROM pemantauanjanin WHERE pemantauanID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $pemantauanID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Data pemantauan janin tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus pemantauan janin
$sql = "DELETE FROM pemantauanjanin WHERE pemantauanID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pemantauanID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data pemantauan janin berhasil dihapus"
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