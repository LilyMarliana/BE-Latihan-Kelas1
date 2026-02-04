<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST
$konsultasiID = isset($_POST['konsultasiID']) ? $_POST['konsultasiID'] : null;

// Validasi input
if (empty($konsultasiID)) {
    echo json_encode([
        "status" => "error",
        "message" => "KonsultasiID wajib diisi"
    ]);
    exit;
}

// Cek apakah konsultasi ada
$checkSql = "SELECT konsultasiID FROM konsultasi WHERE konsultasiID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $konsultasiID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Konsultasi tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus konsultasi
$sql = "DELETE FROM konsultasi WHERE konsultasiID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $konsultasiID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Konsultasi berhasil dihapus"
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