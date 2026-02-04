<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST dengan pengecekan
$interaksiID = isset($_POST['interaksiID']) ? $_POST['interaksiID'] : null;

// Validasi input
if (empty($interaksiID)) {
    echo json_encode([
        "status" => "error",
        "message" => "interaksiID wajib diisi"
    ]);
    exit;
}

// Cek apakah interaksi konten ada
$checkSql = "SELECT interaksiID FROM interaksikonten WHERE interaksiID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $interaksiID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Data interaksi konten tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus interaksi konten
$sql = "DELETE FROM interaksikonten WHERE interaksiID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $interaksiID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data interaksi konten berhasil dihapus"
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