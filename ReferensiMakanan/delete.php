<?php
// Koneksi ke database
include '../db.php';

// Response JSON
header('Content-Type: application/json');

// Ambil ID dari POST dengan pengecekan
$makananID = isset($_POST['makananID']) ? $_POST['makananID'] : null;

// Validasi input
if (empty($makananID)) {
    echo json_encode([
        "status" => "error",
        "message" => "makananID wajib diisi"
    ]);
    exit;
}

// Cek apakah makanan ada
$checkSql = "SELECT makananID FROM referensimakanan WHERE makananID = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $makananID);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Referensi makanan tidak ditemukan"
    ]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Hapus referensi makanan
$sql = "DELETE FROM referensimakanan WHERE makananID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $makananID);

// Eksekusi
if ($stmt->execute()) {
    echo json_encode([
        "status"  => "success",
        "message" => "Data referensi makanan berhasil dihapus"
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